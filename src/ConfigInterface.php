<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization;

interface ConfigInterface
{
    /**
     * How long user access token will be active for specified user
     *
     * @param int $user
     * @return \DateInterval
     */
    public function getExpireInterval(int $user): \DateInterval;

    public function getRefreshExpireInterval(int $user): \DateInterval;
}
