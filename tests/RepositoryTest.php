<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Tests;

use Carbon\Carbon;
use Ramsey\Uuid\FeatureSet;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use yii\redis;
use PHPUnit\Framework\TestCase;
use Wearesho\Yii2\Authorization;

class RepositoryTest extends TestCase
{
    public function testGetWithInvalidUuidToken(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory(new FeatureSet()),
            'refreshEncoder' => $this->createMock(
                Authorization\Repository\RefreshTokenValueEncoder::class
            ),
            'refreshStorage' => new Authorization\Repository\RefreshTokenStorageRedis([
                'redis' => $redis,
            ])
        ]);
        $id = $repository->get('notUuid');
        $this->assertNull($id);
    }

    public function testGetWithValueInRedis(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory(new FeatureSet()),
            'refreshEncoder' => $this->createMock(
                Authorization\Repository\RefreshTokenValueEncoder::class
            ),
            'refreshStorage' => new Authorization\Repository\RefreshTokenStorageRedis([
                'redis' => $redis,
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $token = Uuid::uuid4();

        $redis
            ->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('get'),
                $this->equalTo(["access-{$token}"])
            )
            ->willReturn($id = 1);


        $this->assertEquals(
            $id,
            $repository->get((string)$token)
        );
    }

    public function testGetWithNoValueInRedis(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory(new FeatureSet()),
            'refreshEncoder' => $this->createMock(
                Authorization\Repository\RefreshTokenValueEncoder::class
            ),
            'refreshStorage' => new Authorization\Repository\RefreshTokenStorageRedis([
                'redis' => $redis,
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $token = Uuid::uuid4();

        $redis
            ->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('get'),
                $this->equalTo(["access-{$token}"])
            )
            ->willReturn(null);


        $this->assertNull(
            $repository->get((string)$token)
        );
    }

    public function testTtlWithValueInRedis(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory(new FeatureSet()),
            'refreshEncoder' => $this->createMock(
                Authorization\Repository\RefreshTokenValueEncoder::class
            ),
            'refreshStorage' => new Authorization\Repository\RefreshTokenStorageRedis([
                'redis' => $redis,
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $token = Uuid::uuid4();

        $redis
            ->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('ttl'),
                $this->equalTo(["access-{$token}"])
            )
            ->willReturn($id = 1);


        $this->assertEquals(
            $id,
            $repository->ttl((string)$token)
        );
    }

    public function testTtlWithNoValueInRedis(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory(new FeatureSet()),
            'refreshEncoder' => $this->createMock(
                Authorization\Repository\RefreshTokenValueEncoder::class
            ),
            'refreshStorage' => new Authorization\Repository\RefreshTokenStorageRedis([
                'redis' => $redis,
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $token = Uuid::uuid4();

        $redis
            ->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('ttl'),
                $this->equalTo(["access-{$token}"])
            )
            ->willReturn(null);


        $this->assertNull(
            $repository->ttl((string)$token)
        );
    }

    public function testTtlWithInvalidValue(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory(new FeatureSet()),
            'refreshEncoder' => $this->createMock(
                Authorization\Repository\RefreshTokenValueEncoder::class
            ),
            'refreshStorage' => new Authorization\Repository\RefreshTokenStorageRedis([
                'redis' => $redis,
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $token = "NOT_UUID";

        $redis
            ->expects($this->never())
            ->method('__call')
            ->with(
                $this->equalTo('ttl'),
                $this->equalTo(["access-{$token}"])
            );


        $this->assertNull(
            $repository->ttl((string)$token)
        );
    }

    public function testDeleteWithInvalidToken(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory(new FeatureSet()),
            'refreshEncoder' => $this->createMock(
                Authorization\Repository\RefreshTokenValueEncoder::class
            ),
            'refreshStorage' => new Authorization\Repository\RefreshTokenStorageRedis([
                'redis' => $redis,
            ]),
        ]);
        $invalidToken = 'notUuid';

        $this->assertNull($repository->delete($invalidToken));
    }

    public function testDeleteNotSavedToken(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory(new FeatureSet()),
            'refreshEncoder' => $this->createMock(
                Authorization\Repository\RefreshTokenValueEncoder::class
            ),
            'refreshStorage' => new Authorization\Repository\RefreshTokenStorageRedis([
                'redis' => $redis,
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $token = Uuid::uuid4();

        $redis
            ->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('get'),
                $this->equalTo(["refresh-{$token}"])
            )
            ->willReturn(null);


        $this->assertNull(
            $repository->delete((string)$token)
        );
    }

    public function testDeleteOnlyRefreshToken(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory(new FeatureSet()),
            'refreshEncoder' => $refreshEncoder = $this->createMock(
                Authorization\Repository\RefreshTokenValueEncoder::class
            ),
            'refreshStorage' => new Authorization\Repository\RefreshTokenStorageRedis([
                'redis' => $redis,
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $refreshToken = (string)Uuid::uuid4();
        /** @noinspection PhpUnhandledExceptionInspection */
        $accessToken = (string)Uuid::uuid4();

        $refreshEncoder
            ->expects($this->once())
            ->method('decode')
            ->with(sha1($accessToken))
            ->willReturn(null);

        $redis->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(function (string $method, array $keys) use ($accessToken, $refreshToken) {
                $this->assertContains($method, ['get', 'del']);
                switch ($method) {
                    case 'get':
                        $this->assertCount(1, $keys);
                        $key = array_shift($keys);
                        switch ($key) {
                            case "refresh-{$refreshToken}":
                                return sha1($accessToken);
                        }
                        return $this->fail("Unexpected key: {$key} for method {$method} (Redis Mock)");
                    case "del":
                        $this->assertEquals($keys, ["refresh-{$refreshToken}"]);
                        return 1;
                }
                $this->fail("Unexpected method: {$method} (Redis Mock)");
            });

        $this->assertNull(
            $repository->delete((string)$refreshToken)
        );
    }

    public function testDeleteBothAccessAndRefresh(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory(new FeatureSet()),
            'refreshEncoder' => $refreshEncoder = $this->createMock(
                Authorization\Repository\RefreshTokenValueEncoder::class
            ),
            'refreshStorage' => new Authorization\Repository\RefreshTokenStorageRedis([
                'redis' => $redis,
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $refreshToken = (string)Uuid::uuid4();
        /** @noinspection PhpUnhandledExceptionInspection */
        $accessToken = (string)Uuid::uuid4();
        $userId = 1337;

        $refreshEncoder
            ->expects($this->once())
            ->method('decode')
            ->with(sha1($accessToken))
            ->willReturn(new Authorization\Repository\RefreshTokenValue($accessToken, $userId));

        $redis->expects($this->exactly(5))
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $keys) use ($accessToken, $refreshToken, $userId) {
                    $this->assertContains($method, ['get', 'del', 'multi', 'exec',]);
                    switch ($method) {
                        case 'get':
                            $this->assertCount(1, $keys);
                            $key = array_shift($keys);
                            switch ($key) {
                                case "refresh-{$refreshToken}":
                                    return sha1($accessToken);
                                case "access-{$accessToken}":
                                    return $userId;
                            }
                            return $this->fail("Unexpected key: {$key} for method {$method} (Redis Mock)");
                        case "del":
                            $this->assertCount(1, $keys);
                            $this->assertContains($keys[0], ["refresh-{$refreshToken}", "access-{$accessToken}"]);
                            return $userId;
                        case 'multi':
                        case 'exec':
                            return null;
                    }
                    $this->fail("Unexpected method: {$method} (Redis Mock)");
                }
            );

        $this->assertEquals(
            $userId,
            $repository->delete((string)$refreshToken)
        );
    }

    public function testCreatingValidUuidTokenPair(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $config = $this->createMock(Authorization\Config::class),
            'factory' => $factory = $this->createMock(UuidFactory::class),
            'refreshEncoder' => $refreshEncoder = $this->createMock(
                Authorization\Repository\RefreshTokenValueEncoder::class
            ),
            'refreshStorage' => new Authorization\Repository\RefreshTokenStorageRedis([
                'redis' => $redis,
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $access = Uuid::uuid4()->toString();
        /** @noinspection PhpUnhandledExceptionInspection */
        $refresh = Uuid::uuid4()->toString();

        $userId = 13371;
        $expireInterval = 30;
        $refreshExpireInterval = 90;

        $refreshEncoder
            ->expects($this->once())
            ->method('encode')
            ->with(new Authorization\Repository\RefreshTokenValue($access, $userId))
            ->willReturn(sha1($access));

        $config
            ->expects($this->once())
            ->method('getExpireInterval')
            ->with($userId)
            ->willReturn(new \DateInterval('PT' . $expireInterval . 'S'));
        $config
            ->expects($this->once())
            ->method('getRefreshExpireInterval')
            ->with($userId)
            ->willReturn(new \DateInterval('PT' . $refreshExpireInterval . 'S'));

        $factory
            ->expects($this->exactly(2))
            ->method('uuid4')
            ->willReturn($access, $refresh);

        $redis->expects($this->exactly(4))
            ->method('__call')
            ->willReturnCallback(
                function (
                    string $method,
                    array $keys
                ) use (
                    $access,
                    $refresh,
                    $expireInterval,
                    $refreshExpireInterval,
                    $userId
                ) {
                    switch ($method) {
                        case "multi":
                        case "exec":
                            $this->assertEquals([], $keys);
                            return null;
                        case 'setex':
                            $this->assertCount(3, $keys);
                            [$key, $expire, $value] = $keys;
                            switch ($key) {
                                case "refresh-{$refresh}":
                                    $this->assertEquals($refreshExpireInterval, $expire);
                                    $this->assertEquals(sha1($access), $value);
                                    return null;
                                case "access-{$access}":
                                    $this->assertEquals($expireInterval, $expire);
                                    $this->assertEquals($userId, $value);
                                    return null;
                            }
                            return $this->fail("Unexpected key: {$key} for method {$method} (Redis Mock)");
                    }
                    return $this->fail("Unexpected method: {$method} (Redis Mock)");
                }
            );


        Carbon::setTestNow(Carbon::now()); // fix time to correct expire testing
        $token = $repository->create($userId);
        Carbon::setTestNow();

        $this->assertEquals(
            $access,
            $token->getAccess()
        );
        $this->assertEquals(
            $refresh,
            $token->getRefresh()
        );
    }
}
