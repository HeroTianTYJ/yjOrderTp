<?php

namespace qrCode;

class QrRsItem
{
    private int $mm;
    private int $nn;
    private array $alphaTo = [];
    private array $indexOf = [];
    private array $genPoly = [];
    private int $nRoots;
    private int $pad;

    public static function initRsChar($symSize, $gfPoly, $fcr, $prim, $nRoots, $pad)
    {
        $QrRsItem = new QrRsItem();
        $QrRsItem->mm = $symSize;
        $QrRsItem->nn = (1 << $symSize) - 1;
        $QrRsItem->pad = $pad;
        $QrRsItem->alphaTo = array_fill(0, $QrRsItem->nn + 1, 0);
        $QrRsItem->indexOf = array_fill(0, $QrRsItem->nn + 1, 0);
        $NN =& $QrRsItem->nn;
        $A0 =& $NN;
        $QrRsItem->indexOf[0] = $A0;
        $QrRsItem->alphaTo[$A0] = 0;
        $sr = 1;
        for ($i = 0; $i < $QrRsItem->nn; $i++) {
            $QrRsItem->indexOf[$sr] = $i;
            $QrRsItem->alphaTo[$i] = $sr;
            $sr <<= 1;
            if ($sr & (1 << $symSize)) {
                $sr ^= $gfPoly;
            }
            $sr &= $QrRsItem->nn;
        }
        if ($sr != 1) {
            echo 11;
            return null;
        }
        $QrRsItem->genPoly = array_fill(0, $nRoots + 1, 0);
        $QrRsItem->nRoots = $nRoots;
        $QrRsItem->genPoly[0] = 1;
        for ($i = 0, $root = $fcr * $prim; $i < $nRoots; $i++, $root += $prim) {
            $QrRsItem->genPoly[$i + 1] = 1;
            for ($j = $i; $j > 0; $j--) {
                if ($QrRsItem->genPoly[$j] != 0) {
                    $QrRsItem->genPoly[$j] = $QrRsItem->genPoly[$j - 1] ^
                        $QrRsItem->alphaTo[$QrRsItem->modNn($QrRsItem->indexOf[$QrRsItem->genPoly[$j]] + $root)];
                } else {
                    $QrRsItem->genPoly[$j] = $QrRsItem->genPoly[$j - 1];
                }
            }
            $QrRsItem->genPoly[0] =
                $QrRsItem->alphaTo[$QrRsItem->modNn($QrRsItem->indexOf[$QrRsItem->genPoly[0]] + $root)];
        }
        for ($i = 0; $i <= $nRoots; $i++) {
            $QrRsItem->genPoly[$i] = $QrRsItem->indexOf[$QrRsItem->genPoly[$i]];
        }
        return $QrRsItem;
    }

    public function encodeRsChar($data, &$parity)
    {
        $nn =& $this->nn;
        $alphaTo =& $this->alphaTo;
        $indexOf =& $this->indexOf;
        $genPoly =& $this->genPoly;
        $nRoots =& $this->nRoots;
        $pad =& $this->pad;
        $a0 =& $nn;
        $parity = array_fill(0, $nRoots, 0);
        for ($i = 0; $i < ($nn - $nRoots - $pad); $i++) {
            $feedback = $indexOf[$data[$i] ^ $parity[0]];
            if ($feedback != $a0) {
                $feedback = $this->modNn($nn - $genPoly[$nRoots] + $feedback);
                for ($j = 1; $j < $nRoots; $j++) {
                    $parity[$j] ^= $alphaTo[$this->modNn($feedback + $genPoly[$nRoots - $j])];
                }
            }
            array_shift($parity);
            if ($feedback != $a0) {
                $parity[] = $alphaTo[$this->modNn($feedback + $genPoly[0])];
            } else {
                $parity[] = 0;
            }
        }
    }

    private function modNn($x)
    {
        while ($x >= $this->nn) {
            $x -= $this->nn;
            $x = ($x >> $this->mm) + ($x & $this->nn);
        }
        return $x;
    }
}
