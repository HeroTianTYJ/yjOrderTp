<?php

namespace czdb\entity;

class DecryptedBlock
{
    private int $clientId;
    private int $expirationDate;
    private int $randomSize;

    public function getClientId()
    {
        return $this->clientId;
    }

    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    public function getRandomSize()
    {
        return $this->randomSize;
    }

    public function setRandomSize($randomSize)
    {
        $this->randomSize = $randomSize;
    }

    public static function decrypt($key, $encryptedBytes)
    {
        $DecryptedBlock = new DecryptedBlock();
        $decryptedBytes = array_values(
            unpack('C*', openssl_decrypt($encryptedBytes, 'AES-128-ECB', base64_decode($key), OPENSSL_RAW_DATA))
        );
        $DecryptedBlock->setClientId(self::getIntLong($decryptedBytes, 0) >> 20);
        $DecryptedBlock->setExpirationDate(self::getIntLong($decryptedBytes, 0) & 0xFFFFF);
        $DecryptedBlock->setRandomSize(self::getIntLong($decryptedBytes, 4));
        return $DecryptedBlock;
    }

    private static function getIntLong(array $b, int $offset): int
    {
        return (($b[$offset++] & 0xFF) | (($b[$offset++] << 8) & 0xFF00) | (($b[$offset++] << 16) & 0xFF0000) |
            (($b[$offset] << 24) & 0xFF000000));
    }
}
