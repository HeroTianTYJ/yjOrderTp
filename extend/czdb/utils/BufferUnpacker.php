<?php

namespace czdb\utils;

use Decimal\Decimal;

class BufferUnpacker
{
    private string $buffer;
    private int $offset = 0;
    private bool $isBigIntAsDec;
    private bool $isBigIntAsGmp;
    private array $extensions = [];

    public function __construct(string $buffer = '', $options = null, array $extensions = [])
    {
        if (is_null($options)) {
            $options = UnpackOptions::fromDefaults();
        } elseif (!$options instanceof UnpackOptions) {
            $options = UnpackOptions::fromBitmask($options);
        }

        $this->isBigIntAsDec = $options->isBigIntAsDecMode();
        $this->isBigIntAsGmp = $options->isBigIntAsGmpMode();

        $this->buffer = $buffer;

        if ($extensions) {
            foreach ($extensions as $extension) {
                $this->extensions[$extension->getType()] = $extension;
            }
        }
    }

    public function reset(string $buffer = ''): self
    {
        $this->buffer = $buffer;
        $this->offset = 0;

        return $this;
    }

    public function read($length)
    {
        $data = substr($this->buffer, $this->offset, $length);
        $this->offset += $length;
        return $data;
    }

    public function unpack()
    {
        $c = ord($this->buffer[$this->offset]);
        ++$this->offset;
        if ($c <= 0x7f) {
            return $c;
        }
        if ($c >= 0xa0 && $c <= 0xbf) {
            return ($c & 0x1f) ? $this->read($c & 0x1f) : '';
        }
        if ($c >= 0xe0) {
            return $c - 0x100;
        }
        switch ($c) {
            case 0xc0:
                return null;
            case 0xc2:
                return false;
            case 0xc3:
                return true;
            case 0x90:
            case 0x80:
                return [];
            case 0x81:
                return [$this->unpackMapKey() => $this->unpack()];
            case 0x82:
                return [$this->unpackMapKey() => $this->unpack(), $this->unpackMapKey() => $this->unpack()];
            case 0x83:
                return [
                    $this->unpackMapKey() => $this->unpack(),
                    $this->unpackMapKey() => $this->unpack(),
                    $this->unpackMapKey() => $this->unpack()
                ];
            case 0x84:
                return $this->unpackMapData(4);
            case 0x85:
                return $this->unpackMapData(5);
            case 0x86:
                return $this->unpackMapData(6);
            case 0x87:
                return $this->unpackMapData(7);
            case 0x88:
                return $this->unpackMapData(8);
            case 0x89:
                return $this->unpackMapData(9);
            case 0x8a:
                return $this->unpackMapData(10);
            case 0x8b:
                return $this->unpackMapData(11);
            case 0x8c:
                return $this->unpackMapData(12);
            case 0x8d:
                return $this->unpackMapData(13);
            case 0x8e:
                return $this->unpackMapData(14);
            case 0x8f:
                return $this->unpackMapData(15);
            case 0x91:
                return [$this->unpack()];
            case 0x92:
                return [$this->unpack(), $this->unpack()];
            case 0x93:
                return [$this->unpack(), $this->unpack(), $this->unpack()];
            case 0x94:
                return $this->unpackArrayData(4);
            case 0x95:
                return $this->unpackArrayData(5);
            case 0x96:
                return $this->unpackArrayData(6);
            case 0x97:
                return $this->unpackArrayData(7);
            case 0x98:
                return $this->unpackArrayData(8);
            case 0x99:
                return $this->unpackArrayData(9);
            case 0x9a:
                return $this->unpackArrayData(10);
            case 0x9b:
                return $this->unpackArrayData(11);
            case 0x9c:
                return $this->unpackArrayData(12);
            case 0x9d:
                return $this->unpackArrayData(13);
            case 0x9e:
                return $this->unpackArrayData(14);
            case 0x9f:
                return $this->unpackArrayData(15);
            case 0xd9:
            case 0xc4:
                return $this->read($this->unpackUint8());
            case 0xda:
            case 0xc5:
                return $this->read($this->unpackUint16());
            case 0xdb:
            case 0xc6:
                return $this->read($this->unpackUint32());
            // float
            case 0xca:
                return $this->unpackFloat32();
            case 0xcb:
                return $this->unpackFloat64();
            // uint
            case 0xcc:
                return $this->unpackUint8();
            case 0xcd:
                return $this->unpackUint16();
            case 0xce:
                return $this->unpackUint32();
            case 0xcf:
                return $this->unpackUint64();
            case 0xd0:
                return $this->unpackInt8();
            case 0xd1:
                return $this->unpackInt16();
            case 0xd2:
                return $this->unpackInt32();
            case 0xd3:
                return $this->unpackInt64();
            case 0xdc:
                return $this->unpackArrayData($this->unpackUint16());
            case 0xdd:
                return $this->unpackArrayData($this->unpackUint32());
            case 0xde:
                return $this->unpackMapData($this->unpackUint16());
            case 0xdf:
                return $this->unpackMapData($this->unpackUint32());
            case 0xd4:
                return $this->unpackExtData(1);
            case 0xd5:
                return $this->unpackExtData(2);
            case 0xd6:
                return $this->unpackExtData(4);
            case 0xd7:
                return $this->unpackExtData(8);
            case 0xd8:
                return $this->unpackExtData(16);
            case 0xc7:
                return $this->unpackExtData($this->unpackUint8());
            case 0xc8:
                return $this->unpackExtData($this->unpackUint16());
            case 0xc9:
                return $this->unpackExtData($this->unpackUint32());
        }
        return null;
    }

