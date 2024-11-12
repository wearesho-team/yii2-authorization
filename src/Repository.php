<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization;

use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use yii\redis;
use yii\base;
use yii\di;

class Repository extends base\BaseObject
{
    /** @var string|array|redis\Connection */
    public $redis = 'redis';

    /** @var string|array|UuidFactoryInterface */
    public $factory = UuidFactory::class;

    /** @var array|string|ConfigInterface */
    public $config = ConfigInterface::class;

    /** @var array|string|Repository\RefreshTokenValueEncoder */
    public $refreshEncoder = Repository\RefreshTokenValueEncoder::class;

    /** @var array|string|Repository\RefreshTokenStorage */
    public $refreshStorage = Repository\RefreshTokenStorage::class;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->redis = di\Instance::ensure($this->redis, redis\Connection::class);
        $this->config = di\Instance::ensure($this->config, ConfigInterface::class);
        $this->factory = di\Instance::ensure($this->factory, UuidFactoryInterface::class);
        $this->refreshEncoder = di\Instance::ensure(
            $this->refreshEncoder,
            Repository\RefreshTokenValueEncoder::class
        );
        $this->refreshStorage = di\Instance::ensure(
            $this->refreshStorage,
            Repository\RefreshTokenStorage::class
        );
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
        $refreshTokenValueEncoded = $this->refreshStorage->pull($refreshKey);
        if (is_null($refreshTokenValueEncoded)) {
            return null;
        }
        $refreshTokenValue = $this->refreshEncoder->decode($refreshTokenValueEncoded);
        if (is_null($refreshTokenValue)) {
            $this->refreshStorage->delete($refreshKey);
            return null;
        }

        $accessKey = $this->getAccessKey($refreshTokenValue->getAccessToken());

        $this->redis->multi();

        $this->redis->del(
            $accessKey
        );

        $this->refreshStorage->delete($refreshKey);

        $this->redis->exec();

        return $refreshTokenValue->getUserId();
    }

    public function create(int $userId): Token
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $token = new Token(
            (string)$this->factory->uuid4(),
            (string)$this->factory->uuid4()
        );
        $expireAccess = (int)Carbon::now()->add($this->config->getExpireInterval($userId))
            ->diffInSeconds();
        $expireRefresh = Carbon::now()->add($this->config->getRefreshExpireInterval($userId));

        $this->redis->multi();

        $this->redis->setex(
            $this->getAccessKey($token->getAccess()),
            $expireAccess,
            $userId
        );

        $this->refreshStorage->push(
            $this->getRefreshKey($token->getRefresh()),
            $this->refreshEncoder->encode(
                new Repository\RefreshTokenValue($token->getAccess(), $userId)
            ),
            $expireRefresh
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
