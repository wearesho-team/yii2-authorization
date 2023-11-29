<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use yii\di;
use yii\base;
use yii\redis;

class Repository extends base\BaseObject
{
    /** @var string|array|redis\Connection */
    public $redis = 'redis';

    /** @var string|array|UuidFactoryInterface */
    public $factory = UuidFactory::class;

    /** @var array|string|ConfigInterface */
    public $config = ConfigInterface::class;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->redis = di\Instance::ensure($this->redis, redis\Connection::class);
        $this->config = di\Instance::ensure($this->config, ConfigInterface::class);
        $this->factory = di\Instance::ensure($this->factory, UuidFactoryInterface::class);
    }

    /**
     * @param string $access have to be valid UUID
     * @return int user identifier
     */
    public function get(string $access): ?int
    {
        if (!$this->validate($access)) {
            return null;
        }

        $key = $this->getAccessKey($access);
        /** @var string|null $value */
        $value = $this->redis->get($key);
        if (is_null($value)) {
            return $value;
        }

        return (int)$value;
    }

    public function ttl(string $access): ?int
    {
        if (!$this->validate($access)) {
            return null;
        }

        $key = $this->getAccessKey($access);
        $ttl = $this->redis->ttl($key);
        if (is_null($ttl)) {
            return $ttl;
        }

        return (int)$ttl;
    }

    /**
     * @param string $refresh
     * @return int|null user id if deleted or null if refresh token is invalid
     */
    public function delete(string $refresh): ?int
    {
        if (!$this->validate($refresh)) {
            return null;
        }

        $refreshKey = $this->getRefreshKey($refresh);
        $access = $this->redis->get($refreshKey);
        if (is_null($access)) {
            return null;
        }

        $accessKey = $this->getAccessKey($access);
        /** @var string|null $userId */
        $userId = $this->redis->get($accessKey);

        $this->redis->del(
            $refreshKey,
            $accessKey
        );

        return (int)$userId ?: null;
    }

    public function create(int $userId): Token
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $token = new Token(
            (string)$this->factory->uuid4(),
            (string)$this->factory->uuid4()
        );
        $expire = Carbon::now()->add($this->config->getExpireInterval($userId))
            ->diffInSeconds();

        $this->redis->multi();

        $this->redis->setex(
            $accessKey = $this->getAccessKey($token->getAccess()),
            $expire,
            $userId
        );
        $this->redis->setex(
            $refreshKey = $this->getRefreshKey($token->getRefresh()),
            $expire,
            $token->getAccess()
        );

        $this->redis->exec();

        return $token;
    }

    protected function getAccessKey(string $access): string
    {
        return "access-{$access}";
    }

    protected function getRefreshKey(string $refresh): string
    {
        return "refresh-{$refresh}";
    }

    protected function validate(string $value): bool
    {
        return Uuid::isValid($value);
    }
}
