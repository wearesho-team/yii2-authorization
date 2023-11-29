<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Wearesho\Yii2\Authorization\Repository\RefreshTokenValue;

class RefreshTokenValueTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $accessToken = 'sampleAccessToken';
        $userId = 123;

        $refreshToken = new RefreshTokenValue($accessToken, $userId);

        $this->assertSame($accessToken, $refreshToken->getAccessToken());
        $this->assertSame($userId, $refreshToken->getUserId());
    }
}
