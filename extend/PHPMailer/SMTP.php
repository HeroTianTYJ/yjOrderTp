<?php

namespace PHPMailer;

class SMTP
{
    //private const VERSION = '6.6.3';
    private const DEFAULT_PORT = 25;
    private const MAX_LINE_LENGTH = 998;
    private const MAX_REPLY_LENGTH = 512;
    private const DEBUG_OFF = 0;
    private int $doDebug = self::DEBUG_OFF;
    private string $debugOutput = 'echo';
    private bool $doVerp = false;
    private int $timeout = 300;
    private int $timeLimit = 300;
    private array $smtpTransactionIdPatterns = [
        'exim' => '/[\d]{3} OK id=(.*)/',
        'sendmail' => '/[\d]{3} 2.0.0 (.*) Message/',
        'postfix' => '/[\d]{3} 2.0.0 Ok: queued as (.*)/',
        'Microsoft_ESMTP' => '/[0-9]{3} 2.[\d].0 (.*)@(?:.*) Queued mail for delivery/',
        'Amazon_SES' => '/[\d]{3} Ok (.*)/',
        'SendGrid' => '/[\d]{3} Ok: queued as (.*)/',
        'CampaignMonitor' => '/[\d]{3} 2.0.0 OK:([a-zA-Z\d]{48})/',
        'Haraka' => '/[\d]{3} Message Queued \((.*)\)/',
        'Mailjet' => '/[\d]{3} OK queued as (.*)/'
    ];
    private $lastSmtpTransactionId;
    private $smtpConn;
    private array $error = ['error' => '', 'detail' => '', 'smtp_code' => '', 'smtp_code_ex' => ''];
    private $heloRply;
    private ?array $serverCaps;
    private string $lastReply = '';

    public function connect($host, $port = null, $timeout = 30, $options = [])
    {
        if ($this->connected()) {
            return false;
        }
        if (empty($port)) {
            $port = self::DEFAULT_PORT;
        }
        $this->smtpConn = $this->getSMTPConnection($host, $port, $timeout, $options);
        if ($this->smtpConn === false) {
            return false;
        }
        $this->lastReply = $this->getLines();
        $responseCode = (int)substr($this->lastReply, 0, 3);
        if ($responseCode === 220) {
            return true;
        }
        if ($responseCode === 554) {
            $this->quit();
        }
        $this->close();
        return false;
    }

