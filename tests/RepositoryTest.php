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

        $redis
            ->expects($this->at(0))
            ->method('__call')
            ->with(
                $this->equalTo('get'),
                $this->equalTo(["refresh-{$refreshToken}"])
            )
            ->willReturn($accessToken);

        $redis
            ->expects($this->at(1))
            ->method('__call')
            ->with(
                $this->equalTo('get'),
                $this->equalTo(["access-{$accessToken}"])
            )
            ->willReturn(null);


        $redis
            ->expects($this->at(2))
            ->method('__call')
            ->with(
                $this->equalTo('del'),
                $this->equalTo(["refresh-{$refreshToken}", "access-{$accessToken}"])
            )
            ->willReturn(1);

        $this->assertNull(
            $repository->delete((string)$refreshToken)
        );
    }

    public function testDeleteBothAccessAndRefresj(): void
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

        $redis
            ->expects($this->at(0))
            ->method('__call')
            ->with(
                $this->equalTo('get'),
                $this->equalTo(["refresh-{$refreshToken}"])
            )
            ->willReturn($accessToken);

        $redis
            ->expects($this->at(1))
            ->method('__call')
            ->with(
                $this->equalTo('get'),
                $this->equalTo(["access-{$accessToken}"])
            )
            ->willReturn($userId = 1);


        $redis
            ->expects($this->at(2))
            ->method('__call')
            ->with(
                $this->equalTo('del'),
                $this->equalTo(["refresh-{$refreshToken}", "access-{$accessToken}"])
            )
            ->willReturn(1);

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
        $access = Uuid::uuid4();
        /** @noinspection PhpUnhandledExceptionInspection */
        $refresh = Uuid::uuid4();

        $userId = 1;

        /** @noinspection PhpUnhandledExceptionInspection */
        $config
            ->expects($this->once())
            ->method('getExpireInterval')
            ->with($userId)
            ->willReturn(new \DateInterval('PT30S'));

        $factory
            ->expects($this->at(0))
            ->method('uuid4')
            ->willReturn($access);
        $factory
            ->expects($this->at(1))
            ->method('uuid4')
            ->willReturn($refresh);

        $redis
            ->expects($this->at(0))
            ->method('__call')
            ->with(
                $this->equalTo('multi'),
                $this->equalTo([])
            );

        $redis
            ->expects($this->at(1))
            ->method('__call')
            ->with(
                $this->equalTo('set'),
                $this->equalTo(["access-{$access}", $userId])
            );
        $redis
            ->expects($this->at(2))
            ->method('__call')
            ->with(
                $this->equalTo('set'),
                $this->equalTo(["refresh-{$refresh}", $access])
            );

        $redis
            ->expects($this->at(3))
            ->method('__call')
            ->with(
                $this->equalTo('expire'),
                $this->equalTo(["access-{$access}", 30])
            );
        $redis
            ->expects($this->at(4))
            ->method('__call')
            ->with(
                $this->equalTo('expire'),
                $this->equalTo(["refresh-{$refresh}", 30])
            );

        $redis
            ->expects($this->at(5))
            ->method('__call')
            ->with(
                $this->equalTo('exec'),
                $this->equalTo([])
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
