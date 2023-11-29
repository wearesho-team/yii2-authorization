<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Tests;

use PHPUnit\Framework\TestCase;
use Wearesho\Yii2\Authorization;

class EnvironmentConfigTest extends TestCase
{
    /** @var Authorization\EnvironmentConfig */
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new Authorization\EnvironmentConfig();
        putenv('AUTHORIZATION_EXPIRE_INTERVAL'); // clear environment
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        putenv('AUTHORIZATION_EXPIRE_INTERVAL'); // clear environment
    }

    public function testEmptyExpireInterval(): void
    {
        $this->expectException(\Horat1us\Environment\Exception\Missing::class);
        $this->expectExceptionMessage('Missing environment key AUTHORIZATION_EXPIRE_INTERVAL');
        $this->config->getExpireInterval(0);
    }

    public function testExpireInterval(): void
    {
        putenv('AUTHORIZATION_EXPIRE_INTERVAL=1');
        $expireInterval = $this->config->getExpireInterval(0);
        $this->assertEquals(
            new \DateInterval('PT1S'),
            $expireInterval
        );
    }
}
