<?php

namespace qrCode;

class QrInput
{
    public array $qrInputItem;
    private int $version;
    private int $level;

    public function __construct($version = 0, $level = 0)
    {
        $this->version = $version;
        $this->level = $level;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
        return 0;
    }

    public function getErrorCorrectionLevel()
    {
        return $this->level;
    }

    public function append($mode, $size, $data)
    {
        $this->qrInputItem[] = new QrInputItem($mode, $size, $data);
        return 0;
    }

    public static function estimateBitsModeNum($size)
    {
        $w = (int)$size / 3;
        $bits = $w * 10;
        switch ($size - $w * 3) {
            case 1:
                $bits += 4;
                break;
            case 2:
                $bits += 7;
                break;
        }
        return $bits;
    }

    public static function lookAnTable($c)
    {
        return $c > 127 ? -1 : [-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
            -1,-1,-1,36,-1,-1,-1,37,38,-1,-1,-1,-1,39,40,-1,41,42,43,0,1,2,3,4,5,6,7,8,9,44,-1,-1,-1,-1,-1,-1,10,11,12,
            13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
            -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1][$c];
    }

    public function getByteStream()
    {
        return $this->getBitStream()->toByte();
    }

    private function estimateBitStreamSize($version)
    {
        $bits = 0;
        foreach ($this->qrInputItem as $item) {
            $bits += $item->estimateBitStreamSizeOfEntry($version);
        }
        return $bits;
    }

    private function estimateVersion()
    {
        $qrSpecMinimumVersion = 0;
        do {
            $prev = $qrSpecMinimumVersion;
            $qrSpecMinimumVersion =
                QrSpec::getMinimumVersion((int)(($this->estimateBitStreamSize($prev) + 7) / 8), $this->level);
            if ($qrSpecMinimumVersion < 0) {
                return -1;
            }
        } while ($qrSpecMinimumVersion > $prev);
        return $qrSpecMinimumVersion;
    }

    private function createBitStream()
    {
        $total = 0;
        foreach ($this->qrInputItem as $item) {
            $encodeBitStream = $item->encodeBitStream($this->version);
            if ($encodeBitStream < 0) {
                return -1;
            }
            $total += $encodeBitStream;
        }
        return $total;
    }

    private function convertData()
    {
        $estimateVersion = $this->estimateVersion();
        if ($estimateVersion > $this->getVersion()) {
            $this->setVersion($estimateVersion);
        }
        $bitStream = $this->createBitStream();
        if ($bitStream < 0) {
            return -1;
        }
        $estimateVersion = QrSpec::getMinimumVersion((int)(($bitStream + 7) / 8), $this->level);
        if ($estimateVersion > $this->getVersion()) {
            $this->setVersion($estimateVersion);
        }
        return 0;
    }

    private function mergeBitStream()
    {
        if ($this->convertData() < 0) {
            return null;
        }
        $QrBitStream = new QrBitStream();
        foreach ($this->qrInputItem as $item) {
            $ret = $QrBitStream->append($item->qrBitStream);
            if ($ret < 0) {
                return null;
            }
        }
        return $QrBitStream;
    }

    private function getBitStream()
    {
        $mergeBitStream = $this->mergeBitStream();
        $bits = $mergeBitStream->size();
        $qrSpecDataLength = QrSpec::getDataLength($this->version, $this->level);
        $maxBits = $qrSpecDataLength * 8;
        if ($maxBits == $bits) {
            return 0;
        }
        if ($maxBits - $bits < 5) {
            return $mergeBitStream->appendNum($maxBits - $bits, 0);
        }
        $bits += 4;
        $words = (int)(($bits + 7) / 8);
        $QrBitStream = new QrBitStream();
        $ret = $QrBitStream->appendNum($words * 8 - $bits + 4, 0);
        if ($ret < 0) {
            return $ret;
        }
        $padLen = $qrSpecDataLength - $words;
        if ($padLen > 0) {
            $padBuf = [];
            for ($i = 0; $i < $padLen; $i++) {
                $padBuf[$i] = ($i & 1) ? 0x11 : 0xec;
            }
            $ret = $QrBitStream->appendBytes($padLen, $padBuf);
            if ($ret < 0) {
                return $ret;
            }
        }
        $mergeBitStream->append($QrBitStream);
        return $mergeBitStream;
    }
}
