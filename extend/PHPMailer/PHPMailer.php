<?php

namespace PHPMailer;

use Exception;

class PHPMailer
{
    public string $Host = 'localhost';
    public int $Port = 25;
    public string $Username = '';
    public string $Password = '';
    public string $From = '';
    public string $FromName = '';
    public string $Subject = '';
    public string $Body = '';
    private const CHARSET_ASCII = 'us-ascii';
    private const CHARSET_ISO88591 = 'iso-8859-1';
    private const CHARSET_UTF8 = 'utf-8';
    private const CONTENT_TYPE_PLAINTEXT = 'text/plain';
    private const CONTENT_TYPE_TEXT_CALENDAR = 'text/calendar';
    private const CONTENT_TYPE_TEXT_HTML = 'text/html';
    private const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
    private const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';
    private const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';
    private const ENCODING_7BIT = '7bit';
    private const ENCODING_8BIT = '8bit';
    private const ENCODING_BASE64 = 'base64';
    private const ENCODING_BINARY = 'binary';
    private const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';
    private const ENCRYPTION_STARTTLS = 'tls';
    private const ENCRYPTION_SMTPS = 'ssl';
    private const ICAL_METHOD_REQUEST = 'REQUEST';
    private const ICAL_METHOD_PUBLISH = 'PUBLISH';
    private const ICAL_METHOD_REPLY = 'REPLY';
    private const ICAL_METHOD_ADD = 'ADD';
    private const ICAL_METHOD_CANCEL = 'CANCEL';
    private const ICAL_METHOD_REFRESH = 'REFRESH';
    private const ICAL_METHOD_COUNTER = 'COUNTER';
    private const ICAL_METHOD_DECLINECOUNTER = 'DECLINECOUNTER';
    private $Priority;
    private string $ContentType = self::CONTENT_TYPE_TEXT_HTML;
    private string $Encoding = self::ENCODING_8BIT;
    private string $ErrorInfo = '';
    private string $Sender = '';
    private string $AltBody = '';
    private string $Ical = '';
    private static array $IcalMethods = [
        self::ICAL_METHOD_REQUEST,
        self::ICAL_METHOD_PUBLISH,
        self::ICAL_METHOD_REPLY,
        self::ICAL_METHOD_ADD,
        self::ICAL_METHOD_CANCEL,
        self::ICAL_METHOD_REFRESH,
        self::ICAL_METHOD_COUNTER,
        self::ICAL_METHOD_DECLINECOUNTER
    ];
    private string $MIMEBody = '';
    private bool $SMTPAuth = true;
    private string $CharSet = self::CHARSET_UTF8;
    private string $MIMEHeader = '';
    private string $mailHeader = '';
    private int $WordWrap = 0;
    private string $Mailer = 'smtp';
    private string $Sendmail = '/usr/sbin/sendmail';
    private bool $UseSendmailOptions = true;
    private string $ConfirmReadingTo = '';
    private string $Hostname = '';
    private string $MessageID = '';
    private string $MessageDate = '';
    private string $Helo = '';
    private string $SMTPSecure = '';
    private bool $SMTPAutoTLS = true;
    private array $SMTPOptions = [];
    private string $AuthType = '';
    private $oauth;
    private int $Timeout = 300;
    private string $dsn = '';
    private int $SMTPDebug = 0;
    private string $DebugOutput;
    private bool $SMTPKeepAlive = false;
    private bool $SingleTo = false;
    private array $SingleToArray = [];
    private bool $do_verp = false;
    private bool $AllowEmpty = false;
    private string $DKIM_selector = '';
    private string $DKIM_identity = '';
    private string $DKIM_passphrase = '';
    private string $DKIM_domain = '';
    private bool $DKIM_copyHeaderFields = true;
    private array $DKIM_extraHeaders = [];
    private string $DKIM_private = '';
    private string $DKIM_private_string = '';
    private string $action_function = '';
    private string $XMailer = '';
    private static string $validator = 'php';
    private $smtp;
    private array $to = [];
    private array $cc = [];
    private array $bcc = [];
    private array $ReplyTo = [];
    private array $all_recipients = [];
    private array $RecipientsQueue = [];
    private array $ReplyToQueue = [];
    private array $attachment = [];
    private array $CustomHeader = [];
    private string $message_type = '';
    private array $boundary = [];
    private int $error_count = 0;
    private string $sign_cert_file = '';
    private string $sign_key_file = '';
    private string $sign_extra_certs_file = '';
    private string $sign_key_pass = '';
    private bool $exceptions = false;
    private string $uniqueId = '';
    private const VERSION = '6.6.3';
    //private const STOP_MESSAGE = 0;
    private const STOP_CONTINUE = 1;
    private const STOP_CRITICAL = 2;
    private const CRLF = "\r\n";
    private const FWS = ' ';
    private static string $LE = self::CRLF;
    private const MAIL_MAX_LINE_LENGTH = 63;
    private const MAX_LINE_LENGTH = 998;
    private const STD_LINE_LENGTH = 76;
    private array $language = [
        'authenticate' => 'SMTP Error: Could not authenticate.',
        'buggy_php' => 'Your version of PHP is affected by a bug that may result in corrupted messages.' .
            ' To fix it, switch to sending using SMTP, disable the mail.add_x_header option in' .
            ' your php.ini, switch to MacOS or Linux, or upgrade your PHP to version 7.0.17+ or 7.1.3+.',
        'connect_host' => 'SMTP Error: Could not connect to SMTP host.',
        'data_not_accepted' => 'SMTP Error: data not accepted.',
        'empty_message' => 'Message body empty',
        'encoding' => 'Unknown encoding: ',
        'execute' => 'Could not execute: ',
        'extension_missing' => 'Extension missing: ',
        'file_access' => 'Could not access file: ',
        'file_open' => 'File Error: Could not open file: ',
        'from_failed' => 'The following From address failed: ',
        'instantiate' => 'Could not instantiate mail function.',
        'invalid_address' => 'Invalid address: ',
        'invalid_header' => 'Invalid header name or value',
        'invalid_hostentry' => 'Invalid hostentry: ',
        'invalid_host' => 'Invalid host: ',
        'mailer_not_supported' => ' mailer is not supported.',
        'provide_address' => 'You must provide at least one recipient email address.',
        'recipients_failed' => 'SMTP Error: The following recipients failed: ',
        'signing' => 'Signing Error: ',
        'smtp_code' => 'SMTP code: ',
        'smtp_code_ex' => 'Additional SMTP info: ',
        'smtp_connect_failed' => 'SMTP connect() failed.',
        'smtp_detail' => 'Detail: ',
        'smtp_error' => 'SMTP server error: ',
        'variable_set' => 'Cannot set or reset variable: ',
    ];

    public function __construct($exceptions = null)
    {
        if (null !== $exceptions) {
            $this->exceptions = (bool)$exceptions;
        }
        $this->DebugOutput = (strpos(PHP_SAPI, 'cli') !== false ? 'echo' : 'html');
    }

    public function __destruct()
    {
        $this->smtpClose();
    }

