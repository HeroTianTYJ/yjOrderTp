<?php

namespace qrCode;

class QrInputItem
{
    private int $mode;
    private int $size;
    private array $data;
    public QrBitStream $qrBitStream;

    public function __construct($mode, $size, $data)
    {
        $setData = array_slice($data, 0, $size);
        if (count($setData) < $size) {
            $setData = array_merge($setData, array_fill(0, $size - count($setData), 0));
        }
        $this->mode = $mode;
        $this->size = $size;
        $this->data = $setData;
    }

    public function estimateBitStreamSizeOfEntry($version)
    {
        $qrSpecLengthIndicator = QrSpec::lengthIndicator($this->mode, $version == 0 ? 1 : $version);
        $m = 1 << $qrSpecLengthIndicator;
        return (int)(($this->size + $m - 1) / $m) * (4 + $qrSpecLengthIndicator);
    }

    public function encodeBitStream($version)
    {
        unset($this->qrBitStream);
        $QrBitStream = new QrBitStream();
        switch ($this->mode) {
            case 0:
                $QrBitStream->appendNum(4, 0x1);
                $QrBitStream->appendNum(QrSpec::lengthIndicator(0, $version), $this->size);
                $words = (int)($this->size / 3);
                for ($i = 0; $i < $words; $i++) {
                    $QrBitStream->appendNum(10, (ord($this->data[$i * 3]) - ord('0')) * 100 +
                        (ord($this->data[$i * 3 + 1]) - ord('0')) * 10 + (ord($this->data[$i * 3 + 2]) - ord('0')));
                }
                if ($this->size - $words * 3 == 1) {
                    $QrBitStream->appendNum(4, ord($this->data[$words * 3]) - ord('0'));
                } elseif ($this->size - $words * 3 == 2) {
                    $QrBitStream->appendNum(7, (ord($this->data[$words * 3]) - ord('0')) * 10 +
                        (ord($this->data[$words * 3 + 1]) - ord('0')));
                }
                break;
            case 1:
                $QrBitStream->appendNum(4, 0x02);
                $QrBitStream->appendNum(QrSpec::lengthIndicator(1, $version), $this->size);
                $words = (int)($this->size / 2);
                for ($i = 0; $i < $words; $i++) {
                    $QrBitStream->appendNum(
                        11,
                        QrInput::lookAnTable(ord($this->data[$i * 2])) * 45 +
                        QrInput::lookAnTable(ord($this->data[$i * 2 + 1]))
                    );
                }
                if ($this->size & 1) {
                    $QrBitStream->appendNum(6, QrInput::lookAnTable(ord($this->data[$words * 2])));
                }
                break;
            case 2:
                $QrBitStream->appendNum(4, 0x4);
                $QrBitStream->appendNum(QrSpec::lengthIndicator(2, $version), $this->size);
                for ($i = 0; $i < $this->size; $i++) {
                    $QrBitStream->appendNum(8, ord($this->data[$i]));
                }
                break;
        }
        $this->qrBitStream = $QrBitStream;
        return $this->qrBitStream->size();
    }
}
