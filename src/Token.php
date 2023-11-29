<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization;

class Token
{
    /** @var string */
    protected $access;

    /** @var string */
    protected $refresh;

    public function __construct(string $access, string $refresh)
    {
        $this->access = $access;
        $this->refresh = $refresh;
    }

    public function getAccess(): string
    {
        return $this->access;
    }

    public function getRefresh(): string
    {
        return $this->refresh;
    }
}
