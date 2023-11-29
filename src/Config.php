<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization;

use yii\base;

class Config extends base\BaseObject implements ConfigInterface
{
    /** @var \DateInterval|\Closure|string */
    public $expireInterval;

    /** @var \DateInterval|\Closure|string */
    public $refreshExpireInterval;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        if (empty($this->expireInterval)) {
            throw new base\InvalidConfigException("expireInterval must be set", 0);
        }
        if (empty($this->refreshExpireInterval)) {
            throw new base\InvalidConfigException("refreshExpireInterval must be set", 1);
        }

        $this->validateExpireInterval($this->expireInterval);
        $this->validateExpireInterval($this->refreshExpireInterval);
    }

    public function getExpireInterval(int $user): \DateInterval
    {
        return $this->expireInterval;
    }

    public function getRefreshExpireInterval(int $user): \DateInterval
    {
        return $this->refreshExpireInterval;
    }

    private function validateExpireInterval(&$expireInterval): void
    {
        if (is_string($expireInterval)) {
            try {
                $expireInterval = new \DateInterval($expireInterval);
            } catch (\Exception $exception) {
                throw new base\InvalidConfigException(
                    "Invalid expireInterval format: {$exception->getMessage()}, {$expireInterval} given.",
                    1,
                    $exception
                );
            }
            return;
        }

        if (
            $expireInterval instanceof \Closure
            || is_array($expireInterval) && is_callable($expireInterval)
        ) {
            $expireInterval = call_user_func($expireInterval);
        }

        if (!$expireInterval instanceof \DateInterval) {
            throw new base\InvalidConfigException(
                "Invalid expireInterval format: must be \DateInterval or \Closure that returns \DateInterval",
                2
            );
        }
    }
}
