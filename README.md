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

## License
[MIT](./LICENSE)
