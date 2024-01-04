<?php

namespace yjrj;

use think\facade\Config;

class QQWry
{
    private static $instance;
    private int $countryFlag = 0;
    private int $endIpOff = 0;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getAddress($ip = '')
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance->ipToAddress($ip);
    }

    public static function getVersion()
    {
        preg_match_all(
            '/\d{4}年\d{1,2}月\d{1,2}日/',
            mb_convert_encoding(file_get_contents(Config::get('file.qqwry')), 'UTF-8', 'GBK'),
            $version
        );
        return $version[0][0];
    }

    private function getStartEndIp($handle, $recNo, $firstStartIp)
    {
        fseek($handle, $firstStartIp + $recNo * 7);
        $fread = fread($handle, 7);
        return [
            ord($fread[0]) + ord($fread[1]) * 256 + ord($fread[2]) * 256 * 256 + ord($fread[3]) * 256 * 256 * 256,
            ord($fread[4]) + ord($fread[5]) * 256 + ord($fread[6]) * 256 * 256
        ];
    }

    private function getFlagStr($handle, $offset)
    {
        while (1) {
            fseek($handle, $offset);
            $flag = ord(fgetc($handle));
            if ($flag == 1 || $flag == 2) {
                $fread = fread($handle, 3);
                if ($flag == 2) {
                    $this->countryFlag = 2;
                    $this->endIpOff = $offset - 4;
                }
                $offset = ord($fread[0]) + ord($fread[1]) * 256 + ord($fread[2]) * 256 * 256;
            } else {
                break;
            }
        }
        if ($offset < 12) {
            return '';
        }

        fseek($handle, $offset);
        $str = '';
        while (1) {
            $c = fgetc($handle);
            if (ord($c[0]) == 0) {
                break;
            }
            $str .= $c;
        }
        return $str;
    }

    private function ipToAddress($ip)
    {
        if (!$ip) {
            return '';
        } elseif (preg_match('/(^127)/', $ip)) {
            return '本地网络';
        } elseif (preg_match('/(^192)/', $ip)) {
            return '局域网';
        } else {
            $handle = fopen(Config::get('file.qqwry'), 'rb');
            if ($handle == null) {
                return 'FileDataError';
            }

            fseek($handle, 0);
            $fread = fread($handle, 8);
            $firstStartIp = ord($fread[0]) + ord($fread[1]) * 256 + ord($fread[2]) * 256 * 256 +
                ord($fread[3]) * 256 * 256 * 256;
            $recordCount = floor((ord($fread[4]) + ord($fread[5]) * 256 + ord($fread[6]) * 256 * 256 +
                    ord($fread[7]) * 256 * 256 * 256 - $firstStartIp) / 7);
            if ($recordCount <= 1) {
                fclose($handle);
                return 'FileDataError';
            }

            $rangB = 0;
            $rangE = $recordCount;
            $temp = explode('.', $ip);
            $ip = ($temp[0] ?? 0) * 256 * 256 * 256 + ($temp[1] ?? 0) * 256 * 256 + ($temp[2] ?? 0) * 256 +
                ($temp[3] ?? 0);
            while ($rangB < $rangE - 1) {
                $recNo = floor(($rangB + $rangE) / 2);
                $startEndIp = $this->getStartEndIp($handle, $recNo, $firstStartIp);
                if ($ip == $startEndIp[0]) {
                    $rangB = $recNo;
                    break;
                } elseif ($ip > $startEndIp[0]) {
                    $rangB = $recNo;
                } else {
                    $rangE = $recNo;
                }
            }
            $startEndIp = $this->getStartEndIp($handle, $rangB, $firstStartIp);

            fseek($handle, $startEndIp[1]);
            $fread2 = fread($handle, 5);
            if (
                $startEndIp[0] <= $ip && ord($fread2[0]) + ord($fread2[1]) * 256 + ord($fread2[2]) * 256 * 256 +
                ord($fread2[3]) * 256 * 256 * 256 >= $ip
            ) {
                $this->countryFlag = ord($fread2[4]);
                switch ($this->countryFlag) {
                    case 1:
                    case 2:
                        $ipAddress = mb_convert_encoding(
                            $this->getFlagStr($handle, $startEndIp[1] + 4),
                            'UTF-8',
                            'GBK'
                        ) . ' ' . mb_convert_encoding(
                            $this->countryFlag == 1 ? '' : $this->getFlagStr($handle, $this->endIpOff + 8),
                            'UTF-8',
                            'GBK'
                        );
                        break;
                    default:
                        $ipAddress = mb_convert_encoding(
                            $this->getFlagStr($handle, $this->endIpOff + 4),
                            'UTF-8',
                            'GBK'
                        ) . ' ' . mb_convert_encoding($this->getFlagStr($handle, ftell($handle)), 'UTF-8', 'GBK');
                }
            } else {
                $ipAddress = '未知';
            }
            fclose($handle);
            return trim(str_replace(' CZ88.NET', '', $ipAddress));
        }
    }
}
