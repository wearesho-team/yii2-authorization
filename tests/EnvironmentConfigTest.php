<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Tests;

use PHPUnit\Framework\TestCase;
use Wearesho\Yii2\Authorization;

/**
 * Class EnvironmentConfigTest
 * @package Wearesho\Yii2\Authorization\Tests
 */
class EnvironmentConfigTest extends TestCase
{
    /** @var Authorization\EnvironmentConfig */
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new Authorization\EnvironmentConfig;
        putenv('AUTHORIZATION_EXPIRE_INTERVAL'); // clear environment
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        putenv('AUTHORIZATION_EXPIRE_INTERVAL'); // clear environment
    }

    /**
     * @expectedException \Horat1us\Environment\Exception\Missing
     * @expectedExceptionMessage Missing environment key AUTHORIZATION_EXPIRE_INTERVAL
     */
    public function testEmptyExpireInterval(): void
    {
        $this->config->getExpireInterval(0);
    }

    public function testExpireInterval(): void
    {
        putenv('AUTHORIZATION_EXPIRE_INTERVAL=1');
        $expireInterval = $this->config->getExpireInterval(0);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            new \DateInterval('PT1S'),
            $expireInterval
        );
    }
}