    /**
     * @return bool|mixed
     * @throws Exception
     */
    public function addAddress($address, $name = '')
    {
        return $this->addOrEnqueueAnAddress($address, $name);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function send()
    {
        try {
            if (!$this->preSend()) {
                return false;
            }
            return $this->postSend();
        } catch (Exception $exc) {
            $this->mailHeader = '';
            $this->setError($exc->getMessage());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
    }

    private function mailPassThru($to, $subject, $body, $header, $params)
    {
        $subject = ini_get('mbstring.func_overload') & 1 ?
            $this->secureHeader($subject) :
            $this->encodeHeader($this->secureHeader($subject));
        $this->edebug('Sending with mail()');
        $this->edebug('Sendmail path: ' . ini_get('sendmail_path'));
        $this->edebug('Envelope sender: ' . $this->Sender);
        $this->edebug('To: ' . $to);
        $this->edebug('Subject: ' . $subject);
        $this->edebug('Headers: ' . $header);
        if (!$this->UseSendmailOptions || null === $params) {
            $result = @mail($to, $subject, $body, $header);
        } else {
            $this->edebug('Additional params: ' . $params);
            $result = @mail($to, $subject, $body, $header, $params);
        }
        $this->edebug('Result: ' . ($result ? 'true' : 'false'));
        return $result;
    }

    private function edebug($str)
    {
        if ($this->SMTPDebug <= 0) {
            return;
        }
        if (is_callable($this->DebugOutput) && !in_array($this->DebugOutput, ['error_log', 'html', 'echo'])) {
            call_user_func($this->DebugOutput, $str, $this->SMTPDebug);
            return;
        }
        switch ($this->DebugOutput) {
            case 'html':
                echo htmlentities(preg_replace('/[\r\n]+/', '', $str), ENT_QUOTES, 'UTF-8'), "<br>\n";
                break;
            default:
                $str = preg_replace('/\r\n|\r/m', "\n", $str);
                echo gmdate('Y-m-d H:i:s'), "\t",
                    trim(str_replace("\n", "\n                   \t                  ", trim($str))), "\n";
        }
    }

    /**
     * @return bool|mixed
     * @throws Exception
     */
    private function addOrEnqueueAnAddress($address, $name)
    {
        $pos = false;
        if ($address !== null) {
            $address = trim($address);
            $pos = strrpos($address, '@');
        }
        if (false === $pos) {
            $error_message = sprintf('%s (%s): %s', $this->lang('invalid_address'), 'to', $address);
            $this->setError($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        if ($name !== null) {
            $name = trim(preg_replace('/[\r\n]+/', '', $name));
        } else {
            $name = '';
        }
        $params = ['to', $address, $name];
        if (static::idnSupported() && $this->has8bitChars(substr($address, ++$pos))) {
            if ('Reply-To' !== 'to') {
                if (!array_key_exists($address, $this->RecipientsQueue)) {
                    $this->RecipientsQueue[$address] = $params;
                    return true;
                }
            } elseif (!array_key_exists($address, $this->ReplyToQueue)) {
                $this->ReplyToQueue[$address] = $params;
                return true;
            }
            return false;
        }
        return call_user_func_array([$this, 'addAnAddress'], $params);
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function addAnAddress($kind, $address)
    {
        if (!in_array($kind, ['to', 'cc', 'bcc', 'Reply-To'])) {
            $error_message = sprintf('%s: %s', $this->lang('Invalid recipient kind'), $kind);
            $this->setError($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        if (!static::validateAddress($address)) {
            $error_message = sprintf('%s (%s): %s', $this->lang('invalid_address'), $kind, $address);
            $this->setError($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        if ('Reply-To' !== $kind) {
            if (!array_key_exists(strtolower($address), $this->all_recipients)) {
                $this->{$kind}[] = [$address, ''];
                $this->all_recipients[strtolower($address)] = true;
                return true;
            }
        } elseif (!array_key_exists(strtolower($address), $this->ReplyTo)) {
            $this->ReplyTo[strtolower($address)] = [$address,
                                                    ''
            ];
            return true;
        }
        return false;
    }

    private static function parseAddresses($addrStr, $charset = self::CHARSET_ISO88591)
    {
        $addresses = [];
        if (function_exists('imap_rfc822_parse_adrlist')) {
            $list = imap_rfc822_parse_adrlist($addrStr, '');
            imap_errors();
            foreach ($list as $address) {
                if (
                    '.SYNTAX-ERROR.' !== $address->host &&
                    static::validateAddress($address->mailbox . '@' . $address->host)
                ) {
                    if (
                        property_exists($address, 'personal') && defined('MB_CASE_UPPER') &&
                        preg_match('/^=\?.*\?=$/s', $address->personal)
                    ) {
                        $origCharset = mb_internal_encoding();
                        mb_internal_encoding($charset);
                        $address->personal = str_replace('_', '=20', $address->personal);
                        $address->personal = mb_decode_mimeheader($address->personal);
                        mb_internal_encoding($origCharset);
                    }
                    $addresses[] = [
                        'name' => (property_exists($address, 'personal') ? $address->personal : ''),
                        'address' => $address->mailbox . '@' . $address->host
                    ];
                }
            }
        } else {
            $list = explode(',', $addrStr);
            foreach ($list as $address) {
                $address = trim($address);
                if (strpos($address, '<') === false) {
                    if (static::validateAddress($address)) {
                        $addresses[] = ['name' => '', 'address' => $address];
                    }
                } else {
                    [$name, $email] = explode('<', $address);
                    $email = trim(str_replace('>', '', $email));
                    $name = trim($name);
                    if (static::validateAddress($email)) {
                        if (defined('MB_CASE_UPPER') && preg_match('/^=\?.*\?=$/s', $name)) {
                            $origCharset = mb_internal_encoding();
                            mb_internal_encoding($charset);
                            $name = str_replace('_', '=20', $name);
                            $name = mb_decode_mimeheader($name);
                            mb_internal_encoding($origCharset);
                        }
                        $addresses[] = ['name' => trim($name, '\'" '), 'address' => $email];
                    }
                }
            }
        }
        return $addresses;
    }

    private static function validateAddress($address)
    {
        $patternSelect = static::$validator;
        if (is_callable($patternSelect) && !is_string($patternSelect)) {
            return call_user_func($patternSelect, $address);
        }
        if (strpos($address, "\n") !== false || strpos($address, "\r") !== false) {
            return false;
        }
        switch ($patternSelect) {
            case 'pcre':
            case 'pcre8':
                return (bool)preg_match('/^(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)' .
                    '"?(?>\\\[ -~]|[^"])"?(?1)){65,}@)((?>(?>(?>((?>(?>(?>\x0D\x0A)?[\t ])+|(?>[\t ]*\x0D\x0A)' .
                    '?[\t ]+)?)(\((?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))'
                    . '|(?2))?)([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*'
                    . '(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' .
                    '(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}' .
                    '|(?!(?:.*[a-f0-9][:\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:' .
                    '|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
                    '|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD', $address);
            case 'html5':
                return (bool)preg_match('/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' .
                    '[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/D', $address);
            default:
                return filter_var($address, FILTER_VALIDATE_EMAIL) !== false;
        }
    }

    private static function idnSupported()
    {
        return function_exists('idn_to_ascii') && function_exists('mb_convert_encoding');
    }

    private function punyEnCodeAddress($address)
    {
        $pos = strrpos($address, '@');
        if (!empty($this->CharSet) && false !== $pos && static::idnSupported()) {
            $domain = substr($address, ++$pos);
            if ($this->has8bitChars($domain) && @mb_check_encoding($domain, $this->CharSet)) {
                $domain = mb_convert_encoding($domain, self::CHARSET_UTF8, $this->CharSet);
                $errorCode = 0;
                if (defined('INTL_IDNA_VARIANT_UTS46')) {
                    $punycode = idn_to_ascii(
                        $domain,
                        IDNA_DEFAULT | IDNA_USE_STD3_RULES | IDNA_CHECK_BIDI | IDNA_CHECK_CONTEXTJ |
                        IDNA_NONTRANSITIONAL_TO_ASCII
                    );
                } elseif (defined('INTL_IDNA_VARIANT_2003')) {
                    $punycode = idn_to_ascii($domain, $errorCode, INTL_IDNA_VARIANT_2003);
                } else {
                    $punycode = idn_to_ascii($domain, $errorCode);
                }
                if (false !== $punycode) {
                    return substr($address, 0, $pos) . $punycode;
                }
            }
        }
        return $address;
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function preSend()
    {
        'smtp' === $this->Mailer ||
        ('mail' === $this->Mailer && (PHP_VERSION_ID >= 80000 || stripos(PHP_OS, 'WIN') === 0)) ?
            static::setLE(self::CRLF) : static::setLE(PHP_EOL);
        if (
            'mail' === $this->Mailer && ((PHP_VERSION_ID >= 70000 && PHP_VERSION_ID < 70017) ||
                (PHP_VERSION_ID >= 70100 && PHP_VERSION_ID < 70103)) && ini_get('mail.add_x_header') === '1' &&
            stripos(PHP_OS, 'WIN') === 0
        ) {
            trigger_error($this->lang('buggy_php'), E_USER_WARNING);
        }
        try {
            $this->error_count = 0;
            $this->mailHeader = '';
            foreach (array_merge($this->RecipientsQueue, $this->ReplyToQueue) as $params) {
                $params[1] = $this->punyEnCodeAddress($params[1]);
                call_user_func_array([$this, 'addAnAddress'], $params);
            }
            if (count($this->to) + count($this->cc) + count($this->bcc) < 1) {
                throw new Exception($this->lang('provide_address'), self::STOP_CRITICAL);
            }
            foreach (['From', 'Sender', 'ConfirmReadingTo'] as $address_kind) {
                $this->{$address_kind} = trim($this->{$address_kind});
                if (empty($this->{$address_kind})) {
                    continue;
                }
                $this->{$address_kind} = $this->punyEnCodeAddress($this->{$address_kind});
                if (!static::validateAddress($this->{$address_kind})) {
                    $error_message = sprintf(
                        '%s (%s): %s',
                        $this->lang('invalid_address'),
                        $address_kind,
                        $this->{$address_kind}
                    );
                    $this->setError($error_message);
                    $this->edebug($error_message);
                    if ($this->exceptions) {
                        throw new Exception($error_message);
                    }
                    return false;
                }
            }
            if ($this->alternativeExists()) {
                $this->ContentType = static::CONTENT_TYPE_MULTIPART_ALTERNATIVE;
            }
            $this->setMessageType();
            if (!$this->AllowEmpty && empty($this->Body)) {
                throw new Exception($this->lang('empty_message'), self::STOP_CRITICAL);
            }
            $this->Subject = trim($this->Subject);
            $this->MIMEHeader = '';
            $this->MIMEBody = $this->createBody();
            $tempHeaders = $this->MIMEHeader;
            $this->MIMEHeader = $this->createHeader();
            $this->MIMEHeader .= $tempHeaders;
            if ('mail' === $this->Mailer) {
                if (count($this->to) > 0) {
                    $this->mailHeader .= $this->addrAppend('To', $this->to);
                } else {
                    $this->mailHeader .= $this->headerLine('To', 'undisclosed-recipients:;');
                }
                $this->mailHeader .=
                    $this->headerLine('Subject', $this->encodeHeader($this->secureHeader($this->Subject)));
            }
            if (
                !empty($this->DKIM_domain) && !empty($this->DKIM_selector) && (!empty($this->DKIM_private_string) ||
                    (!empty($this->DKIM_private) && static::isPermittedPath($this->DKIM_private) &&
                        file_exists($this->DKIM_private)))
            ) {
                $header_dkim = $this->dkimAdd(
                    $this->MIMEHeader . $this->mailHeader,
                    $this->encodeHeader($this->secureHeader($this->Subject)),
                    $this->MIMEBody
                );
                $this->MIMEHeader = static::stripTrailingWSP($this->MIMEHeader) . static::$LE .
                    static::normalizeBreaks($header_dkim) . static::$LE;
            }
            return true;
        } catch (Exception $exc) {
            $this->setError($exc->getMessage());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function postSend()
    {
        try {
            switch ($this->Mailer) {
                case 'sendmail':
                case 'qmail':
                    return $this->sendmailSend($this->MIMEHeader, $this->MIMEBody);
                case 'smtp':
                    return $this->smtpSend($this->MIMEHeader, $this->MIMEBody);
                case 'mail':
                    return $this->mailSend($this->MIMEHeader, $this->MIMEBody);
                default:
                    $sendMethod = $this->Mailer . 'Send';
                    if (method_exists($this, $sendMethod)) {
                        return $this->{$sendMethod}($this->MIMEHeader, $this->MIMEBody);
                    }
                    return $this->mailSend($this->MIMEHeader, $this->MIMEBody);
            }
        } catch (Exception $exc) {
            if ($this->Mailer === 'smtp' && $this->SMTPKeepAlive) {
                $this->smtp->reset();
            }
            $this->setError($exc->getMessage());
            $this->edebug($exc->getMessage());
            if ($this->exceptions) {
                throw $exc;
            }
        }
        return false;
    }

    /**
     * @param $header
     * @param $body
     * @return true
     * @throws Exception
     */
    private function sendmailSend($header, $body)
    {
        if ($this->Mailer === 'qmail') {
            $this->edebug('Sending with qmail');
        } else {
            $this->edebug('Sending with sendmail');
        }
        $header = static::stripTrailingWSP($header) . static::$LE . static::$LE;
        $sendmail_from_value = ini_get('sendmail_from');
        if (empty($this->Sender) && !empty($sendmail_from_value)) {
            $this->Sender = ini_get('sendmail_from');
        }
        if (!empty($this->Sender) && static::validateAddress($this->Sender) && self::isShellSafe($this->Sender)) {
            $sendmailFmt = $this->Mailer === 'qmail' ? '%s -f%s' : '%s -oi -f%s -t';
        } else {
            $sendmailFmt = '%s -oi -t';
        }
        $sendmail = sprintf($sendmailFmt, escapeshellcmd($this->Sendmail), $this->Sender);
        $this->edebug('Sendmail path: ' . $this->Sendmail);
        $this->edebug('Sendmail command: ' . $sendmail);
        $this->edebug('Envelope sender: ' . $this->Sender);
        $this->edebug('Headers: ' . $header);
        if ($this->SingleTo) {
            foreach ($this->SingleToArray as $toAddr) {
                $mail = @popen($sendmail, 'w');
                if (!$mail) {
                    throw new Exception($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
                }
                $this->edebug('To: ' . $toAddr);
                fwrite($mail, 'To: ' . $toAddr . "\n");
                fwrite($mail, $header);
                fwrite($mail, $body);
                $result = pclose($mail);
                $addrInfo = static::parseAddresses($toAddr, $this->CharSet);
                $this->doCallback(
                    $result === 0,
                    [[$addrInfo['address'], $addrInfo['name']]],
                    $this->cc,
                    $this->bcc,
                    $this->Subject,
                    $body,
                    $this->From,
                    []
                );
                $this->edebug("Result: " . ($result === 0 ? 'true' : 'false'));
                if (0 !== $result) {
                    throw new Exception($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
                }
            }
        } else {
            $mail = @popen($sendmail, 'w');
            if (!$mail) {
                throw new Exception($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
            }
            fwrite($mail, $header);
            fwrite($mail, $body);
            $result = pclose($mail);
            $this->doCallback($result === 0, $this->to, $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);
            $this->edebug("Result: " . ($result === 0 ? 'true' : 'false'));
            if (0 !== $result) {
                throw new Exception($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
            }
        }
        return true;
    }

    private static function isShellSafe($string)
    {
        if (!function_exists('escapeshellarg') || !function_exists('escapeshellcmd')) {
            return false;
        }
        if (
            escapeshellcmd($string) !== $string ||
            !in_array(escapeshellarg($string), ["'" . $string . "'", '"' . $string . '"'])
        ) {
            return false;
        }
        $length = strlen($string);
        for ($i = 0; $i < $length; ++$i) {
            $c = $string[$i];
            if (!ctype_alnum($c) && strpos('@_-.', $c) === false) {
                return false;
            }
        }
        return true;
    }

    private static function isPermittedPath($path)
    {
        return !preg_match('#^[a-z][a-z\d+.-]*://#i', $path);
    }

    private static function fileIsAccessible($path)
    {
        if (!static::isPermittedPath($path)) {
            return false;
        }
        $readable = file_exists($path);
        if (strpos($path, '\\\\') !== 0) {
            $readable = $readable && is_readable($path);
        }
        return $readable;
    }

    /**
     * @param $header
     * @param $body
     * @return true
     * @throws Exception
     */
    private function mailSend($header, $body)
    {
        $header = static::stripTrailingWSP($header) . static::$LE . static::$LE;

        $toArr = [];
        foreach ($this->to as $toAddr) {
            $toArr[] = $this->addrFormat($toAddr);
        }
        $to = implode(', ', $toArr);
        $params = null;
        $sendmail_from_value = ini_get('sendmail_from');
        if (empty($this->Sender) && !empty($sendmail_from_value)) {
            $this->Sender = ini_get('sendmail_from');
        }
        if (!empty($this->Sender) && static::validateAddress($this->Sender)) {
            if (self::isShellSafe($this->Sender)) {
                $params = sprintf('-f%s', $this->Sender);
            }
            $old_from = ini_get('sendmail_from');
            ini_set('sendmail_from', $this->Sender);
        }
        $result = false;
        if ($this->SingleTo && count($toArr) > 1) {
            foreach ($toArr as $toAddr) {
                $result = $this->mailPassThru($toAddr, $this->Subject, $body, $header, $params);
                $addrinfo = static::parseAddresses($toAddr, $this->CharSet);
                $this->doCallback($result, [
                    [
                        $addrinfo['address'],
                        $addrinfo['name']
                    ]
                ], $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);
            }
        } else {
            $result = $this->mailPassThru($to, $this->Subject, $body, $header, $params);
            $this->doCallback($result, $this->to, $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);
        }
        if (isset($old_from)) {
            ini_set('sendmail_from', $old_from);
        }
        if (!$result) {
            throw new Exception($this->lang('instantiate'), self::STOP_CRITICAL);
        }

        return true;
    }

    private function getSMTPInstance()
    {
        if (!is_object($this->smtp)) {
            $this->smtp = new SMTP();
        }
        return $this->smtp;
    }

    /**
     * @param $header
     * @param $body
     * @return true
     * @throws Exception
     */
    private function smtpSend($header, $body)
    {
        $header = static::stripTrailingWSP($header) . static::$LE . static::$LE;
        $bad_rcpt = [];
        if (!$this->smtpConnect($this->SMTPOptions)) {
            throw new Exception($this->lang('smtp_connect_failed'), self::STOP_CRITICAL);
        }
        if ('' === $this->Sender) {
            $smtp_from = $this->From;
        } else {
            $smtp_from = $this->Sender;
        }
        if (!$this->smtp->mail($smtp_from)) {
            $this->setError($this->lang('from_failed') . $smtp_from . ' : ' . implode(',', $this->smtp->getError()));
            throw new Exception($this->ErrorInfo, self::STOP_CRITICAL);
        }
        $callbacks = [];
        foreach ([$this->to, $this->cc, $this->bcc] as $toGroup) {
            foreach ($toGroup as $to) {
                if (!$this->smtp->recipient($to[0], $this->dsn)) {
                    $error = $this->smtp->getError();
                    $bad_rcpt[] = ['to' => $to[0], 'error' => $error['detail']];
                    $isSent = false;
                } else {
                    $isSent = true;
                }
                $callbacks[] = ['issent' => $isSent, 'to' => $to[0], 'name' => $to[1]];
            }
        }
        if ((count($this->all_recipients) > count($bad_rcpt)) && !$this->smtp->data($header . $body)) {
            throw new Exception($this->lang('data_not_accepted'), self::STOP_CRITICAL);
        }
        $smtp_transaction_id = $this->smtp->getLastTransactionID();
        if ($this->SMTPKeepAlive) {
            $this->smtp->reset();
        } else {
            $this->smtp->quit();
            $this->smtp->close();
        }
        foreach ($callbacks as $cb) {
            $this->doCallback(
                $cb['issent'],
                [[$cb['to'], $cb['name']]],
                [],
                [],
                $this->Subject,
                $body,
                $this->From,
                ['smtp_transaction_id' => $smtp_transaction_id]
            );
        }
        if (count($bad_rcpt) > 0) {
            $errStr = '';
            foreach ($bad_rcpt as $bad) {
                $errStr .= $bad['to'] . ': ' . $bad['error'];
            }
            throw new Exception($this->lang('recipients_failed') . $errStr, self::STOP_CONTINUE);
        }
        return true;
    }

    /**
     * @param $options
     * @return bool
     * @throws Exception
     */
    private function smtpConnect($options = null)
    {
        if (null === $this->smtp) {
            $this->smtp = $this->getSMTPInstance();
        }
        if (null === $options) {
            $options = $this->SMTPOptions;
        }
        if ($this->smtp->connected()) {
            return true;
        }
        $this->smtp->setTimeout($this->Timeout);
        $this->smtp->setDebugLevel($this->SMTPDebug);
        $this->smtp->setDebugOutput($this->DebugOutput);
        $this->smtp->setVerp($this->do_verp);
        $hosts = explode(';', $this->Host);
        $lastException = null;
        foreach ($hosts as $hostEntry) {
            $hostInfo = [];
            if (!preg_match('/^(?:(ssl|tls):\/\/)?(.+?)(?::(\d+))?$/', trim($hostEntry), $hostInfo)) {
                $this->edebug($this->lang('invalid_hostentry') . ' ' . trim($hostEntry));
                continue;
            }
            if (!static::isValidHost($hostInfo[2])) {
                $this->edebug($this->lang('invalid_host') . ' ' . $hostInfo[2]);
                continue;
            }
            $prefix = '';
            $secure = $this->SMTPSecure;
            $tls = (static::ENCRYPTION_STARTTLS === $this->SMTPSecure);
            if ('ssl' === $hostInfo[1] || ('' === $hostInfo[1] && static::ENCRYPTION_SMTPS === $this->SMTPSecure)) {
                $prefix = 'ssl://';
                $tls = false;
                $secure = static::ENCRYPTION_SMTPS;
            } elseif ('tls' === $hostInfo[1]) {
                $tls = true;
                $secure = static::ENCRYPTION_STARTTLS;
            }
            $sslExt = defined('OPENSSL_ALGO_SHA256');
            if (static::ENCRYPTION_STARTTLS === $secure || static::ENCRYPTION_SMTPS === $secure) {
                if (!$sslExt) {
                    throw new Exception($this->lang('extension_missing') . 'openssl', self::STOP_CRITICAL);
                }
            }
            $host = $hostInfo[2];
            $port = $this->Port;
            if (
                array_key_exists(3, $hostInfo) && is_numeric($hostInfo[3]) && $hostInfo[3] > 0 && $hostInfo[3] < 65536
            ) {
                $port = (int)$hostInfo[3];
            }
            if ($this->smtp->connect($prefix . $host, $port, $this->Timeout, $options)) {
                try {
                    $hello = $this->Helo ?: $this->serverHostname();
                    $this->smtp->hello($hello);
                    if ($this->SMTPAutoTLS && $sslExt && 'ssl' !== $secure && $this->smtp->getServerExt('STARTTLS')) {
                        $tls = true;
                    }
                    if ($tls) {
                        if (!$this->smtp->startTLS()) {
                            $message = $this->getSmtpErrorMessage();
                            throw new Exception($message);
                        }
                        $this->smtp->hello($hello);
                    }
                    if (
                        $this->SMTPAuth &&
                        !$this->smtp->authenticate($this->Username, $this->Password, $this->AuthType, $this->oauth)
                    ) {
                        throw new Exception($this->lang('authenticate'));
                    }
                    return true;
                } catch (Exception $exc) {
                    $lastException = $exc;
                    $this->edebug($exc->getMessage());
                    $this->smtp->quit();
                }
            }
        }
        $this->smtp->close();
        if ($this->exceptions && null !== $lastException) {
            throw $lastException;
        }
        if ($this->exceptions) {
            throw new Exception($this->getSmtpErrorMessage());
        }
        return false;
    }

    private function smtpClose()
    {
        if ((null !== $this->smtp) && $this->smtp->connected()) {
            $this->smtp->quit();
            $this->smtp->close();
        }
    }

    private function addrAppend($type, $addr)
    {
        $addresses = [];
        foreach ($addr as $address) {
            $addresses[] = $this->addrFormat($address);
        }
        return $type . ': ' . implode(', ', $addresses) . static::$LE;
    }

    private function addrFormat($addr)
    {
        return empty($addr[1]) ?
            $this->secureHeader($addr[0]) :
            $this->encodeHeader($this->secureHeader($addr[1]), 'phrase') . ' <' . $this->secureHeader($addr[0]) . '>';
    }

    private function wrapText($message, $length, $qp_mode = false)
    {
        if ($qp_mode) {
            $soft_break = sprintf(' =%s', static::$LE);
        } else {
            $soft_break = static::$LE;
        }
        $is_utf8 = static::CHARSET_UTF8 === strtolower($this->CharSet);
        $leLen = strlen(static::$LE);
        $crlfLen = strlen(static::$LE);
        $message = static::normalizeBreaks($message);
        if (substr($message, -$leLen) === static::$LE) {
            $message = substr($message, 0, -$leLen);
        }
        $lines = explode(static::$LE, $message);
        $message = '';
        foreach ($lines as $line) {
            $words = explode(' ', $line);
            $buf = '';
            $firstWord = true;
            foreach ($words as $word) {
                if ($qp_mode && (strlen($word) > $length)) {
                    $space_left = $length - strlen($buf) - $crlfLen;
                    if (!$firstWord) {
                        if ($space_left > 20) {
                            $len = $space_left;
                            if ($is_utf8) {
                                $len = $this->utf8CharBoundary($word, $len);
                            } elseif ('=' === substr($word, $len - 1, 1)) {
                                --$len;
                            } elseif ('=' === substr($word, $len - 2, 1)) {
                                $len -= 2;
                            }
                            $part = substr($word, 0, $len);
                            $word = substr($word, $len);
                            $buf .= ' ' . $part;
                            $message .= $buf . sprintf('=%s', static::$LE);
                        } else {
                            $message .= $buf . $soft_break;
                        }
                        $buf = '';
                    }
                    while ($word !== '') {
                        if ($length <= 0) {
                            break;
                        }
                        $len = $length;
                        if ($is_utf8) {
                            $len = $this->utf8CharBoundary($word, $len);
                        } elseif ('=' === substr($word, $len - 1, 1)) {
                            --$len;
                        } elseif ('=' === substr($word, $len - 2, 1)) {
                            $len -= 2;
                        }
                        $part = substr($word, 0, $len);
                        $word = (string)substr($word, $len);
                        if ($word !== '') {
                            $message .= $part . sprintf('=%s', static::$LE);
                        } else {
                            $buf = $part;
                        }
                    }
                } else {
                    $buf_o = $buf;
                    if (!$firstWord) {
                        $buf .= ' ';
                    }
                    $buf .= $word;
                    if ('' !== $buf_o && strlen($buf) > $length) {
                        $message .= $buf_o . $soft_break;
                        $buf = $word;
                    }
                }
                $firstWord = false;
            }
            $message .= $buf . static::$LE;
        }
        return $message;
    }

    private function utf8CharBoundary($encodedText, $maxLength)
    {
        $foundSplitPos = false;
        $lookBack = 3;
        while (!$foundSplitPos) {
            $lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
            $encodedCharPos = strpos($lastChunk, '=');
            if (false !== $encodedCharPos) {
                $hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
                $dec = hexdec($hex);
                if ($dec < 128) {
                    if ($encodedCharPos > 0) {
                        $maxLength -= $lookBack - $encodedCharPos;
                    }
                    $foundSplitPos = true;
                } elseif ($dec >= 192) {
                    $maxLength -= $lookBack - $encodedCharPos;
                    $foundSplitPos = true;
                } else {
                    $lookBack += 3;
                }
            } else {
                $foundSplitPos = true;
            }
        }
        return $maxLength;
    }

    private function setWordWrap()
    {
        if ($this->WordWrap < 1) {
            return;
        }
        switch ($this->message_type) {
            case 'alt':
            case 'alt_inline':
            case 'alt_attach':
            case 'alt_inline_attach':
                $this->AltBody = $this->wrapText($this->AltBody, $this->WordWrap);
                break;
            default:
                $this->Body = $this->wrapText($this->Body, $this->WordWrap);
                break;
        }
    }

    private function createHeader()
    {
        $result = $this->headerLine('Date', '' === $this->MessageDate ? self::rfcDate() : $this->MessageDate);
        if ('mail' !== $this->Mailer) {
            if ($this->SingleTo) {
                foreach ($this->to as $toAddr) {
                    $this->SingleToArray[] = $this->addrFormat($toAddr);
                }
            } elseif (count($this->to) > 0) {
                $result .= $this->addrAppend('To', $this->to);
            } elseif (count($this->cc) === 0) {
                $result .= $this->headerLine('To', 'undisclosed-recipients:;');
            }
        }
        $result .= $this->addrAppend('From', [[trim($this->From), $this->FromName]]);
        if (count($this->cc) > 0) {
            $result .= $this->addrAppend('Cc', $this->cc);
        }
        if (
            ('sendmail' === $this->Mailer || 'qmail' === $this->Mailer || 'mail' === $this->Mailer) &&
            count($this->bcc) > 0
        ) {
            $result .= $this->addrAppend('Bcc', $this->bcc);
        }
        if (count($this->ReplyTo) > 0) {
            $result .= $this->addrAppend('Reply-To', $this->ReplyTo);
        }
        if ('mail' !== $this->Mailer) {
            $result .= $this->headerLine('Subject', $this->encodeHeader($this->secureHeader($this->Subject)));
        }
        if (
            '' !== $this->MessageID &&
            preg_match('/^<((([a-z\d!#$%&\'*+\/=?^_`{|}~-]+(\.[a-z\d!#$%&\'*+\/=?^_`{|}~-]+)*)' .
                '|("(([\x01-\x08\x0B\x0C\x0E-\x1F\x7F]|[\x21\x23-\x5B\x5D-\x7E])' .
                '|(\\[\x01-\x09\x0B\x0C\x0E-\x7F]))*"))@(([a-z\d!#$%&\'*+\/=?^_`{|}~-]+' .
                '(\.[a-z\d!#$%&\'*+\/=?^_`{|}~-]+)*)|(\[(([\x01-\x08\x0B\x0C\x0E-\x1F\x7F]' .
                '|[\x21-\x5A\x5E-\x7E])|(\\[\x01-\x09\x0B\x0C\x0E-\x7F]))*])))>$/Di', $this->MessageID)
        ) {
            $lastMessageID = $this->MessageID;
        } else {
            $lastMessageID = sprintf('<%s@%s>', $this->uniqueId, $this->serverHostname());
        }
        $result .= $this->headerLine('Message-ID', $lastMessageID);
        if (null !== $this->Priority) {
            $result .= $this->headerLine('X-Priority', $this->Priority);
        }
        if ('' === $this->XMailer) {
            $result .= $this->headerLine('X-Mailer', 'PHPMailer ' . self::VERSION .
                ' (https://github.com/PHPMailer/PHPMailer)');
        } elseif (trim($this->XMailer) !== '') {
            $result .= $this->headerLine('X-Mailer', trim($this->XMailer));
        }
        if ('' !== $this->ConfirmReadingTo) {
            $result .= $this->headerLine('Disposition-Notification-To', '<' . $this->ConfirmReadingTo . '>');
        }
        foreach ($this->CustomHeader as $header) {
            $result .= $this->headerLine(trim($header[0]), $this->encodeHeader(trim($header[1])));
        }
        if (!$this->sign_key_file) {
            $result .= $this->headerLine('MIME-Version', '1.0') . $this->getMailMIME();
        }
        return $result;
    }

    private function getMailMIME()
    {
        $result = '';
        $isMultipart = true;
        switch ($this->message_type) {
            case 'inline':
                $result .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                $result .= $this->textLine(' boundary="' . $this->boundary[1] . '"');
                break;
            case 'attach':
            case 'inline_attach':
            case 'alt_attach':
            case 'alt_inline_attach':
                $result .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_MIXED . ';');
                $result .= $this->textLine(' boundary="' . $this->boundary[1] . '"');
                break;
            case 'alt':
            case 'alt_inline':
                $result .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';');
                $result .= $this->textLine(' boundary="' . $this->boundary[1] . '"');
                break;
            default:
                $result .= $this->textLine('Content-Type: ' . $this->ContentType . '; charset=' . $this->CharSet);
                $isMultipart = false;
                break;
        }
        if (static::ENCODING_7BIT !== $this->Encoding) {
            if ($isMultipart) {
                if (static::ENCODING_8BIT === $this->Encoding) {
                    $result .= $this->headerLine('Content-Transfer-Encoding', static::ENCODING_8BIT);
                }
            } else {
                $result .= $this->headerLine('Content-Transfer-Encoding', $this->Encoding);
            }
        }
        return $result;
    }

    private function generateId()
    {
        $len = 32;
        $bytes = '';
        if (function_exists('random_bytes')) {
            try {
                $bytes = random_bytes($len);
            } catch (Exception $e) {
            }
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($len);
        }
        if ($bytes === '') {
            $bytes = hash('sha256', uniqid((string)mt_rand(), true), true);
        }
        return str_replace(['=', '+', '/'], '', base64_encode(hash('sha256', $bytes, true)));
    }

    /**
     * @return mixed|string
     * @throws Exception
     */
    private function createBody()
    {
        $body = '';
        $this->uniqueId = $this->generateId();
        $this->boundary[1] = 'b1_' . $this->uniqueId;
        $this->boundary[2] = 'b2_' . $this->uniqueId;
        $this->boundary[3] = 'b3_' . $this->uniqueId;
        if ($this->sign_key_file) {
            $body .= $this->getMailMIME() . static::$LE;
        }
        $this->setWordWrap();
        $bodyEncoding = $this->Encoding;
        $bodyCharSet = $this->CharSet;
        if (static::ENCODING_8BIT === $bodyEncoding && !$this->has8bitChars($this->Body)) {
            $bodyEncoding = static::ENCODING_7BIT;
            $bodyCharSet = static::CHARSET_ASCII;
        }
        if (static::ENCODING_BASE64 !== $this->Encoding && static::hasLineLongerThanMax($this->Body)) {
            $bodyEncoding = static::ENCODING_QUOTED_PRINTABLE;
        }
        $altBodyEncoding = $this->Encoding;
        $altBodyCharSet = $this->CharSet;
        if (static::ENCODING_8BIT === $altBodyEncoding && !$this->has8bitChars($this->AltBody)) {
            $altBodyEncoding = static::ENCODING_7BIT;
            $altBodyCharSet = static::CHARSET_ASCII;
        }
        if (static::ENCODING_BASE64 !== $altBodyEncoding && static::hasLineLongerThanMax($this->AltBody)) {
            $altBodyEncoding = static::ENCODING_QUOTED_PRINTABLE;
        }
        $mimePre = 'This is a multi-part message in MIME format.' . static::$LE . static::$LE;
        switch ($this->message_type) {
            case 'inline':
                $body .= $mimePre;
                $body .= $this->getBoundary($this->boundary[1], $bodyCharSet, '', $bodyEncoding);
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= $this->attachAll('inline', $this->boundary[1]);
                break;
            case 'attach':
                $body .= $mimePre;
                $body .= $this->getBoundary($this->boundary[1], $bodyCharSet, '', $bodyEncoding);
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= $this->attachAll('attachment', $this->boundary[1]);
                break;
            case 'inline_attach':
                $body .= $mimePre;
                $body .= $this->textLine('--' . $this->boundary[1]);
                $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                $body .= $this->textLine(' boundary="' . $this->boundary[2] . '";');
                $body .= $this->textLine(' type="' . static::CONTENT_TYPE_TEXT_HTML . '"');
                $body .= static::$LE;
                $body .= $this->getBoundary($this->boundary[2], $bodyCharSet, '', $bodyEncoding);
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= $this->attachAll('inline', $this->boundary[2]);
                $body .= static::$LE;
                $body .= $this->attachAll('attachment', $this->boundary[1]);
                break;
            case 'alt':
                $body .= $mimePre;
                $body .= $this->getBoundary(
                    $this->boundary[1],
                    $altBodyCharSet,
                    static::CONTENT_TYPE_PLAINTEXT,
                    $altBodyEncoding
                );
                $body .= $this->encodeString($this->AltBody, $altBodyEncoding);
                $body .= static::$LE;
                $body .= $this->getBoundary(
                    $this->boundary[1],
                    $bodyCharSet,
                    static::CONTENT_TYPE_TEXT_HTML,
                    $bodyEncoding
                );
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                if (!empty($this->Ical)) {
                    $method = static::ICAL_METHOD_REQUEST;
                    foreach (static::$IcalMethods as $iMethod) {
                        if (stripos($this->Ical, 'METHOD:' . $iMethod) !== false) {
                            $method = $iMethod;
                            break;
                        }
                    }
                    $body .= $this->getBoundary(
                        $this->boundary[1],
                        '',
                        static::CONTENT_TYPE_TEXT_CALENDAR . '; method=' . $method,
                        ''
                    );
                    $body .= $this->encodeString($this->Ical, $this->Encoding);
                    $body .= static::$LE;
                }
                $body .= $this->endBoundary($this->boundary[1]);
                break;
            case 'alt_inline':
                $body .= $mimePre;
                $body .= $this->getBoundary(
                    $this->boundary[1],
                    $altBodyCharSet,
                    static::CONTENT_TYPE_PLAINTEXT,
                    $altBodyEncoding
                );
                $body .= $this->encodeString($this->AltBody, $altBodyEncoding);
                $body .= static::$LE;
                $body .= $this->textLine('--' . $this->boundary[1]);
                $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                $body .= $this->textLine(' boundary="' . $this->boundary[2] . '";');
                $body .= $this->textLine(' type="' . static::CONTENT_TYPE_TEXT_HTML . '"');
                $body .= static::$LE;
                $body .= $this->getBoundary(
                    $this->boundary[2],
                    $bodyCharSet,
                    static::CONTENT_TYPE_TEXT_HTML,
                    $bodyEncoding
                );
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= $this->attachAll('inline', $this->boundary[2]);
                $body .= static::$LE;
                $body .= $this->endBoundary($this->boundary[1]);
                break;
            case 'alt_attach':
                $body .= $mimePre;
                $body .= $this->textLine('--' . $this->boundary[1]);
                $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';');
                $body .= $this->textLine(' boundary="' . $this->boundary[2] . '"');
                $body .= static::$LE;
                $body .= $this->getBoundary(
                    $this->boundary[2],
                    $altBodyCharSet,
                    static::CONTENT_TYPE_PLAINTEXT,
                    $altBodyEncoding
                );
                $body .= $this->encodeString($this->AltBody, $altBodyEncoding);
                $body .= static::$LE;
                $body .= $this->getBoundary(
                    $this->boundary[2],
                    $bodyCharSet,
                    static::CONTENT_TYPE_TEXT_HTML,
                    $bodyEncoding
                );
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                if (!empty($this->Ical)) {
                    $method = static::ICAL_METHOD_REQUEST;
                    foreach (static::$IcalMethods as $iMethod) {
                        if (stripos($this->Ical, 'METHOD:' . $iMethod) !== false) {
                            $method = $iMethod;
                            break;
                        }
                    }
                    $body .= $this->getBoundary(
                        $this->boundary[2],
                        '',
                        static::CONTENT_TYPE_TEXT_CALENDAR . '; method=' . $method,
                        ''
                    );
                    $body .= $this->encodeString($this->Ical, $this->Encoding);
                }
                $body .= $this->endBoundary($this->boundary[2]);
                $body .= static::$LE;
                $body .= $this->attachAll('attachment', $this->boundary[1]);
                break;
            case 'alt_inline_attach':
                $body .= $mimePre;
                $body .= $this->textLine('--' . $this->boundary[1]);
                $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';');
                $body .= $this->textLine(' boundary="' . $this->boundary[2] . '"');
                $body .= static::$LE;
                $body .= $this->getBoundary(
                    $this->boundary[2],
                    $altBodyCharSet,
                    static::CONTENT_TYPE_PLAINTEXT,
                    $altBodyEncoding
                );
                $body .= $this->encodeString($this->AltBody, $altBodyEncoding);
                $body .= static::$LE;
                $body .= $this->textLine('--' . $this->boundary[2]);
                $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                $body .= $this->textLine(' boundary="' . $this->boundary[3] . '";');
                $body .= $this->textLine(' type="' . static::CONTENT_TYPE_TEXT_HTML . '"');
                $body .= static::$LE;
                $body .= $this->getBoundary(
                    $this->boundary[3],
                    $bodyCharSet,
                    static::CONTENT_TYPE_TEXT_HTML,
                    $bodyEncoding
                );
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= $this->attachAll('inline', $this->boundary[3]);
                $body .= static::$LE;
                $body .= $this->endBoundary($this->boundary[2]);
                $body .= static::$LE;
                $body .= $this->attachAll('attachment', $this->boundary[1]);
                break;
            default:
                $this->Encoding = $bodyEncoding;
                $body .= $this->encodeString($this->Body, $this->Encoding);
                break;
        }
        if ($this->isError()) {
            $body = '';
            if ($this->exceptions) {
                throw new Exception($this->lang('empty_message'), self::STOP_CRITICAL);
            }
        } elseif ($this->sign_key_file) {
            try {
                if (!defined('PKCS7_TEXT')) {
                    throw new Exception($this->lang('extension_missing') . 'openssl');
                }
                $file = tempnam(sys_get_temp_dir(), 'srcsign');
                $signed = tempnam(sys_get_temp_dir(), 'mailsign');
                file_put_contents($file, $body);
                if (empty($this->sign_extra_certs_file)) {
                    $sign = @openssl_pkcs7_sign($file, $signed, 'file://' . realpath($this->sign_cert_file), [
                        'file://' . realpath($this->sign_key_file),
                        $this->sign_key_pass
                    ], []);
                } else {
                    $sign = @openssl_pkcs7_sign($file, $signed, 'file://' . realpath($this->sign_cert_file), [
                        'file://' . realpath($this->sign_key_file),
                        $this->sign_key_pass
                    ], [], PKCS7_DETACHED, $this->sign_extra_certs_file);
                }
                @unlink($file);
                if ($sign) {
                    $body = file_get_contents($signed);
                    @unlink($signed);
                    $parts = explode("\n\n", $body, 2);
                    $this->MIMEHeader .= $parts[0] . static::$LE . static::$LE;
                    $body = $parts[1];
                } else {
                    @unlink($signed);
                    throw new Exception($this->lang('signing') . openssl_error_string());
                }
            } catch (Exception $exc) {
                $body = '';
                if ($this->exceptions) {
                    throw $exc;
                }
            }
        }
        return $body;
    }

    private function getBoundary($boundary, $charSet, $contentType, $encoding)
    {
        $result = '';
        if ('' === $charSet) {
            $charSet = $this->CharSet;
        }
        if ('' === $contentType) {
            $contentType = $this->ContentType;
        }
        if ('' === $encoding) {
            $encoding = $this->Encoding;
        }
        $result .= $this->textLine('--' . $boundary) . sprintf('Content-Type: %s; charset=%s', $contentType, $charSet) .
            static::$LE;
        if (static::ENCODING_7BIT !== $encoding) {
            $result .= $this->headerLine('Content-Transfer-Encoding', $encoding);
        }
        return $result . self::$LE;
    }

    private function endBoundary($boundary)
    {
        return static::$LE . '--' . $boundary . '--' . static::$LE;
    }

    private function setMessageType()
    {
        $type = [];
        if ($this->alternativeExists()) {
            $type[] = 'alt';
        }
        if ($this->inlineImageExists()) {
            $type[] = 'inline';
        }
        if ($this->attachmentExists()) {
            $type[] = 'attach';
        }
        $this->message_type = implode('_', $type);
        if ('' === $this->message_type) {
            $this->message_type = 'plain';
        }
    }

    private function headerLine($name, $value)
    {
        return $name . ': ' . $value . static::$LE;
    }

    private function textLine($value)
    {
        return $value . static::$LE;
    }

    private function attachAll($disposition_type, $boundary)
    {
        $mime = $cidUniq = $incl = [];
        foreach ($this->attachment as $attachment) {
            if ($attachment[6] === $disposition_type) {
                $string = $path = '';
                $bString = $attachment[5];
                if ($bString) {
                    $string = $attachment[0];
                } else {
                    $path = $attachment[0];
                }
                $inclHash = hash('sha256', serialize($attachment));
                if (in_array($inclHash, $incl, true)) {
                    continue;
                }
                $incl[] = $inclHash;
                $name = $attachment[2];
                $encoding = $attachment[3];
                $type = $attachment[4];
                $disposition = $attachment[6];
                $cid = $attachment[7];
                if ('inline' === $disposition && array_key_exists($cid, $cidUniq)) {
                    continue;
                }
                $cidUniq[$cid] = true;
                $mime[] = sprintf('--%s%s', $boundary, static::$LE);
                if (!empty($name)) {
                    $mime[] = sprintf(
                        'Content-Type: %s; name=%s%s',
                        $type,
                        static::quotedString($this->encodeHeader($this->secureHeader($name))),
                        static::$LE
                    );
                } else {
                    $mime[] = sprintf('Content-Type: %s%s', $type, static::$LE);
                }
                if (static::ENCODING_7BIT !== $encoding) {
                    $mime[] = sprintf('Content-Transfer-Encoding: %s%s', $encoding, static::$LE);
                }
                if ((string)$cid !== '' && $disposition === 'inline') {
                    $mime[] = 'Content-ID: <' . $this->encodeHeader($this->secureHeader($cid)) . '>' . static::$LE;
                }
                if (!empty($disposition)) {
                    $encoded_name = $this->encodeHeader($this->secureHeader($name));
                    if (!empty($encoded_name)) {
                        $mime[] = sprintf(
                            'Content-Disposition: %s; filename=%s%s',
                            $disposition,
                            static::quotedString($encoded_name),
                            static::$LE . static::$LE
                        );
                    } else {
                        $mime[] = sprintf('Content-Disposition: %s%s', $disposition, static::$LE . static::$LE);
                    }
                } else {
                    $mime[] = static::$LE;
                }
                try {
                    if ($bString) {
                        $mime[] = $this->encodeString($string, $encoding);
                    } else {
                        $mime[] = $this->encodeFile($path, $encoding);
                    }
                } catch (Exception $e) {
                }
                if ($this->isError()) {
                    return '';
                }
                $mime[] = static::$LE;
            }
        }
        $mime[] = sprintf('--%s--%s', $boundary, static::$LE);
        return implode('', $mime);
    }

    /**
     * @return array|mixed|string|string[]
     * @throws Exception
     */
    private function encodeFile($path, $encoding = self::ENCODING_BASE64)
    {
        try {
            if (!static::fileIsAccessible($path)) {
                throw new Exception($this->lang('file_open') . $path, self::STOP_CONTINUE);
            }
            $fileBuffer = file_get_contents($path);
            if (false === $fileBuffer) {
                throw new Exception($this->lang('file_open') . $path, self::STOP_CONTINUE);
            }
            return $this->encodeString($fileBuffer, $encoding);
        } catch (Exception $exc) {
            $this->setError($exc->getMessage());
            $this->edebug($exc->getMessage());
            if ($this->exceptions) {
                throw $exc;
            }
            return '';
        }
    }

    /**
     * @return array|mixed|string|string[]
     * @throws Exception
     */
    private function encodeString($str, $encoding = self::ENCODING_BASE64)
    {
        $encoded = '';
        switch (strtolower($encoding)) {
            case static::ENCODING_BASE64:
                $encoded = chunk_split(base64_encode($str), static::STD_LINE_LENGTH, static::$LE);
                break;
            case static::ENCODING_7BIT:
            case static::ENCODING_8BIT:
                $encoded = static::normalizeBreaks($str);
                if (substr($encoded, -(strlen(static::$LE))) !== static::$LE) {
                    $encoded .= static::$LE;
                }
                break;
            case static::ENCODING_BINARY:
                $encoded = $str;
                break;
            case static::ENCODING_QUOTED_PRINTABLE:
                $encoded = $this->encodeQP($str);
                break;
            default:
                $this->setError($this->lang('encoding') . $encoding);
                if ($this->exceptions) {
                    throw new Exception($this->lang('encoding') . $encoding);
                }
                break;
        }
        return $encoded;
    }

    private function encodeHeader($str, $position = 'text')
    {
        $matchCount = 0;
        switch (strtolower($position)) {
            case 'phrase':
                if (!preg_match('/[\200-\377]/', $str)) {
                    $encoded = addcslashes($str, "\0..\37\177\\\"");
                    if (($str === $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)) {
                        return $encoded;
                    }
                    return '"' . $encoded . '"';
                }
                $matchCount = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str);
                break;
            case 'comment':
                $matchCount = preg_match_all('/[()"]/', $str, $matches);
                //fallthrough
            default:
                $matchCount += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
        }
        $charset = $this->has8bitChars($str) ? $this->CharSet : static::CHARSET_ASCII;
        $maxLen = ('mail' === $this->Mailer ? static::MAIL_MAX_LINE_LENGTH : self::MAX_LINE_LENGTH) - 8 -
            strlen($charset);
        if ($matchCount > strlen($str) / 3) {
            $encoding = 'B';
        } elseif ($matchCount > 0) {
            $encoding = 'Q';
        } elseif (strlen($str) > $maxLen) {
            $encoding = 'Q';
        } else {
            $encoding = false;
        }
        switch ($encoding) {
            case 'B':
                if ($this->hasMultiBytes($str)) {
                    $encoded = $this->base64EncodeWrapMB($str, "\n");
                } else {
                    $maxLen -= $maxLen % 4;
                    $encoded = trim(chunk_split(base64_encode($str), $maxLen));
                }
                $encoded = preg_replace('/^(.*)$/m', ' =?' . $charset . '?' . $encoding . '?\\1?=', $encoded);
                break;
            case 'Q':
                $encoded = preg_replace('/^(.*)$/m', ' =?' . $charset . '?' . $encoding . '?\\1?=', str_replace('=' .
                    static::$LE, "\n", trim($this->wrapText($this->encodeQ($str, $position), $maxLen, true))));
                break;
            default:
                return $str;
        }
        return trim(static::normalizeBreaks($encoded));
    }

    private function hasMultiBytes($str)
    {
        if (function_exists('mb_strlen')) {
            return strlen($str) > mb_strlen($str, $this->CharSet);
        }
        return false;
    }

    private function base64EncodeWrapMB($str, $linebreak = null)
    {
        $start = '=?' . $this->CharSet . '?B?';
        $end = '?=';
        $encoded = '';
        if (null === $linebreak) {
            $linebreak = static::$LE;
        }
        $mbLength = mb_strlen($str, $this->CharSet);
        $length = 75 - strlen($start) - strlen($end);
        $ratio = $mbLength / strlen($str);
        $avgLength = floor($length * $ratio * .75);
        for ($i = 0; $i < $mbLength; $i += $offset) {
            $lookBack = 0;
            do {
                $offset = $avgLength - $lookBack;
                $chunk = mb_substr($str, $i, $offset, $this->CharSet);
                $chunk = base64_encode($chunk);
                ++$lookBack;
            } while (strlen($chunk) > $length);
            $encoded .= $chunk . $linebreak;
        }
        return substr($encoded, 0, -strlen($linebreak));
    }

    private function has8bitChars($text)
    {
        return (bool)preg_match('/[\x80-\xFF]/', $text);
    }

    private function encodeQP($string)
    {
        return static::normalizeBreaks(quoted_printable_encode($string));
    }

    private function encodeQ($str, $position = 'text')
    {
        $pattern = '';
        $encoded = str_replace(["\r", "\n"], '', $str);
        switch (strtolower($position)) {
            case 'phrase':
                $pattern = '^A-Za-z0-9!*+\/ -';
                break;
            case 'comment':
                $pattern = '\(\)"';
                //fallthrough
            default:
                $pattern = '\000-\011\013\014\016-\037\075\077\137\177-\377' . $pattern;
        }
        $matches = [];
        if (preg_match_all('/[' . $pattern . ']/', $encoded, $matches)) {
            $eqKey = array_search('=', $matches[0], true);
            if (false !== $eqKey) {
                unset($matches[0][$eqKey]);
                array_unshift($matches[0], '=');
            }
            foreach (array_unique($matches[0]) as $char) {
                $encoded = str_replace($char, '=' . sprintf('%02X', ord($char)), $encoded);
            }
        }
        return str_replace(' ', '_', $encoded);
    }

    private function inlineImageExists()
    {
        foreach ($this->attachment as $attachment) {
            if ('inline' === $attachment[6]) {
                return true;
            }
        }
        return false;
    }

    private function attachmentExists()
    {
        foreach ($this->attachment as $attachment) {
            if ('attachment' === $attachment[6]) {
                return true;
            }
        }
        return false;
    }

    private function alternativeExists()
    {
        return !empty($this->AltBody);
    }

    private function setError($msg)
    {
        ++$this->error_count;
        if ('smtp' === $this->Mailer && null !== $this->smtp) {
            $lastError = $this->smtp->getError();
            if (!empty($lastError['error'])) {
                $msg .= $this->lang('smtp_error') . $lastError['error'] . ' ';
                if (!empty($lastError['detail'])) {
                    $msg .= $this->lang('smtp_detail') . $lastError['detail'];
                }
                if (!empty($lastError['smtp_code'])) {
                    $msg .= $this->lang('smtp_code') . $lastError['smtp_code'];
                }
                if (!empty($lastError['smtp_code_ex'])) {
                    $msg .= $this->lang('smtp_code_ex') . $lastError['smtp_code_ex'];
                }
            }
        }
        $this->ErrorInfo = $msg;
    }

    private static function rfcDate()
    {
        date_default_timezone_set(@date_default_timezone_get());
        return date('D, j M Y H:i:s O');
    }

    private function serverHostname()
    {
        if (!empty($this->Hostname)) {
            $result = $this->Hostname;
        } elseif (isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER)) {
            $result = $_SERVER['SERVER_NAME'];
        } elseif (function_exists('gethostname') && gethostname() !== false) {
            $result = gethostname();
        } else {
            $result = php_uname('n');
        }
        return self::isValidHost($result) ? $result : 'localhost.localdomain';
    }

    private static function isValidHost($host)
    {
        if (
            empty($host) || !is_string($host) || strlen($host) > 256 ||
            !preg_match('/^([a-zA-Z\d.-]*|\[[a-fA-F\d:]+])$/', $host)
        ) {
            return false;
        }
        if (strlen($host) > 2 && substr($host, 0, 1) === '[' && substr($host, -1, 1) === ']') {
            return filter_var(substr($host, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
        }
        if (is_numeric(str_replace('.', '', $host))) {
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
        }
        return filter_var('http://' . $host, FILTER_VALIDATE_URL) !== false;
    }

    private function lang($key)
    {
        if (array_key_exists($key, $this->language)) {
            if ('smtp_connect_failed' === $key) {
                return $this->language[$key] . ' https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting';
            }
            return $this->language[$key];
        }
        return $key;
    }

    private function getSmtpErrorMessage()
    {
        $message = $this->lang('connect_host');
        $error = $this->smtp->getError();
        if (!empty($error['error'])) {
            $message .= ' ' . $error['error'];
            if (!empty($error['detail'])) {
                $message .= ' ' . $error['detail'];
            }
        }
        return $message;
    }

    private function isError()
    {
        return $this->error_count > 0;
    }

    private function secureHeader($str)
    {
        return trim(str_replace(["\r", "\n"], '', $str));
    }

    private static function normalizeBreaks($text, $breakType = null)
    {
        if (null === $breakType) {
            $breakType = static::$LE;
        }
        $text = str_replace([self::CRLF, "\r"], "\n", $text);
        if ("\n" !== $breakType) {
            $text = str_replace("\n", $breakType, $text);
        }
        return $text;
    }

    private static function stripTrailingWSP($text)
    {
        return rtrim($text, " \r\n\t");
    }

    private static function setLE($le)
    {
        static::$LE = $le;
    }

    private function dkimQp($txt)
    {
        $line = '';
        for ($i = 0; $i < strlen($txt); ++$i) {
            $ord = ord($txt[$i]);
            $line .= ((0x21 <= $ord) && ($ord <= 0x3A)) || $ord === 0x3C || ((0x3E <= $ord) && ($ord <= 0x7E)) ?
                $txt[$i] : '=' . sprintf('%02X', $ord);
        }
        return $line;
    }

    /**
     * @param $signHeader
     * @return string
     * @throws Exception
     */
    private function dkimSign($signHeader)
    {
        if (!defined('PKCS7_TEXT')) {
            if ($this->exceptions) {
                throw new Exception($this->lang('extension_missing') . 'openssl');
            }
            return '';
        }
        $privateKeyStr = !empty($this->DKIM_private_string) ?
            $this->DKIM_private_string : file_get_contents($this->DKIM_private);
        $privateKey = $this->DKIM_passphrase !== '' ?
            openssl_pkey_get_private($privateKeyStr, $this->DKIM_passphrase) : openssl_pkey_get_private($privateKeyStr);
        if (openssl_sign($signHeader, $signature, $privateKey, 'sha256WithRSAEncryption')) {
            if (PHP_MAJOR_VERSION < 8) {
                openssl_pkey_free($privateKey);
            }
            return base64_encode($signature);
        }
        if (PHP_MAJOR_VERSION < 8) {
            openssl_pkey_free($privateKey);
        }
        return '';
    }

    private function dkimHeaderC($signHeader)
    {
        $lines =
            explode(self::CRLF, preg_replace('/\r\n[ \t]+/', ' ', static::normalizeBreaks($signHeader, self::CRLF)));
        foreach ($lines as $key => $line) {
            if (strpos($line, ':') === false) {
                continue;
            }
            [$heading, $value] = explode(':', $line, 2);
            $heading = strtolower($heading);
            $value = preg_replace('/[ \t]+/', ' ', $value);
            $lines[$key] = trim($heading, " \t") . ':' . trim($value, " \t");
        }
        return implode(self::CRLF, $lines);
    }

    private function dkimBodyC($body)
    {
        return empty($body) ?
            self::CRLF : static::stripTrailingWSP(static::normalizeBreaks($body, self::CRLF)) . self::CRLF;
    }

    private function dkimAdd($headersLine, $subject, $body)
    {
        $dkimSignatureType = 'rsa-sha256';
        $dkimCanonicalization = 'relaxed/simple';
        $dkimQuery = 'dns/txt';
        $DKIMtime = time();
        $autoSignHeaders = ['from', 'to', 'cc', 'date', 'subject', 'reply-to', 'message-id', 'content-type',
            'mime-version', 'x-mailer'];
        if (stripos($headersLine, 'Subject') === false) {
            $headersLine .= 'Subject: ' . $subject . static::$LE;
        }
        $headerLines = explode(static::$LE, $headersLine);
        $currentHeaderLabel = '';
        $currentHeaderValue = '';
        $parsedHeaders = [];
        $headerLineIndex = 0;
        $headerLineCount = count($headerLines);
        foreach ($headerLines as $headerLine) {
            $matches = [];
            if (preg_match('/^([^ \t]*?)(?::[ \t]*)(.*)$/', $headerLine, $matches)) {
                if ($currentHeaderLabel !== '') {
                    $parsedHeaders[] = ['label' => $currentHeaderLabel, 'value' => $currentHeaderValue];
                }
                $currentHeaderLabel = $matches[1];
                $currentHeaderValue = $matches[2];
            } elseif (preg_match('/^[ \t]+(.*)$/', $headerLine, $matches)) {
                $currentHeaderValue .= ' ' . $matches[1];
            }
            ++$headerLineIndex;
            if ($headerLineIndex >= $headerLineCount) {
                $parsedHeaders[] = ['label' => $currentHeaderLabel, 'value' => $currentHeaderValue];
            }
        }
        $copiedHeaders = [];
        $headersToSignKeys = [];
        $headersToSign = [];
        foreach ($parsedHeaders as $header) {
            if (in_array(strtolower($header['label']), $autoSignHeaders, true)) {
                $headersToSignKeys[] = $header['label'];
                $headersToSign[] = $header['label'] . ': ' . $header['value'];
                if ($this->DKIM_copyHeaderFields) {
                    $copiedHeaders[] = $header['label'] . ':' .
                        str_replace('|', '=7C', $this->dkimQp($header['value']));
                }
                continue;
            }
            if (in_array($header['label'], $this->DKIM_extraHeaders, true)) {
                foreach ($this->CustomHeader as $customHeader) {
                    if ($customHeader[0] === $header['label']) {
                        $headersToSignKeys[] = $header['label'];
                        $headersToSign[] = $header['label'] . ': ' . $header['value'];
                        if ($this->DKIM_copyHeaderFields) {
                            $copiedHeaders[] = $header['label'] . ':' .
                                str_replace('|', '=7C', $this->dkimQp($header['value']));
                        }
                        continue 2;
                    }
                }
            }
        }
        $copiedHeaderFields = '';
        if ($this->DKIM_copyHeaderFields && count($copiedHeaders) > 0) {
            $copiedHeaderFields = ' z=';
            $first = true;
            foreach ($copiedHeaders as $copiedHeader) {
                if (!$first) {
                    $copiedHeaderFields .= static::$LE . ' |';
                }
                if (strlen($copiedHeader) > self::STD_LINE_LENGTH - 3) {
                    $copiedHeaderFields .= substr(
                        chunk_split($copiedHeader, self::STD_LINE_LENGTH - 3, static::$LE . self::FWS),
                        0,
                        -strlen(static::$LE . self::FWS)
                    );
                } else {
                    $copiedHeaderFields .= $copiedHeader;
                }
                $first = false;
            }
            $copiedHeaderFields .= ';' . static::$LE;
        }
        $headerKeys = ' h=' . implode(':', $headersToSignKeys) . ';' . static::$LE;
        $headerValues = implode(static::$LE, $headersToSign);
        $body = $this->dkimBodyC($body);
        $DKIMb64 = base64_encode(pack('H*', hash('sha256', $body)));
        $ident = '';
        if ('' !== $this->DKIM_identity) {
            $ident = ' i=' . $this->DKIM_identity . ';' . static::$LE;
        }
        $dkimSignatureHeader = 'DKIM-Signature: v=1; d=' . $this->DKIM_domain . '; s=' . $this->DKIM_selector . ';' .
            static::$LE . ' a=' . $dkimSignatureType . '; q=' . $dkimQuery . '; t=' . $DKIMtime . '; c=' .
            $dkimCanonicalization . ';' . static::$LE . $headerKeys . $ident . $copiedHeaderFields . ' bh=' .
            $DKIMb64 . ';' . static::$LE . ' b=';
        $signature = '';
        try {
            $signature = $this->dkimSign($this->dkimHeaderC($headerValues . static::$LE . $dkimSignatureHeader));
        } catch (Exception $e) {
        }
        $signature = trim(chunk_split($signature, self::STD_LINE_LENGTH - 3, static::$LE . self::FWS));
        return static::normalizeBreaks($dkimSignatureHeader . $signature);
    }

    private static function hasLineLongerThanMax($str)
    {
        return (bool)preg_match('/^(.{' . (self::MAX_LINE_LENGTH + strlen(static::$LE)) . ',})/m', $str);
    }

    private static function quotedString($str)
    {
        return preg_match('/[ ()<>@,;:"\/\[\]?=]/', $str) ? '"' . str_replace('"', '\\"', $str) . '"' : $str;
    }

    private function doCallback($isSent, $to, $cc, $bcc, $subject, $body, $from, $extra)
    {
        if (!empty($this->action_function) && is_callable($this->action_function)) {
            call_user_func($this->action_function, $isSent, $to, $cc, $bcc, $subject, $body, $from, $extra);
        }
    }
}
