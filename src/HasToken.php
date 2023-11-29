<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization;

use yii\web;
use yii\base;
use yii\di;

/**
 * Trait HasToken
 * @package Wearesho\Yii2\Authorization
 *
 * Should be used on entity that implements IdentityInterface
 *
 * @see web\IdentityInterface
 *
 * @method static findIdentity(int $id)
 */
trait HasToken
{
    /**
     * May be overridden to use different repositories for different users
     *
     * @return Repository
     * @throws base\InvalidConfigException
     * @throws di\NotInstantiableException
     */
    public static function getAuthorizationRepository(): Repository
    {
        /** @var Repository $repository */
        $repository = \Yii::$container->get(Repository::class);
        return $repository;
    }

    /**
     * @inheritdoc
     * @see web\IdentityInterface::findIdentityByAccessToken()
     *
     * @throws base\InvalidConfigException
     * @throws di\NotInstantiableException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $userId = static::getAuthorizationRepository()->get($token);
        if (\is_null($userId)) {
            return null;
        }

        return static::findIdentity($userId);
    }
}
