<?php

namespace czdb;

use czdb\entity\DecryptedBlock;
use czdb\entity\HyperHeaderBlock;
use Exception;
use czdb\entity\DataBlock;

class DbSearcher
{
    private const SUPER_PART_LENGTH = 17;
    private const FIRST_INDEX_PTR = 5;
    private const END_INDEX_PTR = 13;
    private const HEADER_BLOCK_PTR = 9;
    private const FILE_SIZE_PTR = 1;
    private const QUERY_TYPE_MEMORY = 'MEMORY';
    private const QUERY_TYPE_BTREE = 'BTREE';
    private int $dbType;
    private int $ipBytesLength;
    private string $queryType;
    private int $totalHeaderBlockSize;
    private $raf;
    private string $fileName;
    private array $HeaderSip = [];
    private array $HeaderPtr = [];
    private int $headerLength;
    private int $firstIndexPtr = 0;
    private int $totalIndexBlocks = 0;
    private ?string $dbBinStr = null;
    private int $columnSelection = 0;
    private ?string $geoMapData = null;
    private int $headerSize;

    public function __construct($dbFile, $key, $queryType = 'BTREE')
    {
        try {
            $this->fileName = $dbFile;
            $this->queryType = $queryType;
            $this->raf = fopen($dbFile, 'rb');
            $offset = ($this->decrypt($this->raf, $key))->getHeaderSize();
            $this->headerSize = $offset;
            fseek($this->raf, $offset);
            $superBytes = array_values(unpack('C*', fread($this->raf, DbSearcher::SUPER_PART_LENGTH)));
            $this->dbType = ($superBytes[0] & 1) == 0 ? 4 : 6;
            $this->ipBytesLength = $this->dbType == 4 ? 4 : 16;
            if ($queryType == self::QUERY_TYPE_MEMORY) {
                $this->initializeForMemorySearch();
            } elseif ($queryType == self::QUERY_TYPE_BTREE) {
                $this->initBtreeModeParam();
            }
        } catch (Exception $e) {
        }
    }

    public function search($ip)
    {
        $dataBlock = null;
        try {
            $ipBytes = $this->getIpBytes($ip);
            if ($this->queryType == self::QUERY_TYPE_MEMORY) {
                $dataBlock = $this->memorySearch($ipBytes);
            } elseif ($this->queryType == self::QUERY_TYPE_BTREE) {
                $dataBlock = $this->bTreeSearch($ipBytes);
            }
        } catch (Exception $e) {
        }
        return $dataBlock == null ? null : $dataBlock->getRegion($this->geoMapData, $this->columnSelection);
    }

    public function version()
    {
        $handle = fopen($this->fileName, 'rb');
        if (!$handle) {
            return false;
        }
        $pos = -1;
        $lastLine = '';
        fseek($handle, $pos, SEEK_END);
        $char = fgetc($handle);
        while ($char !== false && $char !== "\n" && $char !== "\r") {
            $lastLine = $char . $lastLine;
            fseek($handle, --$pos, SEEK_END);
            $char = fgetc($handle);
        }
        fclose($handle);
        return preg_match('/\d{4}[-\/]\d{2}[-\/]\d{2}/', $lastLine, $matches) ? str_replace('/', '-', $matches[0]) : '';
    }

    public function close()
    {
        if (is_resource($this->raf)) {
            fclose($this->raf);
            $this->raf = null;
        }
        $this->dbBinStr = null;
        $this->HeaderSip = [];
        $this->HeaderPtr = [];
        $this->geoMapData = null;
    }

    private function compareBytes($bytes1, $bytes2, $length)
    {
        for ($i = 1; $i <= $length; $i++) {
            $byte1 = $bytes1[$i];
            $byte2 = $bytes2[$i];
            if ($byte1 != $byte2) {
                return $byte1 < $byte2 ? -1 : 1;
            }
        }
        return 0;
    }

