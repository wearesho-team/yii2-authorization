<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Tests;

use yii\di;
use yii\base;
use Wearesho\Yii2\Authorization;
use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testMissingConfigDefinition(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Config definition must be set.');
        new Authorization\Bootstrap();
    }

    public function testMissingRefreshTokenStorageDefinition(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('RefreshTokenStorage definition must be set.');
        new Authorization\Bootstrap([
            'config' => [
                'class' => Authorization\EnvironmentConfig::class,
            ],
            'refreshTokenStorage' => null,
        ]);
    }

    public function testSettingDefinitionOnContainer(): void
    {
        $container = $this->createMock(di\Container::class);
        \Yii::$container = $container;

        $configDefinition = $this->createMock(Authorization\Config::class);
        $refreshTokenStorageDefinition = [
            'class' => Authorization\Repository\RefreshTokenStorageRedis::class,
            'redis' => 'redis2',
        ];

        $container
            ->expects($this->exactly(2))
            ->method('set')
            ->willReturn(
                function (
                    string $class,
                    $definition
                ) use (
                    $configDefinition,
                    $refreshTokenStorageDefinition
                ): void {
                    switch ($class) {
                        case Authorization\ConfigInterface::class:
                            $this->assertEquals($definition, $configDefinition);
                            break;
                        case Authorization\Repository\RefreshTokenStorage::class:
                            $this->assertEquals($definition, $refreshTokenStorageDefinition);
                            break;
                        default:
                            $this->fail("Unexpected class configured: " . $class);
                    }
                },
            );

        $bootstrap = new Authorization\Bootstrap([
            'config' => $configDefinition,
            'refreshTokenStorage' => $refreshTokenStorageDefinition,
        ]);
        $bootstrap->bootstrap($this->createMock(base\Application::class));
    }
}