    public function unpackInt()
    {
        $c = ord($this->buffer[$this->offset]);
        ++$this->offset;
        if ($c <= 0x7f) {
            return $c;
        }
        if ($c >= 0xe0) {
            return $c - 0x100;
        }
        switch ($c) {
            case 0xcc:
                return $this->unpackUint8();
            case 0xcd:
                return $this->unpackUint16();
            case 0xce:
                return $this->unpackUint32();
            case 0xcf:
                return $this->unpackUint64();
            case 0xd0:
                return $this->unpackInt8();
            case 0xd1:
                return $this->unpackInt16();
            case 0xd2:
                return $this->unpackInt32();
            case 0xd3:
                return $this->unpackInt64();
        }
        return null;
    }

    public function unpackStr()
    {
        $c = ord($this->buffer[$this->offset]);
        ++$this->offset;
        if ($c >= 0xa0 && $c <= 0xbf) {
            return ($c & 0x1f) ? $this->read($c & 0x1f) : '';
        }
        if (0xd9 === $c) {
            return $this->read($this->unpackUint8());
        }
        if (0xda === $c) {
            return $this->read($this->unpackUint16());
        }
        if (0xdb === $c) {
            return $this->read($this->unpackUint32());
        }
        return null;
    }

    public function unpackArrayHeader()
    {
        $c = ord($this->buffer[$this->offset]);
        ++$this->offset;
        if ($c >= 0x90 && $c <= 0x9f) {
            return $c & 0xf;
        }
        if (0xdc === $c) {
            return $this->unpackUint16();
        }
        if (0xdd === $c) {
            return $this->unpackUint32();
        }
        return null;
    }

    private function unpackUint8()
    {
        return ord($this->buffer[$this->offset++]);
    }

    private function unpackUint16()
    {
        return ord($this->buffer[$this->offset++]) << 8 | ord($this->buffer[$this->offset++]);
    }

    private function unpackUint32()
    {
        return ord($this->buffer[$this->offset++]) << 24 | ord($this->buffer[$this->offset++]) << 16
            | ord($this->buffer[$this->offset++]) << 8 | ord($this->buffer[$this->offset++]);
    }

    private function unpackUint64()
    {
        $num = unpack('J', $this->buffer, $this->offset)[1];
        $this->offset += 8;
        if ($num >= 0) {
            return $num;
        }
        if ($this->isBigIntAsDec) {
            return new Decimal(sprintf('%u', $num));
        }
        if ($this->isBigIntAsGmp) {
            return gmp_import(substr($this->buffer, $this->offset - 8, 8));
        }
        return sprintf('%u', $num);
    }

    private function unpackUint64MapKey()
    {
        $num = unpack('J', $this->buffer, $this->offset)[1];
        $this->offset += 8;
        return $num >= 0 ? $num : sprintf('%u', $num);
    }

    private function unpackInt8()
    {
        $num = ord($this->buffer[$this->offset]);
        ++$this->offset;
        return $num > 0x7f ? $num - 0x100 : $num;
    }

    private function unpackInt16()
    {
        $num = ord($this->buffer[$this->offset]) << 8 | ord($this->buffer[++$this->offset]);
        ++$this->offset;
        return $num > 0x7fff ? $num - 0x10000 : $num;
    }

    private function unpackInt32()
    {
        $num = ord($this->buffer[$this->offset]) << 24 | ord($this->buffer[++$this->offset]) << 16
            | ord($this->buffer[++$this->offset]) << 8 | ord($this->buffer[++$this->offset]);
        ++$this->offset;
        return $num > 0x7fffffff ? $num - 0x100000000 : $num;
    }

    private function unpackInt64()
    {
        $num = unpack('J', $this->buffer, $this->offset)[1];
        $this->offset += 8;
        return $num;
    }

    private function unpackFloat32()
    {
        $num = unpack('G', $this->buffer, $this->offset)[1];
        $this->offset += 4;
        return $num;
    }

    private function unpackFloat64()
    {
        $num = unpack('E', $this->buffer, $this->offset)[1];
        $this->offset += 8;
        return $num;
    }

    private function unpackArrayData($size)
    {
        $array = [];
        while ($size--) {
            $array[] = $this->unpack();
        }
        return $array;
    }

    private function unpackMapData($size)
    {
        $map = [];
        while ($size--) {
            $map[$this->unpackMapKey()] = $this->unpack();
        }
        return $map;
    }

    private function unpackMapKey()
    {
        $c = ord($this->buffer[$this->offset]);
        ++$this->offset;
        if ($c <= 0x7f) {
            return $c;
        }
        if ($c >= 0xa0 && $c <= 0xbf) {
            return ($c & 0x1f) ? $this->read($c & 0x1f) : '';
        }
        if ($c >= 0xe0) {
            return $c - 0x100;
        }
        switch ($c) {
            case 0xcc:
                return $this->unpackUint8();
            case 0xcd:
                return $this->unpackUint16();
            case 0xce:
                return $this->unpackUint32();
            case 0xcf:
                return $this->unpackUint64MapKey();
            case 0xd0:
                return $this->unpackInt8();
            case 0xd1:
                return $this->unpackInt16();
            case 0xd2:
                return $this->unpackInt32();
            case 0xd3:
                return $this->unpackInt64();
            case 0xd9:
            case 0xc4:
                return $this->read($this->unpackUint8());
            case 0xda:
            case 0xc5:
                return $this->read($this->unpackUint16());
            case 0xdb:
            case 0xc6:
                return $this->read($this->unpackUint32());
        }
        return null;
    }

    private function unpackExtData($length)
    {
        $type = ord($this->buffer[$this->offset]);
        ++$this->offset;
        if ($type > 0x7f) {
            $type -= 0x100;
        }
        if (isset($this->extensions[$type])) {
            return $this->extensions[$type]->unpackExt($this, $length);
        }
        $this->offset += $length;
        return null;
    }
}