    private function memorySearch($ip)
    {
        $l = 0;
        $h = $this->totalIndexBlocks;
        $dataPtr = 0;
        $dataLen = 0;
        while ($l <= $h) {
            $m = intval(($l + $h) / 2);
            $p = $this->firstIndexPtr + intval($m * ($this->dbType == 4 ? 13 : 37));
            $cmpStart = $this->compareBytes(
                $ip,
                unpack('C*', substr($this->dbBinStr, $p, $this->ipBytesLength)),
                $this->ipBytesLength
            );
            if (
                $cmpStart >= 0 &&
                $this->compareBytes(
                    $ip,
                    unpack('C*', substr($this->dbBinStr, $p + $this->ipBytesLength, $this->ipBytesLength)),
                    $this->ipBytesLength
                ) <= 0
            ) {
                $dataPtr = unpack('L', substr($this->dbBinStr, $p + $this->ipBytesLength * 2, 4))[1];
                $dataLen = ord($this->dbBinStr[$p + $this->ipBytesLength * 2 + 4]);
                break;
            } elseif ($cmpStart < 0) {
                $h = $m - 1;
            } else {
                $l = $m + 1;
            }
        }
        if ($dataPtr == 0) {
            return null;
        }
        return new DataBlock(substr($this->dbBinStr, $dataPtr, $dataLen));
    }

    private function bTreeSearch($ip)
    {
        $sptrNeptr = $this->searchInHeader($ip);
        $sptr = $sptrNeptr[0];
        if ($sptr == 0) {
            return null;
        }
        $blockLen = $sptrNeptr[1] - $sptr;
        $blen = $this->dbType == 4 ? 13 : 37;
        $this->fseek($this->raf, $sptr);
        $iBuffer = fread($this->raf, $blockLen + $blen);
        $l = 0;
        $h = $blockLen / $blen;
        $dataPtr = 0;
        $dataLen = 0;
        while ($l <= $h) {
            $m = intval(($l + $h) / 2);
            $p = $m * $blen;
            $sip = unpack('C*', substr($iBuffer, $p, $this->ipBytesLength));
            $eip = unpack('C*', substr($iBuffer, $p + $this->ipBytesLength, $this->ipBytesLength));
            $cmpStart = $this->compareBytes($ip, $sip, $this->ipBytesLength);
            $cmpEnd = $this->compareBytes($ip, $eip, $this->ipBytesLength);
            if ($cmpStart >= 0 && $cmpEnd <= 0) {
                $dataPtr = unpack('L', substr($iBuffer, $p + $this->ipBytesLength * 2, 4))[1];
                $dataLen = ord($iBuffer[$p + $this->ipBytesLength * 2 + 4]);
                break;
            } elseif ($cmpStart < 0) {
                $h = $m - 1;
            } else {
                $l = $m + 1;
            }
        }
        if ($dataPtr == 0) {
            return null;
        }
        $this->fseek($this->raf, $dataPtr);
        return new DataBlock(fread($this->raf, $dataLen));
    }

