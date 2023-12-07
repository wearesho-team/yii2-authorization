<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Repository;

use Carbon\Carbon;

/**
 * Saving refresh token is placed in a separate interface to allow storing in other databases,
 * to allow saving refresh tokens in cases when redis deleted data (for example, in case of reboot).
 */
interface RefreshTokenStorage
{
    public function push(string $key, string $value, Carbon $expireAt): void;

    public function pull(string $key): ?string;

    public function delete(string $key): void;

    public function clean(): void;
}
