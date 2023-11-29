<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Repository;

class RefreshTokenValueEncoder
{
    public const VALUE_PREFIX = 'v2';
    public const VALUE_SEPARATOR = ':';
    public const VALUE_HASH_ALGO = 'fnv1a32';

    public function encode(RefreshTokenValue $value): string
    {
        $values = [
            static::VALUE_PREFIX,
            $value->getAccessToken(),
            $value->getUserId(),
            $this->getHash($value),
        ];
        return implode(static::VALUE_SEPARATOR, $values);
    }

    public function decode(string $value): ?RefreshTokenValue
    {
        $values = explode(static::VALUE_SEPARATOR, $value, 4);
        if (count($values) !== 4) {
            return null;
        }
        [$prefix, $accessToken, $userId, $hash] = $values;
        if ($prefix !== static::VALUE_PREFIX) {
            return null;
        }
        $value = new RefreshTokenValue($accessToken, (int)$userId);
        $validHash = $this->getHash($value);
        if ($validHash !== $hash) {
            return null;
        }
        return $value;
    }

    private function getHash(RefreshTokenValue $value): string
    {
        return hash(
            static::VALUE_HASH_ALGO,
            implode(static::VALUE_SEPARATOR, [
                strrev($value->getAccessToken()),
                static::VALUE_PREFIX,
                strrev((string)$value->getUserId())
            ])
        );
    }
}
