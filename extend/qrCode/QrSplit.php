<?php

namespace qrCode;

class QrSplit
{
    public string $dataStr = '';
    public QrInput $input;
    public int $modeHint;

    public function __construct($dataStr, $input, $modeHint)
    {
        $this->dataStr = $dataStr;
        $this->input = $input;
        $this->modeHint = $modeHint;
    }

    public static function isDigital($str, $pos)
    {
        return !($pos >= strlen($str)) && (ord($str[$pos]) >= ord('0')) && (ord($str[$pos]) <= ord('9'));
    }

    public static function isAlNum($str, $pos)
    {
        return !$pos >= strlen($str) && QrInput::lookAnTable(ord($str[$pos])) >= 0;
    }

    public function identifyMode($pos)
    {
        if ($pos >= strlen($this->dataStr)) {
            return -1;
        }
        $c = $this->dataStr[$pos];
        if (self::isDigital($this->dataStr, $pos)) {
            return 0;
        } elseif (self::isAlNum($this->dataStr, $pos)) {
            return 1;
        } elseif ($this->modeHint == 3) {
            if ($pos + 1 < strlen($this->dataStr)) {
                $d = $this->dataStr[$pos + 1];
                $word = (ord($c) << 8) | ord($d);
                if (($word >= 0x8140 && $word <= 0x9ffc) || ($word >= 0xe040 && $word <= 0xebbf)) {
                    return 3;
                }
            }
        }
        return 2;
    }

    public function eat8()
    {
        $la = QrSpec::lengthIndicator(1, $this->input->getVersion());
        $ln = QrSpec::lengthIndicator(0, $this->input->getVersion());
        $p = 1;
        $dataStrLen = strlen($this->dataStr);
        while ($p < $dataStrLen) {
            $mode = $this->identifyMode($p);
            if ($mode == 3) {
                break;
            }
            if ($mode == 0) {
                $q = $p;
                while (self::isDigital($this->dataStr, $q)) {
                    $q++;
                }
                $dif = $p * 8 + QrInput::estimateBitsModeNum($q - $p) + 4 + $ln - $q * 8;
                if ($dif < 0) {
                    break;
                } else {
                    $p = $q;
                }
            } elseif ($mode == 1) {
                $q = $p;
                while (self::isAlNum($this->dataStr, $q)) {
                    $q++;
                }
                $dif = $p * 8 + (int)((($q - $p)) / 2) * 11 + ($q - $p & 1 ? 6 : 0) + 4 + $la - $q * 8;
                if ($dif < 0) {
                    break;
                } else {
                    $p = $q;
                }
            } else {
                $p++;
            }
        }
        return $this->input->append(2, $p, str_split($this->dataStr)) < 0 ? -1 : $p;
    }

    public function splitString()
    {
        while (strlen($this->dataStr) > 0) {
            if ($this->dataStr == '') {
                return 0;
            }
            $length = $this->eat8();
            if ($length == 0) {
                return 0;
            }
            if ($length < 0) {
                return -1;
            }
            $this->dataStr = substr($this->dataStr, $length);
        }
        return 0;
    }

    public function toUpper()
    {
        $stringLen = strlen($this->dataStr);
        $p = 0;
        while ($p < $stringLen) {
            $mode = self::identifyMode(substr($this->dataStr, $p));
            if ($mode == 3) {
                $p += 2;
            } else {
                if (ord($this->dataStr[$p]) >= ord('a') && ord($this->dataStr[$p]) <= ord('z')) {
                    $this->dataStr[$p] = chr(ord($this->dataStr[$p]) - 32);
                }
                $p++;
            }
        }
        return $this->dataStr;
    }

    public static function splitStringToQrInput($string, QrInput $input, $modeHint, $caseSensitive = true)
    {
        $QrSplit = new QrSplit($string, $input, $modeHint);
        if (!$caseSensitive) {
            $QrSplit->toUpper();
        }
        return $QrSplit->splitString();
    }
}
