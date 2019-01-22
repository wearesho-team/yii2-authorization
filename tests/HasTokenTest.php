<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Tests;

use yii\di;
use Wearesho\Yii2\Authorization;
use PHPUnit\Framework\TestCase;

/**
 * Class HasTokenTest
 * @package Wearesho\Yii2\Authorization\Tests
 */
class HasTokenTest extends TestCase
{
    public function testReturningAuthorizationRepositoryFromContainer(): void
    {
        $container = $this->createMock(di\Container::class);
        \Yii::$container = $container;

        $container
            ->expects($this->once())
            ->method('get')
            ->with(Authorization\Repository::class)
            ->willReturn($repository = $this->createMock(Authorization\Repository::class));

        $user = new class()
        {
            use Authorization\HasToken;
        };

        $this->assertEquals(
            $repository,
            $user::getAuthorizationRepository()
        );
        \Yii::$container = new di\Container;
    }

    public function testFindingIdentityByWrongToken(): void
    {
        $container = $this->createMock(di\Container::class);
        \Yii::$container = $container;

        $container
            ->expects($this->once())
            ->method('get')
            ->with(Authorization\Repository::class)
            ->willReturn($repository = $this->createMock(Authorization\Repository::class));

        $user = new class
        {
            use Authorization\HasToken;

            public static function findIdentity(int $id): ?self
            {
                return new self;
            }
        };

        $token = 'invalidAccessToken';

        $repository
            ->expects($this->once())
            ->method('get')
            ->with($token)
            ->willReturn(null);

        $this->assertNull($user->findIdentityByAccessToken($token));
    }

    public function testFindingIdentityByValidToken(): void
    {
        $container = $this->createMock(di\Container::class);
        \Yii::$container = $container;

        $container
            ->expects($this->once())
            ->method('get')
            ->with(Authorization\Repository::class)
            ->willReturn($repository = $this->createMock(Authorization\Repository::class));

        $user = new class
        {
            use Authorization\HasToken;

            public static function findIdentity(int $id): ?self
            {
                return $id === 1 ? new self : null;
            }
        };

        $token = 'tokenForUserOne';

        $repository
            ->expects($this->once())
            ->method('get')
            ->with($token)
            ->willReturn(1);

        $this->assertInstanceOf(
            get_class($user),
            $user->findIdentityByAccessToken($token)
        );
    }
}
