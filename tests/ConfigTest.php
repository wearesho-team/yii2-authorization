<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Tests;

use PHPUnit\Framework\TestCase;
use Wearesho\Yii2\Authorization;
use yii\base;

class ConfigTest extends TestCase
{
    public function testExpireIntervalAsString(): void
    {
        $config = new Authorization\Config([
            'expireInterval' => 'PT1M',
        ]);
        $this->assertEquals(
            new \DateInterval("PT1M"),
            $config->getExpireInterval(0)
        );
    }

    public function testExpireIntervalAsInvalidString(): void
    {
        $invalidString = 'P30M2';
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            "Invalid expireInterval format: Unknown or bad format (P30M2)"
        );
        new Authorization\Config([
            'expireInterval' => $invalidString,
        ]);
    }

    public function testExpireIntervalAsInstance(): void
    {
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

        $this->assertEquals(
            new \DateInterval("PT30M"),
            $config->getExpireInterval(0)
        );
    }

    public function testExpireIntervalAsInvalidClosure(): void
    {
        $invalidClosure = function (): \stdClass {
            return new \stdClass();
        };

        $this->expectExceptionMessage(
            "Invalid expireInterval format: must be \DateInterval or \Closure that returns \DateInterval"
        );
        $this->expectExceptionCode(2);
        $this->expectException(base\InvalidConfigException::class);
        new Authorization\Config([
            'expireInterval' => $invalidClosure,
        ]);
    }

    public function testExpireIntervalAsInvalidObject(): void
    {
        $invalidObject = new \stdClass();

        $this->expectExceptionMessage(
            "Invalid expireInterval format: must be \DateInterval or \Closure that returns \DateInterval"
        );
        $this->expectExceptionCode(2);
        $this->expectException(base\InvalidConfigException::class);

        new Authorization\Config([
            'expireInterval' => $invalidObject,
        ]);
    }

    public function testEmptyExpireInterval(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage(
            "expireInterval must be set"
        );
        $this->expectExceptionCode(0);
        new Authorization\Config();
    }
}
