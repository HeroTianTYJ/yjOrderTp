<?php

namespace qrCode;

class QrBitStream
{
    private array $data = [];

    public function size()
    {
        return count($this->data);
    }

    public function append(QrBitStream $QrBitStream)
    {
        if ($QrBitStream->size() == 0) {
            return 0;
        }
        if ($this->size() == 0) {
            $this->data = $QrBitStream->data;
            return 0;
        }
        $this->data = array_values(array_merge($this->data, $QrBitStream->data));
        return 0;
    }

    public function appendNum($bits, $num)
    {
        if ($bits == 0) {
            return 0;
        }
        $newFromNum = QrBitStream::newFromNum($bits, $num);
        if (is_null($newFromNum)) {
            return -1;
        }
        $ret = $this->append($newFromNum);
        unset($newFromNum);
        return $ret;
    }

    public function appendBytes($size, $data)
    {
        if ($size == 0) {
            return 0;
        }
        $newFromBytes = QrBitStream::newFromBytes($size, $data);
        if (is_null($newFromBytes)) {
            return -1;
        }
        $ret = $this->append($newFromBytes);
        unset($newFromBytes);
        return $ret;
    }

    public function toByte()
    {
        $size = $this->size();
        if ($size == 0) {
            return [];
        }
        $data = array_fill(0, (int)(($size + 7) / 8), 0);
        $bytes = (int)($size / 8);
        $p = 0;
        for ($i = 0; $i < $bytes; $i++) {
            $v = 0;
            for ($j = 0; $j < 8; $j++) {
                $v = $v << 1;
                $v |= $this->data[$p];
                $p++;
            }
            $data[$i] = $v;
        }
        if ($size & 7) {
            $v = 0;
            for ($j = 0; $j < ($size & 7); $j++) {
                $v = $v << 1;
                $v |= $this->data[$p];
                $p++;
            }
            $data[$bytes] = $v;
        }
        return $data;
    }

    private static function newFromNum($bits, $num)
    {
        $QrBitStream = new QrBitStream();
        $QrBitStream->data = array_fill(0, $bits, 0);
        $mask = 1 << ($bits - 1);
        for ($i = 0; $i < $bits; $i++) {
            if ($num & $mask) {
                $QrBitStream->data[$i] = 1;
            } else {
                $QrBitStream->data[$i] = 0;
            }
            $mask = $mask >> 1;
        }
        return $QrBitStream;
    }

    private static function newFromBytes($size, $data)
    {
        $QrBitStream = new QrBitStream();
        $QrBitStream->data = array_fill(0, $size * 8, 0);
        $p = 0;
        for ($i = 0; $i < $size; $i++) {
            $mask = 0x80;
            for ($j = 0; $j < 8; $j++) {
                $QrBitStream->data[$p] = $data[$i] & $mask ? 1 : 0;
                $p++;
                $mask = $mask >> 1;
            }
        }
        return $QrBitStream;
    }
}
