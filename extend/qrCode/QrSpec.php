<?php

namespace qrCode;

class QrSpec
{
    private static array $capacity = [[0,0,0,[0,0,0,0]],[21,26,0,[7,10,13,17]],[25,44,7,[10,16,22,28]],
        [29,70,7,[15,26,36,44]],[33,100,7,[20,36,52,64]],[37,134,7,[26,48,72,88]],[41,172,7,[36,64,96,112]],
        [45,196,0,[40,72,108,130]],[49,242,0,[48,88,132,156]],[53,292,0,[60,110,160,192]],[57,346,0,[72,130,192,224]],
        [61,404,0,[80,150,224,264]],[65,466,0,[96,176,260,308]],[69,532,0,[104,198,288,352]],
        [73,581,3,[120,216,320,384]],[77,655,3,[132,240,360,432]],[81,733,3,[144,280,408,480]],
        [85,815,3,[168,308,448,532]],[89,901,3,[180,338,504,588]],[93,991,3,[196,364,546,650]],
        [97,1085,3,[224,416,600,700]],[101,1156,4,[224,442,644,750]],[105,1258,4,[252,476,690,816]],
        [109,1364,4,[270,504,750,900]],[113,1474,4,[300,560,810,960]],[117,1588,4,[312,588,870,1050]],
        [121,1706,4,[336,644,952,1110]],[125,1828,4,[360,700,1020,1200]],[129,1921,3,[390,728,1050,1260]],
        [133,2051,3,[420,784,1140,1350]],[137,2185,3,[450,812,1200,1440]],[141,2323,3,[480,868,1290,1530]],
        [145,2465,3,[510,924,1350,1620]],[149,2611,3,[540,980,1440,1710]],[153,2761,3,[570,1036,1530,1800]],
        [157,2876,0,[570,1064,1590,1890]],[161,3034,0,[600,1120,1680,1980]],[165,3196,0,[630,1204,1770,2100]],
        [169,3362,0,[660,1260,1860,2220]],[173,3532,0,[720,1316,1950,2310]],[177,3706,0,[750,1372,2040,2430]]];
    private static array $lengthTableBits = [[10,12,14],[9,11,13],[8,16,16],[8,10,12]];
    private static array $alignmentPattern = [[0,0],[0,0],[18,0],[22,0],[26,0],[30,0],[34,0],[22,38],[24,42],[26,46],
        [28,50],[30,54],[32,58],[34,62],[26,46],[26,48],[26,50],[30,54],[30,56],[30,58],[34,62],[28,50],[26,50],[30,54],
        [28,54],[32,58],[30,58],[34,62],[26,50],[30,54],[26,52],[30,56],[34,60],[30,58],[34,62],[30,54],[24,50],[28,54],
        [32,58],[26,54],[30,58]];

    public static function getDataLength($version, $level)
    {
        return self::$capacity[$version][1] - self::$capacity[$version][3][$level];
    }

    public static function getWidth($version)
    {
        return self::$capacity[$version][0];
    }

    public static function getRemainder($version)
    {
        return self::$capacity[$version][2];
    }

    public static function getMinimumVersion($size, $level)
    {
        for ($i = 1; $i <= 40; $i++) {
            if (self::$capacity[$i][1] - self::$capacity[$i][3][$level] >= $size) {
                return $i;
            }
        }
        return -1;
    }

    public static function lengthIndicator($mode, $version)
    {
        if ($mode == 4) {
            return 0;
        }
        if ($version <= 9) {
            $l = 0;
        } elseif ($version <= 26) {
            $l = 1;
        } else {
            $l = 2;
        }
        return self::$lengthTableBits[$mode][$l];
    }

    public static function getEccSpec($version, $level, array &$spec)
    {
        $eccTable = [[[0,0],[0,0],[0,0],[0,0]],[[1,0],[1,0],[1,0],[1,0]],[[1,0],[1,0],[1,0],[1,0]],
            [[1,0],[1,0],[2,0],[2,0]],[[1,0],[2,0],[2,0],[4,0]],[[1,0],[2,0],[2,2],[2,2]],[[2,0],[4,0],[4,0],[4,0]],
            [[2,0],[4,0],[2,4],[4,1]],[[2,0],[2,2],[4,2],[4,2]],[[2,0],[3,2],[4,4],[4,4]],[[2,2],[4,1],[6,2],[6,2]],
            [[4,0],[1,4],[4,4],[3,8]],[[2,2],[6,2],[4,6],[7,4]],[[4,0],[8,1],[8,4],[12,4]],[[3,1],[4,5],[11,5],[11,5]],
            [[5,1],[5,5],[5,7],[11,7]],[[5,1],[7,3],[15,2],[3,13]],[[1,5],[10,1],[1,15],[2,17]],
            [[5,1],[9,4],[17,1],[2,19]], [[3,4],[3,11],[17,4],[9,16]],[[3,5],[3,13],[15,5],[15,10]],
            [[4,4],[17,0],[17,6],[19,6]], [[2,7],[17,0],[7,16],[34,0]],[[4,5],[4,14],[11,14],[16,14]],
            [[6,4],[6,14],[11,16],[30,2]], [[8,4],[8,13],[7,22],[22,13]],[[10,2],[19,4],[28,6],[33,4]],
            [[8,4],[22,3],[8,26],[12,28]], [[3,10],[3,23],[4,31],[11,31]],[[7,7],[21,7],[1,37],[19,26]],
            [[5,10],[19,10],[15,25],[23,25]],[[13,3],[2,29],[42,1],[23,28]],[[17,0],[10,23],[10,35],[19,35]],
            [[17,1],[14,21],[29,19],[11,46]], [[13,6],[14,23],[44,7],[59,1]],[[12,7],[12,26],[39,14],[22,41]],
            [[6,14],[6,34],[46,10],[2,64]], [[17,4],[29,14],[49,10],[24,46]],[[4,18],[13,32],[48,14],[42,32]],
            [[20,4],[40,7],[43,22],[10,67]],[[19,6],[18,31],[34,34],[20,61]]];
        $b1 = $eccTable[$version][$level][0];
        $b2 = $eccTable[$version][$level][1];
        $data = self::getDataLength($version, $level);
        $ecc = self::$capacity[$version][3][$level];
        $spec[0] = $b1;
        if ($b2 == 0) {
            $spec[1] = (int)($data / $b1);
            $spec[2] = (int)($ecc / $b1);
            $spec[3] = 0;
            $spec[4] = 0;
        } else {
            $spec[1] = (int)($data / ($b1 + $b2));
            $spec[2] = (int)($ecc / ($b1 + $b2));
            $spec[3] = $b2;
            $spec[4] = $spec[1] + 1;
        }
    }

