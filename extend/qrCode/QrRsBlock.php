<?php

namespace qrCode;

class QrRsBlock
{
    public int $dataLength;
    public array $data = [];
    public array $ecc = [];

    public function __construct($dl, $data, $ecc, QrRsItem $QrRsItem)
    {
        $QrRsItem->encodeRsChar($data, $ecc);
        $this->dataLength = $dl;
        $this->data = $data;
        $this->ecc = $ecc;
    }
}
