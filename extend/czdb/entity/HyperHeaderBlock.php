<?php

namespace czdb\entity;

class HyperHeaderBlock
{
    public const HEADER_SIZE = 12;
    protected string $version;
    protected int $clientId;
    protected int $encryptedBlockSize;
    protected DecryptedBlock $decryptedBlock;

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    public function setEncryptedBlockSize($encryptedBlockSize)
    {
        $this->encryptedBlockSize = $encryptedBlockSize;
    }

    public function setDecryptedBlock($decryptedBlock)
    {
        $this->decryptedBlock = $decryptedBlock;
    }

    public function getHeaderSize()
    {
        return 12 + $this->encryptedBlockSize + $this->decryptedBlock->getRandomSize();
    }
}