    public static function createFrame($version)
    {
        if ($version < 1 || $version > 40) {
            return null;
        }
        $width = self::$capacity[$version][0];
        $frame = array_fill(0, $width, str_repeat("\0", $width));
        self::putFinderPattern($frame, 0, 0);
        self::putFinderPattern($frame, $width - 7, 0);
        self::putFinderPattern($frame, 0, $width - 7);
        $yOffset = $width - 7;
        for ($y = 0; $y < 7; $y++) {
            $frame[$y][7] = "\xc0";
            $frame[$y][$width - 8] = "\xc0";
            $frame[$yOffset][7] = "\xc0";
            $yOffset++;
        }
        $setPattern = str_repeat("\xc0", 8);
        $frame[7] = substr_replace($frame[7], $setPattern, 0, strlen($setPattern));
        $frame[7] = substr_replace($frame[7], $setPattern, $width - 8, strlen($setPattern));
        $frame[$width - 8] = substr_replace($frame[$width - 8], $setPattern, 0, strlen($setPattern));
        $setPattern = str_repeat("\x84", 9);
        $frame[8] = substr_replace($frame[8], $setPattern, 0, strlen($setPattern));
        $frame[8] = substr_replace($frame[8], $setPattern, $width - 8, strlen($setPattern));
        $yOffset = $width - 8;
        for ($y = 0; $y < 8; $y++, $yOffset++) {
            $frame[$y][8] = "\x84";
            $frame[$yOffset][8] = "\x84";
        }
        for ($i = 1; $i < $width - 15; $i++) {
            $frame[6][7 + $i] = chr(0x90 | ($i & 1));
            $frame[7 + $i][6] = chr(0x90 | ($i & 1));
        }
        self::putAlignmentPattern($version, $frame, $width);
        $frame[$width - 8][8] = "\x81";
        return $frame;
    }

    private static function putAlignmentMarker(array &$frame, $ox, $oy)
    {
        $finder = ["\xa1\xa1\xa1\xa1\xa1", "\xa1\xa0\xa0\xa0\xa1", "\xa1\xa0\xa1\xa0\xa1", "\xa1\xa0\xa0\xa0\xa1",
                   "\xa1\xa1\xa1\xa1\xa1"];
        for ($i = 0; $i < 5; $i++) {
            $frame[$oy - 2 + $i] = substr_replace($frame[$oy - 2 + $i], $finder[$i], $ox - 2, strlen($finder[$i]));
        }
    }

    private static function putFinderPattern(&$frame, $ox, $oy)
    {
        $finder = ["\xc1\xc1\xc1\xc1\xc1\xc1\xc1","\xc1\xc0\xc0\xc0\xc0\xc0\xc1","\xc1\xc0\xc1\xc1\xc1\xc0\xc1",
            "\xc1\xc0\xc1\xc1\xc1\xc0\xc1","\xc1\xc0\xc1\xc1\xc1\xc0\xc1","\xc1\xc0\xc0\xc0\xc0\xc0\xc1",
            "\xc1\xc1\xc1\xc1\xc1\xc1\xc1"];
        for ($i = 0; $i < 7; $i++) {
            $frame[$oy + $i] = substr_replace($frame[$oy + $i], $finder[$i], $ox, strlen($finder[$i]));
        }
    }

    private static function putAlignmentPattern($version, &$frame, $width)
    {
        if ($version < 2) {
            return;
        }
        $d = self::$alignmentPattern[$version][1] - self::$alignmentPattern[$version][0];
        $w = $d < 0 ? 2 : (int)(($width - self::$alignmentPattern[$version][0]) / $d + 2);
        if ($w * $w - 3 == 1) {
            $x = self::$alignmentPattern[$version][0];
            $y = self::$alignmentPattern[$version][0];
            self::putAlignmentMarker($frame, $x, $y);
            return;
        }
        $cx = self::$alignmentPattern[$version][0];
        for ($x = 1; $x < $w - 1; $x++) {
            self::putAlignmentMarker($frame, 6, $cx);
            self::putAlignmentMarker($frame, $cx, 6);
            $cx += $d;
        }
        $cy = self::$alignmentPattern[$version][0];
        for ($y = 0; $y < $w - 1; $y++) {
            $cx = self::$alignmentPattern[$version][0];
            for ($x = 0; $x < $w - 1; $x++) {
                self::putAlignmentMarker($frame, $cx, $cy);
                $cx += $d;
            }
            $cy += $d;
        }
    }
}
