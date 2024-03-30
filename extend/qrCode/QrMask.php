<?php

namespace qrCode;

class QrMask
{
    private array $runLength = [];

    public function __construct()
    {
        $this->runLength = array_fill(0, 178, 0);
    }

    public function mask($width, $frame, $level)
    {
        $minDemerit = PHP_INT_MAX;
        $checkedMasks = range(0, 7);
        $howManuOut = 8 - (2 % 9);
        for ($i = 0; $i < $howManuOut; $i++) {
            $remPos = rand(0, count($checkedMasks) - 1);
            unset($checkedMasks[$remPos]);
            $checkedMasks = array_values($checkedMasks);
        }
        $bestMask = $frame;
        foreach ($checkedMasks as $i) {
            $mask = array_fill(0, $width, str_repeat("\0", $width));
            $blacks = $this->makeMaskNo($i, $width, $frame, $mask);
            $blacks += $this->writeFormatInformation($width, $mask, $i, $level);
            $blacks = (int)(100 * $blacks / ($width * $width));
            $demerit = (int)((int)(abs($blacks - 50) / 5) * 10);
            $demerit += $this->evaluateSymbol($width, $mask);
            if ($demerit < $minDemerit) {
                $minDemerit = $demerit;
                $bestMask = $mask;
            }
        }
        return $bestMask;
    }

    private function writeFormatInformation($width, &$frame, $mask, $level)
    {
        $blacks = 0;
        $format = $this->getFormatInfo($mask, $level);
        for ($i = 0; $i < 8; $i++) {
            if ($format & 1) {
                $blacks += 2;
                $v = 0x85;
            } else {
                $v = 0x84;
            }
            $frame[8][$width - 1 - $i] = chr($v);
            if ($i < 6) {
                $frame[$i][8] = chr($v);
            } else {
                $frame[$i + 1][8] = chr($v);
            }
            $format = $format >> 1;
        }
        for ($i = 0; $i < 7; $i++) {
            if ($format & 1) {
                $blacks += 2;
                $v = 0x85;
            } else {
                $v = 0x84;
            }
            $frame[$width - 7 + $i][8] = chr($v);
            if ($i == 0) {
                $frame[8][7] = chr($v);
            } else {
                $frame[8][6 - $i] = chr($v);
            }
            $format = $format >> 1;
        }

        return $blacks;
    }

    private function mask0($x, $y)
    {
        return ($x + $y) & 1;
    }

    private function mask1($x, $y)
    {
        return $y & 1;
    }

    private function mask2($x, $y)
    {
        return $x % 3;
    }

    private function mask3($x, $y)
    {
        return ($x + $y) % 3;
    }

    private function mask4($x, $y)
    {
        return (((int)($y / 2)) + ((int)($x / 3))) & 1;
    }

    private function mask5($x, $y)
    {
        return (($x * $y) & 1) + ($x * $y) % 3;
    }

    private function mask6($x, $y)
    {
        return ((($x * $y) & 1) + ($x * $y) % 3) & 1;
    }

    private function mask7($x, $y)
    {
        return ((($x * $y) % 3) + (($x + $y) & 1)) & 1;
    }

    private function generateMaskNo($maskNo, $width, $frame)
    {
        $bitMask = array_fill(0, $width, array_fill(0, $width, 0));
        for ($y = 0; $y < $width; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if (ord($frame[$y][$x]) & 0x80) {
                    $bitMask[$y][$x] = 0;
                } else {
                    $bitMask[$y][$x] = call_user_func([$this, 'mask' . $maskNo], $x, $y) == 0;
                }
            }
        }
        return $bitMask;
    }

    private function makeMaskNo($maskNo, $width, $s, &$d)
    {
        $bitMask = $this->generateMaskNo($maskNo, $width, $s);
        $d = $s;
        $b = 0;
        for ($y = 0; $y < $width; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($bitMask[$y][$x] == 1) {
                    $d[$y][$x] = chr(ord($s[$y][$x]) ^ (int)$bitMask[$y][$x]);
                }
                $b += ord($d[$y][$x]) & 1;
            }
        }
        return $b;
    }

    private function evaluateSymbol($width, $frame)
    {
        $demerit = 0;
        for ($y = 0; $y < $width; $y++) {
            $head = 0;
            $this->runLength[0] = 1;
            $frameY = $frame[$y];
            $frameYM = null;
            if ($y > 0) {
                $frameYM = $frame[$y - 1];
            }
            for ($x = 0; $x < $width; $x++) {
                if (($x > 0) && ($y > 0)) {
                    $b22 = ord($frameY[$x]) & ord($frameY[$x - 1]) & ord($frameYM[$x]) & ord($frameYM[$x - 1]);
                    $w22 = ord($frameY[$x]) | ord($frameY[$x - 1]) | ord($frameYM[$x]) | ord($frameYM[$x - 1]);
                    if (($b22 | ($w22 ^ 1)) & 1) {
                        $demerit += 3;
                    }
                }
                if (($x == 0) && (ord($frameY[$x]) & 1)) {
                    $this->runLength[0] = -1;
                    $head = 1;
                    $this->runLength[$head] = 1;
                } elseif ($x > 0) {
                    if ((ord($frameY[$x]) ^ ord($frameY[$x - 1])) & 1) {
                        $head++;
                        $this->runLength[$head] = 1;
                    } else {
                        $this->runLength[$head]++;
                    }
                }
            }
        }
        for ($x = 0; $x < $width; $x++) {
            $head = 0;
            $this->runLength[0] = 1;
            for ($y = 0; $y < $width; $y++) {
                if ($y == 0 && (ord($frame[$y][$x]) & 1)) {
                    $this->runLength[0] = -1;
                    $head = 1;
                    $this->runLength[$head] = 1;
                } elseif ($y > 0) {
                    if ((ord($frame[$y][$x]) ^ ord($frame[$y - 1][$x])) & 1) {
                        $head++;
                        $this->runLength[$head] = 1;
                    } else {
                        $this->runLength[$head]++;
                    }
                }
            }
        }
        return $demerit;
    }

    private function getFormatInfo($mask, $level)
    {
        return $mask < 0 || $mask > 7 || $level < 0 || $level > 3 ? 0 : [
            [0x77c4,0x72f3,0x7daa,0x789d,0x662f,0x6318,0x6c41,0x6976],
            [0x5412,0x5125,0x5e7c,0x5b4b,0x45f9,0x40ce,0x4f97,0x4aa0],
            [0x355f,0x3068,0x3f31,0x3a06,0x24b4,0x2183,0x2eda,0x2bed],
            [0x1689,0x13be,0x1ce7,0x19d0,0x0762,0x0255,0x0d0c,0x083b]][$level][$mask];
    }
}
