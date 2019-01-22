<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Tests;

use PHPUnit\Framework\TestCase;
use Wearesho\Yii2\Authorization;

/**
 * Class ConfigTest
 * @package Wearesho\Yii2\Authorization\Tests
 */
class ConfigTest extends TestCase
{
    public function testExpireIntervalAsString(): void
    {
        $config = new Authorization\Config([
            'expireInterval' => 'PT1M',
        ]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            new \DateInterval("PT1M"),
            $config->getExpireInterval(0)
        );
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionCode 1
     */
    public function testExpireIntervalAsInvalidString(): void
    {
        $invalidString = 'P30M2';
        $this->expectExceptionMessage(
            "Invalid expireInterval format: DateInterval::__construct(): Unknown or bad format (P30M2)"
        );
        new Authorization\Config([
            'expireInterval' => $invalidString,
        ]);
    }

    public function testExpireIntervalAsInstance(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $interval = new \DateInterval("PT1M");

        $config = new Authorization\Config([
            'expireInterval' => $interval,
        ]);

        $this->assertEquals($interval, $config->getExpireInterval(0));
    }

    public function testExpireIntervalAsClosure(): void
    {
        $getInterval = function (): \DateInterval {
            return new \DateInterval("PT30M");
        };

        $config = new Authorization\Config([
            'expireInterval' => $getInterval,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            new \DateInterval("PT30M"),
            $config->getExpireInterval(0)
        );
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionCode 2
     */
    public function testExpireIntervalAsInvalidClosure(): void
    {
        $invalidClosure = function (): \stdClass {
            return new \stdClass();
        };

        $this->expectExceptionMessage(
            "Invalid expireInterval format: must be \DateInterval or \Closure that returns \DateInterval"
        );

        new Authorization\Config([
            'expireInterval' => $invalidClosure,
        ]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionCode 2
     */
    public function testExpireIntervalAsInvalidObject(): void
    {
        $invalidObject = new \stdClass();

        $this->expectExceptionMessage(
            "Invalid expireInterval format: must be \DateInterval or \Closure that returns \DateInterval"
        );

        new Authorization\Config([
            'expireInterval' => $invalidObject,
        ]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage expireInterval must be set
     * @expectedExceptionCode 0
     */
    public function testEmptyExpireInterval(): void
    {
        new Authorization\Config;
    }
}
