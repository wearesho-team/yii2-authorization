<?php

namespace Wearesho\Yii2\Authorization\Repository;

use Carbon\Carbon;
use yii\redis;
use yii\base;
use yii\di;

class RefreshTokenStorageRedis extends base\BaseObject implements RefreshTokenStorage
{
    /** @var string|array|redis\Connection */
    public $redis = 'redis';

    public function init(): void
    {
        parent::init();
        $this->redis = di\Instance::ensure($this->redis, redis\Connection::class);
    }

    public function push(string $key, string $value, Carbon $expireAt): void
    {
        $expireRefresh = $expireAt->diffInSeconds();
        $this->redis->setex(
            $key,
            $expireRefresh,
            $value
        );
    }

    public function pull(string $key): ?string
    {
        return $this->redis->get($key);
    }

    public function delete(string $key): void
    {
        $this->redis->del($key);
    }

    public function clean(): void
    {
        // redis automatically expiring refresh tokens
        return;
    }
}
