<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization;

use Horat1us\Environment;

class EnvironmentConfig extends Environment\Yii2\Config implements ConfigInterface
{
    public $keyPrefix = 'AUTHORIZATION_';

    public function getExpireInterval(int $user): \DateInterval
    {
        $expireSeconds = $this->getExpireSeconds($user);
        return new \DateInterval("PT{$expireSeconds}S");
    }

    public function getRefreshExpireInterval(int $user): \DateInterval
    {
        $expireSeconds = $this->getRefreshExpireSeconds($user);
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

    protected function getRefreshExpireSeconds(int $user): int
    {
        return (int)$this->getEnv("REFRESH_EXPIRE_INTERVAL");
    }
}
