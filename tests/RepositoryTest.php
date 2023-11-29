<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Tests;

use Carbon\Carbon;
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
            'redis' => $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory,
        ]);
        $id = $repository->get('notUuid');
        $this->assertNull($id);
    }

    public function testGetWithValueInRedis(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory,
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
            'factory' => new UuidFactory,
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
            'factory' => new UuidFactory,
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
            'factory' => new UuidFactory,
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

    public function testDeleteWithInvalidToken(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory,
        ]);
        $invalidToken = 'notUuid';

        $this->assertNull($repository->delete($invalidToken));
    }

    public function testDeleteNotSavedToken(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $this->createMock(Authorization\Config::class),
            'factory' => new UuidFactory,
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
            'factory' => new UuidFactory,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $refreshToken = (string)Uuid::uuid4();
        /** @noinspection PhpUnhandledExceptionInspection */
        $accessToken = (string)Uuid::uuid4();


        $redis->expects($this->exactly(3))
            ->method('__call')
            ->willReturnCallback(function (string $method, array $keys) use ($accessToken, $refreshToken) {
                $this->assertContains($method, ['get', 'del']);
                switch ($method) {
                    case 'get':
                        $this->assertCount(1, $keys);
                        $key = array_shift($keys);
                        switch ($key) {
                            case "refresh-{$refreshToken}":
                                return $accessToken;
                            case "access-{$accessToken}":
                                return null;
                            default:
                                $this->fail("Unexpected key: {$key} for method {$method} (Redis Mock)");
                        }
                    case "del":
                        $this->assertEquals($keys, ["refresh-{$refreshToken}", "access-{$accessToken}"]);
                        return 1;
                    default:
                        $this->fail("Unexpected method: {$method} (Redis Mock)");
                }
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
            'factory' => new UuidFactory,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $refreshToken = (string)Uuid::uuid4();
        /** @noinspection PhpUnhandledExceptionInspection */
        $accessToken = (string)Uuid::uuid4();
        $userId = 1337;

        $redis->expects($this->exactly(3))
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $keys) use ($accessToken, $refreshToken, $userId) {
                    $this->assertContains($method, ['get', 'del']);
                    switch ($method) {
                        case 'get':
                            $this->assertCount(1, $keys);
                            $key = array_shift($keys);
                            switch ($key) {
                                case "refresh-{$refreshToken}":
                                    return $accessToken;
                                case "access-{$accessToken}":
                                    return $userId;
                                default:
                                    $this->fail("Unexpected key: {$key} for method {$method} (Redis Mock)");
                            }
                        case "del":
                            $this->assertEquals($keys, ["refresh-{$refreshToken}", "access-{$accessToken}"]);
                            return $userId;
                        default:
                            $this->fail("Unexpected method: {$method} (Redis Mock)");
                    }
                }
            );

        $this->assertEquals(
            $userId,
            $repository->delete((string)$refreshToken)
        );
    }

    public function testCreatingValidUuidTokenPaid(): void
    {
        $repository = new Authorization\Repository([
            'redis' => $redis = $this->createMock(redis\Connection::class),
            'config' => $config = $this->createMock(Authorization\Config::class),
            'factory' => $factory = $this->createMock(UuidFactory::class),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $access = Uuid::uuid4()->toString();
        /** @noinspection PhpUnhandledExceptionInspection */
        $refresh = Uuid::uuid4()->toString();

        $userId = 13371;
        $expireInterval = 30;

        /** @noinspection PhpUnhandledExceptionInspection */
        $config
            ->expects($this->once())
            ->method('getExpireInterval')
            ->with($userId)
            ->willReturn(new \DateInterval('PT' . $expireInterval . 'S'));

        $factory
            ->expects($this->exactly(2))
            ->method('uuid4')
            ->willReturn($access, $refresh);

        $redis->expects($this->exactly(4))
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $keys) use ($access, $refresh, $expireInterval, $userId) {
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
                                    $this->assertEquals($expireInterval, $expire);
                                    $this->assertEquals($access, $value);
                                    return null;
                                case "access-{$access}":
                                    $this->assertEquals($expireInterval, $expire);
                                    $this->assertEquals($userId, $value);
                                    return null;
                                default:
                                    $this->fail("Unexpected key: {$key} for method {$method} (Redis Mock)");
                            }
                        default:
                            $this->fail("Unexpected method: {$method} (Redis Mock)");
                    }
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