    /**
     * @param $ip
     * @return array|false
     * @throws Exception
     */
    private function getIpBytes($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, $this->dbType == 4 ? FILTER_FLAG_IPV4 : FILTER_FLAG_IPV6)) {
            throw new Exception('IP [' . $ip . '] format error for ' . $this->dbType);
        }
        return unpack('C*', inet_pton($ip));
    }

    private function searchInHeader($ip)
    {
        $l = 0;
        $h = $this->headerLength - 1;
        $sptr = 0;
        $eptr = 0;
        while ($l <= $h) {
            $m = intval(($l + $h) / 2);
            $cmp = $this->compareBytes($ip, $this->HeaderSip[$m], $this->ipBytesLength);
            if ($cmp < 0) {
                $h = $m - 1;
            } elseif ($cmp > 0) {
                $l = $m + 1;
            } else {
                $sptr = $this->HeaderPtr[$m > 0 ? $m - 1 : $m];
                $eptr = $this->HeaderPtr[$m];
                break;
            }
        }
        if ($l == 0) {
            return [0, 0];
        }
        if ($l > $h) {
            if ($l < $this->headerLength) {
                $sptr = $this->HeaderPtr[$l - 1];
                $eptr = $this->HeaderPtr[$l];
            } elseif ($h >= 0 && $h + 1 < $this->headerLength) {
                $sptr = $this->HeaderPtr[$h];
                $eptr = $this->HeaderPtr[$h + 1];
            } else {
                $sptr = $this->HeaderPtr[$this->headerLength - 1];
                $eptr = $sptr + ($this->dbType == 4 ? 13 : 37);
            }
        }
        return [$sptr, $eptr];
    }

    /**
     * 为内存搜索初始化参数。
     * @throws Exception 如果文件大小不匹配。
     */
    private function initializeForMemorySearch()
    {
        $this->fseek($this->raf, 0);
        $fileSize = filesize($this->fileName) - $this->headerSize;
        $this->dbBinStr = fread($this->raf, $fileSize);
        $this->totalHeaderBlockSize = unpack('L', $this->dbBinStr, self::HEADER_BLOCK_PTR)[1];
        $fileSizeInFile = unpack('L', $this->dbBinStr, self::FILE_SIZE_PTR)[1];
        if ($fileSize != $fileSizeInFile) {
            throw new Exception('FileSize not match with the file');
        }
        $this->firstIndexPtr = unpack('L', $this->dbBinStr, self::FIRST_INDEX_PTR)[1];
        $lastIndexPtr = unpack('L', $this->dbBinStr, self::END_INDEX_PTR)[1];
        $this->totalIndexBlocks = (int)(($lastIndexPtr - $this->firstIndexPtr) / ($this->dbType == 4 ? 13 : 37)) + 1;
        $headerBlockBytes = substr($this->dbBinStr, self::SUPER_PART_LENGTH, $this->totalHeaderBlockSize);
        $this->initHeaderBlock($headerBlockBytes, $this->totalHeaderBlockSize);
    }

    private function initBtreeModeParam()
    {
        $this->fseek($this->raf, 0);
        $data = fread($this->raf, self::SUPER_PART_LENGTH);
        $this->totalHeaderBlockSize = unpack('L', $data, self::HEADER_BLOCK_PTR)[1];
        $data = fread($this->raf, $this->totalHeaderBlockSize);
        $this->initHeaderBlock($data, $this->totalHeaderBlockSize);
    }

    private function initHeaderBlock($headerBytes, $size)
    {
        $indexLength = 20;
        $idx = 0;
        for ($i = 0; $i < $size; $i += $indexLength) {
            $dataPtrSegment = substr($headerBytes, $i + 16, 4);
            $dataPtr = unpack('L', $dataPtrSegment)[1];
            if ($dataPtr === 0) {
                break;
            }
            $this->HeaderSip[$idx] = unpack('C*', substr($headerBytes, $i, 16));
            $this->HeaderPtr[$idx] = $dataPtr;
            $idx++;
        }
        $this->headerLength = $idx;
    }

    private function fseek($handler, $offset)
    {
        fseek($handler, $this->headerSize + $offset);
    }

    /**
     * @throws Exception
     */
    private function decrypt($is, $key)
    {
        $headerBytes = fread($is, HyperHeaderBlock::HEADER_SIZE);
        $version = unpack('L', $headerBytes)[1];
        $clientId = unpack('L', $headerBytes, 4)[1];
        $encryptedBlockSize = unpack('L', $headerBytes, 8)[1];
        $encryptedBytes = fread($is, $encryptedBlockSize);
        $decryptedBlock = DecryptedBlock::decrypt($key, $encryptedBytes);
        if ($decryptedBlock->getClientId() != $clientId) {
            throw new Exception('Wrong clientId');
        }
        $currentDate = intval(date('ymd'));
        if ($decryptedBlock->getExpirationDate() < $currentDate) {
            throw new Exception('DB is expired');
        }
        $HyperHeaderBlock = new HyperHeaderBlock();
        $HyperHeaderBlock->setVersion($version);
        $HyperHeaderBlock->setClientId($clientId);
        $HyperHeaderBlock->setEncryptedBlockSize($encryptedBlockSize);
        $HyperHeaderBlock->setDecryptedBlock($decryptedBlock);
        return $HyperHeaderBlock;
    }
}
