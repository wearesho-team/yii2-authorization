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

## License
[MIT](./LICENSE)
