<?php

namespace qrCode;

class QrCode
{
    public static function png($text, $size = 3, $level = 0, $margin = 4, $outfile = '')
    {
        ob_start();
        switch ($level . '') {
            case 'l':
            case 'L':
                $level = 0;
                break;
            case 'm':
            case 'M':
                $level = 1;
                break;
            case 'q':
            case 'Q':
                $level = 2;
                break;
            case 'h':
            case 'H':
                $level = 3;
                break;
        }
        $QrCode = new QrCode();
        $QrInput = new QrInput(0, $level);
        QrSplit::splitStringToQrInput($text, $QrInput, 2);
        $binary = $QrCode->binary($QrCode->encodeMask($QrInput));
        ob_end_clean();
        $image = self::image($binary, min(max(1, $size), (int)(1024 / (count($binary) + 2 * $margin))), $margin);
        if ($outfile == '') {
            header('Content-type: image/png');
            imagepng($image);
        } else {
            imagepng($image, $outfile);
        }
        imagedestroy($image);
    }

    private static function image($frame, $pixelPerPoint = 4, $outerFrame = 4)
    {
        $h = count($frame);
        $w = strlen($frame[0]);
        $imgW = $w + 2 * $outerFrame;
        $imgH = $h + 2 * $outerFrame;
        $baseImage = imagecreate($imgW, $imgH);
        $col = imagecolorallocate($baseImage, 0, 0, 0);
        imagefill($baseImage, 0, 0, imagecolorallocate($baseImage, 255, 255, 255));
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if ($frame[$y][$x] == '1') {
                    imagesetpixel($baseImage, $x + $outerFrame, $y + $outerFrame, $col);
                }
            }
        }
        $targetImage = imagecreate($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);
        imagecopyresized(
            $targetImage,
            $baseImage,
            0,
            0,
            0,
            0,
            $imgW * $pixelPerPoint,
            $imgH * $pixelPerPoint,
            $imgW,
            $imgH
        );
        imagedestroy($baseImage);
        return $targetImage;
    }

    private function encodeMask(QrInput $QrInput)
    {
        $QrRawCode = new QrRawCode($QrInput);
        $version = $QrRawCode->version;
        $qrSpecWidth = QrSpec::getWidth($version);
        $FrameFiller = new FrameFiller($qrSpecWidth, QrSpec::createFrame($version));
        for ($i = 0; $i < $QrRawCode->dataLength + $QrRawCode->eccLength; $i++) {
            $qrRawCode = $QrRawCode->getCode();
            $bit = 0x80;
            for ($j = 0; $j < 8; $j++) {
                $FrameFiller->setFrameAt($FrameFiller->next(), 0x02 | (($bit & $qrRawCode) != 0));
                $bit = $bit >> 1;
            }
        }
        unset($QrRawCode);
        for ($i = 0; $i < QrSpec::getRemainder($version); $i++) {
            $FrameFiller->setFrameAt($FrameFiller->next(), 0x02);
        }
        $frame = $FrameFiller->frame;
        unset($FrameFiller);
        return (new QrMask())->mask($qrSpecWidth, $frame, $QrInput->getErrorCorrectionLevel());
    }

    private function binary($frame)
    {
        $len = count($frame);
        foreach ($frame as &$frameLine) {
            for ($i = 0; $i < $len; $i++) {
                $frameLine[$i] = ord($frameLine[$i]) & 1 ? '1' : '0';
            }
        }
        return $frame;
    }
}
