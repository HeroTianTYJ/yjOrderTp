<?php

namespace czdb\utils;

final class UnpackOptions
{
    public const BIGINT_AS_STR = 0b001;
    public const BIGINT_AS_GMP = 0b010;
    public const BIGINT_AS_DEC = 0b100;
    private int $bigIntMode;

    private function __construct($bigIntMode)
    {
        $this->bigIntMode = $bigIntMode;
    }

    public static function fromDefaults(): self
    {
        return new self(self::BIGINT_AS_STR);
    }

    public static function fromBitmask(int $bitmask): self
    {
        return new self(
            self::getSingleOption($bitmask, self::BIGINT_AS_STR | self::BIGINT_AS_GMP | self::BIGINT_AS_DEC)
                ?: self::BIGINT_AS_STR
        );
    }

    public function isBigIntAsGmpMode(): bool
    {
        return self::BIGINT_AS_GMP === $this->bigIntMode;
    }

    public function isBigIntAsDecMode(): bool
    {
        return self::BIGINT_AS_DEC === $this->bigIntMode;
    }

    private static function getSingleOption(int $bitmask, int $validBitmask): int
    {
        $option = $bitmask & $validBitmask;
        if ($option === ($option & -$option)) {
            return $option;
        }
        return 0;
    }
}
