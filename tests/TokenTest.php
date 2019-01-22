<?php

namespace Wearesho\Yii2\Authorization\Tests;

use Wearesho\Yii2\Authorization;
use PHPUnit\Framework\TestCase;

/**
 * Class TokenTest
 * @package Wearesho\Yii2\Authorization\Tests
 */
class TokenTest extends TestCase
{
    protected const ACCESS = 'accessValue';
    protected const REFRESH = 'refreshValue';

    /** @var Authorization\Token */
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = new Authorization\Token(static::ACCESS, static::REFRESH);
    }

    public function testGetAccess(): void
    {
        $this->assertEquals(
            static::ACCESS,
            $this->token->getAccess()
        );
    }

    public function testGetRefresh(): void
    {
        $this->assertEquals(
            static::REFRESH,
            $this->token->getRefresh()
        );
    }
}