    public function startTLS()
    {
        if (!$this->sendCommand('STARTTLS', 220)) {
            return false;
        }
        $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        }
        $crypto_ok = stream_socket_enable_crypto($this->smtpConn, true, $crypto_method);
        restore_error_handler();
        return (bool)$crypto_ok;
    }

    public function authenticate($username, $password, $authType = null, $OAuth = null)
    {
        if (!$this->serverCaps) {
            return false;
        }
        if (array_key_exists('EHLO', $this->serverCaps)) {
            if (!array_key_exists('AUTH', $this->serverCaps)) {
                return false;
            }
            if (null !== $authType && !in_array($authType, $this->serverCaps['AUTH'], true)) {
                $authType = null;
            }
            if (empty($authType)) {
                foreach (['CRAM-MD5', 'LOGIN', 'PLAIN', 'XOAUTH2'] as $method) {
                    if (in_array($method, $this->serverCaps['AUTH'], true)) {
                        $authType = $method;
                        break;
                    }
                }
                if (empty($authType)) {
                    return false;
                }
            }
            if (!in_array($authType, $this->serverCaps['AUTH'], true)) {
                return false;
            }
        } elseif (empty($authType)) {
            $authType = 'LOGIN';
        }
        switch ($authType) {
            case 'PLAIN':
                if (!$this->sendCommand('AUTH PLAIN', 334)) {
                    return false;
                }
                if (!$this->sendCommand(base64_encode("\0" . $username . "\0" . $password), 235)) {
                    return false;
                }
                break;
            case 'LOGIN':
                if (!$this->sendCommand('AUTH LOGIN', 334)) {
                    return false;
                }
                if (!$this->sendCommand(base64_encode($username), 334)) {
                    return false;
                }
                if (!$this->sendCommand(base64_encode($password ?: ''), 235)) {
                    return false;
                }
                break;
            case 'CRAM-MD5':
                if (!$this->sendCommand('AUTH CRAM-MD5', 334)) {
                    return false;
                }
                $response = $username . ' ' . $this->hmac(base64_decode(substr($this->lastReply, 4)), $password);
                return $this->sendCommand(base64_encode($response), 235);
            case 'XOAUTH2':
                if (null === $OAuth) {
                    return false;
                }
                $oauth = $OAuth->getOauth64();
                if (!$this->sendCommand('AUTH XOAUTH2 ' . $oauth, 235)) {
                    return false;
                }
                break;
            default:
                return false;
        }
        return true;
    }

    public function connected()
    {
        if (is_resource($this->smtpConn)) {
            $sock_status = stream_get_meta_data($this->smtpConn);
            if ($sock_status['eof']) {
                $this->close();
                return false;
            }
            return true;
        }
        return false;
    }

    public function close()
    {
        $this->serverCaps = null;
        $this->heloRply = null;
        if (is_resource($this->smtpConn)) {
            fclose($this->smtpConn);
            $this->smtpConn = null;
        }
    }

    public function data($msg_data)
    {
        if (!$this->sendCommand('DATA', 354)) {
            return false;
        }
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $msg_data));
        $field = substr($lines[0], 0, strpos($lines[0], ':'));
        $in_headers = false;
        if (!empty($field) && strpos($field, ' ') === false) {
            $in_headers = true;
        }
        foreach ($lines as $line) {
            $lines_out = [];
            if ($in_headers && $line === '') {
                $in_headers = false;
            }
            while (isset($line[self::MAX_LINE_LENGTH])) {
                $pos = strrpos(substr($line, 0, self::MAX_LINE_LENGTH), ' ');
                if (!$pos) {
                    $pos = self::MAX_LINE_LENGTH - 1;
                    $lines_out[] = substr($line, 0, $pos);
                    $line = substr($line, $pos);
                } else {
                    $lines_out[] = substr($line, 0, $pos);
                    $line = substr($line, $pos + 1);
                }
                if ($in_headers) {
                    $line = "\t" . $line;
                }
            }
            $lines_out[] = $line;
            foreach ($lines_out as $line_out) {
                if (!empty($line_out) && $line_out[0] === '.') {
                    $line_out = '.' . $line_out;
                }
                $this->clientSend($line_out . "\r\n");
            }
        }
        $saveTimeLimit = $this->timeLimit;
        $this->timeLimit *= 2;
        $result = $this->sendCommand('.', 250);
        $this->recordLastTransactionID();
        $this->timeLimit = $saveTimeLimit;
        return $result;
    }

    public function hello($host = '')
    {
        if ($this->sendHello('EHLO', $host)) {
            return true;
        }
        if (substr($this->heloRply, 0, 3) == '421') {
            return false;
        }
        return $this->sendHello('HELO', $host);
    }

    public function mail($from)
    {
        return $this->sendCommand('MAIL FROM:<' . $from . '>' . ($this->doVerp ? ' XVERP' : ''), 250);
    }

    public function quit($close_on_error = true)
    {
        $noError = $this->sendCommand('QUIT', 221);
        $err = $this->error;
        if ($noError || $close_on_error) {
            $this->close();
            $this->error = $err;
        }
        return $noError;
    }

    public function recipient($address, $dsn = '')
    {
        if (empty($dsn)) {
            $rcpt = 'RCPT TO:<' . $address . '>';
        } else {
            $dsn = strtoupper($dsn);
            $notify = [];
            if (strpos($dsn, 'NEVER') !== false) {
                $notify[] = 'NEVER';
            } else {
                foreach (['SUCCESS', 'FAILURE', 'DELAY'] as $value) {
                    if (strpos($dsn, $value) !== false) {
                        $notify[] = $value;
                    }
                }
            }
            $rcpt = 'RCPT TO:<' . $address . '> NOTIFY=' . implode(',', $notify);
        }
        return $this->sendCommand($rcpt, [250, 251]);
    }

    public function setVerp($enabled = false)
    {
        $this->doVerp = $enabled;
    }

    public function setDebugOutput($method = 'echo')
    {
        $this->debugOutput = $method;
    }

    public function setDebugLevel($level = 0)
    {
        $this->doDebug = $level;
    }

    public function setTimeout($timeout = 0)
    {
        $this->timeout = $timeout;
    }

    public function getServerExt($name)
    {
        if (!$this->serverCaps) {
            return null;
        }
        if (!array_key_exists($name, $this->serverCaps)) {
            if ('HELO' === $name) {
                return $this->serverCaps['EHLO'];
            }
            if ('EHLO' === $name || array_key_exists('EHLO', $this->serverCaps)) {
                return false;
            }
            return null;
        }
        return $this->serverCaps[$name];
    }

    public function getLastTransactionID()
    {
        return $this->lastSmtpTransactionId;
    }

    private function getSMTPConnection($host, $port = null, $timeout = 30, $options = [])
    {
        static $streamOk;
        if (null === $streamOk) {
            $streamOk = function_exists('stream_socket_client');
        }
        $errno = 0;
        $errStr = '';
        if ($streamOk) {
            $socket_context = stream_context_create($options);
            $connection = stream_socket_client(
                $host . ':' . $port,
                $errno,
                $errStr,
                $timeout,
                STREAM_CLIENT_CONNECT,
                $socket_context
            );
        } else {
            $connection = fsockopen($host, $port, $errno, $errStr, $timeout);
        }
        restore_error_handler();
        if (!is_resource($connection)) {
            return false;
        }
        if (strpos(PHP_OS, 'WIN') !== 0) {
            $max = (int)ini_get('max_execution_time');
            if (0 !== $max && $timeout > $max && strpos(ini_get('disable_functions'), 'set_time_limit') === false) {
                @set_time_limit($timeout);
            }
            stream_set_timeout($connection, $timeout);
        }
        return $connection;
    }

    private function hmac($data, $key)
    {
        if (function_exists('hash_hmac')) {
            return hash_hmac('md5', $data, $key);
        }
        $byteLen = 64;
        if (strlen($key) > $byteLen) {
            $key = pack('H*', md5($key));
        }
        $key = str_pad($key, $byteLen, chr(0x00));
        return md5($key ^ str_pad('', $byteLen, chr(0x5c)) . pack('H*', md5($key ^ str_pad('', $byteLen, chr(0x36)) .
                $data)));
    }

    private function sendHello($hello, $host)
    {
        $noError = $this->sendCommand($hello . ' ' . $host, 250);
        $this->heloRply = $this->lastReply;
        if ($noError) {
            $this->parseHelloFields($hello);
        } else {
            $this->serverCaps = null;
        }
        return $noError;
    }

    private function parseHelloFields($type)
    {
        $this->serverCaps = [];
        $lines = explode("\n", $this->heloRply);
        foreach ($lines as $n => $s) {
            $s = trim(substr($s, 4));
            if (empty($s)) {
                continue;
            }
            $fields = explode(' ', $s);
            if (!empty($fields)) {
                if (!$n) {
                    $name = $type;
                    $fields = $fields[0];
                } else {
                    $name = array_shift($fields);
                    switch ($name) {
                        case 'SIZE':
                            $fields = ($fields ? $fields[0] : 0);
                            break;
                        case 'AUTH':
                            if (!is_array($fields)) {
                                $fields = [];
                            }
                            break;
                        default:
                            $fields = true;
                    }
                }
                $this->serverCaps[$name] = $fields;
            }
        }
    }

    private function sendCommand($commandString, $expect)
    {
        if (!$this->connected()) {
            return false;
        }
        if ((strpos($commandString, "\n") !== false) || (strpos($commandString, "\r") !== false)) {
            return false;
        }
        $this->clientSend($commandString . "\r\n");
        $this->lastReply = $this->getLines();
        $matches = [];
        if (preg_match('/^(\d{3})[ -](?:(\d\\.\d\\.\d{1,2}) )?/', $this->lastReply, $matches)) {
            $code = (int)$matches[1];
        } else {
            $code = (int)substr($this->lastReply, 0, 3);
        }
        if (!in_array($code, (array)$expect, true)) {
            return false;
        }
        return true;
    }

    private function clientSend($data)
    {
        fwrite($this->smtpConn, $data);
        restore_error_handler();
    }

    private function getError()
    {
        return $this->error;
    }

    private function getLastReply()
    {
        return $this->lastReply;
    }

    private function getLines()
    {
        if (!is_resource($this->smtpConn)) {
            return '';
        }
        $data = '';
        $endTime = 0;
        stream_set_timeout($this->smtpConn, $this->timeout);
        if ($this->timeLimit > 0) {
            $endTime = time() + $this->timeLimit;
        }
        $selR = [$this->smtpConn];
        $selW = null;
        while (is_resource($this->smtpConn) && !feof($this->smtpConn)) {
            $n = stream_select($selR, $selW, $selW, $this->timeLimit);
            restore_error_handler();
            if ($n === false) {
                $message = $this->getError()['detail'];
                if (stripos($message, 'interrupted system call') !== false) {
                    continue;
                }
                break;
            }
            if (!$n) {
                break;
            }
            $str = @fgets($this->smtpConn, self::MAX_REPLY_LENGTH);
            $data .= $str;
            if (!isset($str[3]) || $str[3] === ' ' || $str[3] === "\r" || $str[3] === "\n") {
                break;
            }
            $info = stream_get_meta_data($this->smtpConn);
            if ($info['timed_out']) {
                break;
            }
            if ($endTime && time() > $endTime) {
                break;
            }
        }
        return $data;
    }

    private function recordLastTransactionID()
    {
        $reply = $this->getLastReply();
        if (empty($reply)) {
            $this->lastSmtpTransactionId = null;
        } else {
            $this->lastSmtpTransactionId = false;
            foreach ($this->smtpTransactionIdPatterns as $smtp_transaction_id_pattern) {
                $matches = [];
                if (preg_match($smtp_transaction_id_pattern, $reply, $matches)) {
                    $this->lastSmtpTransactionId = trim($matches[1]);
                    break;
                }
            }
        }
    }
}
