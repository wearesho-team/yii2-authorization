<?php

namespace Wearesho\Yii2\Authorization\Tests;

use yii\di;
use yii\base;
use Wearesho\Yii2\Authorization;
use PHPUnit\Framework\TestCase;

/**
 * Class BootstrapTest
 * @package Wearesho\Yii2\Authorization\Tests
 */
class BootstrapTest extends TestCase
{
    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Config definition must be set.
     */
    public function testMissingConfigDefinition(): void
    {
        new Authorization\Bootstrap;
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
