# Yii2 Authorization
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

## License
[MIT](./LICENSE)
