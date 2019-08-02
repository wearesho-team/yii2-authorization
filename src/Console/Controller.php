<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Console;

use Wearesho\Yii2\Authorization;
use Carbon\Carbon;
use yii\helpers;
use yii\console;
use yii\base;
use yii\di;

/**
 * Class Controller
 * @package Wearesho\Yii2\Authorization\Console
 */
class Controller extends console\Controller
{
    /** @var string|array|Authorization\Repository */
    public $repository = Authorization\Repository::class;

    /**
     * @param base\Action $action
     * @return bool
     * @throws base\InvalidConfigException
     */
    public function beforeAction($action): bool
    {
        $beforeAction = parent::beforeAction($action);
        if ($beforeAction) {
            $this->repository = di\Instance::ensure(
                $this->repository,
                Authorization\Repository::class
            );
        }
        return $beforeAction;
    }

    /**
     * Creates new access-refresh token pair
     * @param int $userId
     */
    public function actionCreate(int $userId): void
    {
        $token = $this->repository->create($userId);

        $this->stdout("UserID = {$userId}" . PHP_EOL);
        $this->stdout("Access Token = {$token->getAccess()}" . PHP_EOL, helpers\Console::FG_GREEN);
        $this->stdout("Refresh Token = {$token->getRefresh()}" . PHP_EOL, helpers\Console::FG_YELLOW);

        $this->actionTtl($token->getAccess());
    }

    /**
     * Displays TTL (seconds) and expire date for access token
     * @param string $access
     */
    public function actionTtl(string $access): void
    {
        $ttl = $this->repository->ttl($access);

        if (is_null($ttl)) {
            $this->stdout("Not Found" . PHP_EOL, helpers\Console::FG_RED);
            return;
        }

        $expire = Carbon::now()->addSeconds($ttl)->toDateTimeString();

        $this->stdout("TTL = {$ttl}s ");
        $this->stdout("(expire {$expire})" . PHP_EOL, helpers\Console::FG_GREY);
    }

    /**
     * Deletes access-refresh token pair
     * @param string $refresh
     */
    public function actionDelete(string $refresh): void
    {
        $userId = $this->repository->delete($refresh);
        if (is_null($userId)) {
            $this->stdout("Not Found" . PHP_EOL, helpers\Console::FG_RED);
            return;
        }

        $this->stdout("Token successfully deleted." . PHP_EOL, helpers\Console::FG_GREEN);
        $this->stdout("User ID = {$userId}" . PHP_EOL);
    }
}
