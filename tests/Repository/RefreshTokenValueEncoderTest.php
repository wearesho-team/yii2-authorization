<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Authorization\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Wearesho\Yii2\Authorization\Repository\RefreshTokenValue;
use Wearesho\Yii2\Authorization\Repository\RefreshTokenValueEncoder;

class RefreshTokenValueEncoderTest extends TestCase
{
    public function decodingDataProvider(): iterable
    {
        return [
            [Uuid::uuid4()->toString(), null], // deprecated values
            ['not-really-valid-data', null],
            ['v2:eeea00b7-d766-4570-864c-a9fd5c288489:1337:invalid-hash', null,],
            ['v2:eeea00b7-d766-4570-864c-a9fd5c288489:1337:8b9746bf', new RefreshTokenValue(
                'eeea00b7-d766-4570-864c-a9fd5c288489',
                1337,
            )],
        ];
    }

    /**
     * @dataProvider decodingDataProvider
     */
    public function testDecoding(string $encodedValue, ?RefreshTokenValue $expectedValue): void
    {
        $encoder = new RefreshTokenValueEncoder();
        $value = $encoder->decode($encodedValue);
        $this->assertEquals($expectedValue, $value);
    }

    public function testEncodeAndDecode()
    {
        $accessToken = 'sampleAccessToken';
        $userId = 123;

        $value = new RefreshTokenValue($accessToken, $userId);
        $encoder = new RefreshTokenValueEncoder();

        $encodedValue = $encoder->encode($value);
        $decodedValue = $encoder->decode($encodedValue);

        $this->assertSame($accessToken, $decodedValue->getAccessToken());
        $this->assertSame($userId, $decodedValue->getUserId());
    }

    public function testParseInvalidValue()
    {
        $invalidValue = 'invalid:value';

        $encoder = new RefreshTokenValueEncoder();
        $decodedValue = $encoder->decode($invalidValue);

        $this->assertNull($decodedValue);
    }
}
