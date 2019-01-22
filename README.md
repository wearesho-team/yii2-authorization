# Yii2 Authorization
[![Build Status](https://travis-ci.org/wearesho-team/yii2-authorization.svg?branch=master)](https://travis-ci.org/wearesho-team/yii2-authorization)
[![codecov](https://codecov.io/gh/wearesho-team/yii2-authorization/branch/master/graph/badge.svg)](https://codecov.io/gh/wearesho-team/yii2-authorization)

Authorizing users using redis tokens for Yii2

## Installation

```bash
composer require wearesho-team/yii2-authorization:^1.0.0
```

## Usage

### Token Entity
To operate with access and refresh token pair you should use [Token](./src/Token.php) entity.
```php
<?php

use Wearesho\Yii2\Authorization;

$token = new Authorization\Token("accessValue", "refreshValue");
$token->getAccess(); // accessValue
$token->getRefresh(); // refreshValue
```

### Configuration
For configuration you have to use [ConfigInterface](./src/ConfigInterface.php).
Few implementations available out-of-box:

#### Config
Simple Yii2 base object: [Config](./src/Config.php)

```php
<?php

use Wearesho\Yii2\Authorization;

$config = new Authorization\Config([
    'expireInterval' => 'PT1M', // as \DateInterval value format
]);

$config = new Authorization\Config([
    'expireInterval' => new \DateInterval("PT1M"), // as \DateInterval instance
]);

$config = new Authorization\Config([
    'expireInterval' => function(): \DateInterval {
        return new \DateInterval("PT1M");
    }, // as \Closure that returns \DateInterval
]);
```

#### Environment configuration
To use environment to configure authorization you should use [EnvironmentConfig](./src / EnvironmentConfig . php).  
Environment keys(with default prefix):
- **AUTHORIZATION_EXPIRE_INTERVAL ** -(default: null), seconds before tokens will be expired

```php
<?php

use Wearesho\Yii2\Authorization;

$config = new Authorization\EnvironmentConfig();
$config->getExpireInterval(0); // AUTHORIZATION_EXPIRE_INTERVAL will be loaded from environment

```

### Repository
To store tokens you should use [Repository](./src/Repository.php).
It will store tokens in specified redis connection.

```php
<?php

use yii\redis;
use Wearesho\Yii2\Authorization;
use Ramsey\Uuid\UuidFactoryInterface;

$repository = new Authorization\Repository([
    'config' => Authorization\ConfigInterface::class,
    'redis' => redis\Connection::class, // your connection
    'factory' => UuidFactoryInterface::class, // some implementation 
]);

$userId = 1;

// Creating new token pair
$token = $repository->create($userId); // Token entity

// Getting user ID using access token
$repository->get($token->getAccess()); // will return 1

// Removing token pair
$userId = $repository->delete($token->getRefresh());

// Then you can create new token pair (for refreshing)
$newToken = $repository->create($userId);
```

### HasToken
To implement part of yii`s web\Identity interface you should use
[HasToken](./src/HasToken.php) trait, which implement findIdentityByAccessToken
method and will allow to use something like HttpBearerAuth behaviors.

```php
<?php

use Wearesho\Yii2\Authorization;
use yii\web;

class User implements web\IdentityInterface
{
    use Authorization\HasToken;
    
    // then, implement other interface methods
}
```

## License
[MIT](./LICENSE)
