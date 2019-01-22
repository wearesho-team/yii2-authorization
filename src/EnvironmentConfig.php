<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization;

use Horat1us\Environment;

/**
 * Class EnvironmentConfig
 * @package Wearesho\Yii2\Authorization
 */
class EnvironmentConfig extends Environment\Yii2\Config implements ConfigInterface
{
    public $keyPrefix = 'AUTHORIZATION_';

    public function getExpireInterval(int $user): \DateInterval
    {
        $expireSeconds = $this->getExpireSeconds($user);
        /** @noinspection PhpUnhandledExceptionInspection */
        return new \DateInterval("PT{$expireSeconds}S");
    }

    /**
     * @param int $user may be used in child classes to use different environment keys for different users
     * @return int
     */
    protected function getExpireSeconds(int $user): int
    {
        return (int)$this->getEnv('EXPIRE_INTERVAL');
    }
}
