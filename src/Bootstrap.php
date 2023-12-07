<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization;

use yii\base;

class Bootstrap extends base\BaseObject implements base\BootstrapInterface
{
    /** @var string|array|ConfigInterface */
    public $config;

    /** @var string|array|Repository\RefreshTokenStorage */
    public $refreshTokenStorage = [
        'class' => Repository\RefreshTokenStorageRedis::class,
    ];

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        if (is_null($this->config)) {
            throw new base\InvalidConfigException("Config definition must be set.");
        }
        if (is_null($this->refreshTokenStorage)) {
            throw new base\InvalidConfigException("RefreshTokenStorage definition must be set.");
        }
    }

    public function bootstrap($app): void
    {
        \Yii::$container->set(
            ConfigInterface::class,
            $this->config
        );
        \Yii::$container->set(
            Repository\RefreshTokenStorage::class,
            $this->refreshTokenStorage
        );
    }
}
