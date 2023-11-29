<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Repository;

class RefreshTokenValue
{
    private string $accessToken;
    private int $userId;

    public function __construct(string $accessToken, int $userId)
    {
        $this->accessToken = $accessToken;
        $this->userId = $userId;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
