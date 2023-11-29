<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization;

use yii\base;

class Bootstrap extends base\BaseObject implements base\BootstrapInterface
{
    /** @var string|array|ConfigInterface */
    public $config;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        if (is_null($this->config)) {
            throw new base\InvalidConfigException("Config definition must be set.");
        }
    }

    public function bootstrap($app): void
    {
        \Yii::$container->set(
            ConfigInterface::class,
            $this->config
        );
    }
}
