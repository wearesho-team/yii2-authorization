<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization;

use yii\base;

/**
 * Class Config
 * @package Wearesho\Yii2\Authorization
 */
class Config extends base\BaseObject implements ConfigInterface
{
    /** @var \DateInterval|\Closure|string */
    public $expireInterval;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        if (empty($this->expireInterval)) {
            throw new base\InvalidConfigException("expireInterval must be set", 0);
        }

        if (is_string($this->expireInterval)) {
            try {
                $this->expireInterval = new \DateInterval($this->expireInterval);
            } catch (\Exception $exception) {
                throw new base\InvalidConfigException(
                    "Invalid expireInterval format: {$exception->getMessage()}",
                    1,
                    $exception
                );
            }
            return;
        }

        if ($this->expireInterval instanceof \Closure
            || is_array($this->expireInterval) && is_callable($this->expireInterval)
        ) {
            $this->expireInterval = call_user_func($this->expireInterval);
        }

        if (!$this->expireInterval instanceof \DateInterval) {
            throw new base\InvalidConfigException(
                "Invalid expireInterval format: must be \DateInterval or \Closure that returns \DateInterval",
                2
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getExpireInterval(int $user): \DateInterval
    {
        return $this->expireInterval;
    }
}
