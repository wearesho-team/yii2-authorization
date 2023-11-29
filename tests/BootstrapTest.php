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

    public function testSettingDefinitionOnContainer(): void
    {
        $container = $this->createMock(di\Container::class);
        \Yii::$container = $container;

        $configDefinition = $this->createMock(Authorization\Config::class);

        $container
            ->expects($this->once())
            ->method('set')
            ->with(
                Authorization\ConfigInterface::class,
                $configDefinition
            );

        $bootstrap = new Authorization\Bootstrap([
            'config' => $configDefinition,
        ]);
        $bootstrap->bootstrap($this->createMock(base\Application::class));
    }
}
