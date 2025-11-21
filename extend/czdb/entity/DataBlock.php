<?php

namespace czdb\entity;

use Exception;
use czdb\utils\BufferUnpacker;

class DataBlock
{
    private string $region;

    public function __construct($region)
    {
        $this->region = $region;
    }

    public function getRegion($geoMapData, $columnSelection)
    {
        try {
            $unpacker = new BufferUnpacker();
            $unpacker->reset($this->region);
            $geoPosMixSize = $unpacker->unpackInt();
            $otherData = $unpacker->unpackStr();
            if ($geoPosMixSize == 0) {
                return $otherData;
            }
            $regionData = substr($geoMapData, $geoPosMixSize & 0x00FFFFFF, ($geoPosMixSize >> 24) & 0xFF);
            $sb = '';
            $unpacker->reset($regionData);
            $columnNumber = $unpacker->unpackArrayHeader();
            for ($i = 0; $i < $columnNumber; $i++) {
                $columnSelected = ($columnSelection >> ($i + 1) & 1) == 1;
                $value = $unpacker->unpackStr();
                $value = $value === '' ? 'null' : $value;
                if ($columnSelected) {
                    $sb .= $value . "\t";
                }
            }
            return $sb . $otherData;
        } catch (Exception $e) {
            return null;
        }
    }
}
