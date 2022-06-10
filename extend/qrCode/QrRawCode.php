<?php

namespace qrCode;

class QrRawCode
{
    public $version;
    public ?array $byteStream = [];
    public $dataLength;
    public $eccLength;
    private array $eccCode;
    private int $blocks;
    private array $rsBlocks = [];
    private int $count;
    private int $b1;

    public function __construct(QrInput $QrInput)
    {
        $spec = [0, 0, 0, 0, 0];
        $this->byteStream = $QrInput->getByteStream();
        QrSpec::getEccSpec($QrInput->getVersion(), $QrInput->getErrorCorrectionLevel(), $spec);
        $this->version = $QrInput->getVersion();
        $this->b1 = $spec[0];
        $this->dataLength = $spec[0] * $spec[1] + $spec[3] * $spec[4];
        $this->eccLength = ($spec[0] + $spec[3]) * $spec[2];
        $this->eccCode = array_fill(0, $this->eccLength, 0);
        $this->blocks = $spec[0] + $spec[3];
        $this->init($spec);
        $this->count = 0;
    }

    public function getCode()
    {
        if ($this->count < $this->dataLength) {
            $row = $this->count % $this->blocks;
            $col = $this->count / $this->blocks;
            if ($col >= $this->rsBlocks[0]->dataLength) {
                $row += $this->b1;
            }
            $ret = $this->rsBlocks[$row]->data[$col];
        } elseif ($this->count < $this->dataLength + $this->eccLength) {
            $row = ($this->count - $this->dataLength) % $this->blocks;
            $col = ($this->count - $this->dataLength) / $this->blocks;
            $ret = $this->rsBlocks[$row]->ecc[$col];
        } else {
            return 0;
        }
        $this->count++;
        return $ret;
    }

    private function init(array $spec)
    {
        $dl = $spec[1];
        $el = $spec[2];
        $rs = QrRsItem::initRsChar(8, 0x11d, 0, 1, $el, 255 - $dl - $el);
        $blockNo = 0;
        $dataPos = 0;
        $eccPos = 0;
        for ($i = 0; $i < $spec[0]; $i++) {
            $ecc = array_slice($this->eccCode, $eccPos);
            $this->rsBlocks[$blockNo] = new QrRsBlock($dl, array_slice($this->byteStream, $dataPos), $ecc, $rs);
            $this->eccCode = array_merge(array_slice($this->eccCode, 0, $eccPos), $ecc);
            $dataPos += $dl;
            $eccPos += $el;
            $blockNo++;
        }
    }
}
