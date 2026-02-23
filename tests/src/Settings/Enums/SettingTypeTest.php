<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Settings\Enums;

use Marktic\Settings\Settings\Enums\SettingType;
use Marktic\Settings\Tests\AbstractTest;

class SettingTypeTest extends AbstractTest
{
    public function testCastString(): void
    {
        self::assertSame('hello', SettingType::String->cast('hello'));
    }

    public function testCastInteger(): void
    {
        self::assertSame(42, SettingType::Integer->cast('42'));
    }

    public function testCastFloat(): void
    {
        self::assertSame(3.14, SettingType::Float->cast('3.14'));
    }

    public function testCastBoolean(): void
    {
        self::assertTrue(SettingType::Boolean->cast('1'));
        self::assertFalse(SettingType::Boolean->cast('0'));
    }

    public function testCastJson(): void
    {
        $result = SettingType::Json->cast('{"key":"value"}');
        self::assertIsArray($result);
        self::assertSame('value', $result['key']);
    }

    public function testEncodeString(): void
    {
        self::assertSame('hello', SettingType::String->encode('hello'));
    }

    public function testEncodeInteger(): void
    {
        self::assertSame('42', SettingType::Integer->encode(42));
    }

    public function testEncodeJson(): void
    {
        $result = SettingType::Json->encode(['key' => 'value']);
        self::assertSame('{"key":"value"}', $result);
    }

    public function testEncodeBoolean(): void
    {
        self::assertSame('1', SettingType::Boolean->encode(true));
        self::assertSame('0', SettingType::Boolean->encode(false));
    }

    public function testFromValue(): void
    {
        self::assertSame(SettingType::String, SettingType::from('string'));
        self::assertSame(SettingType::Json, SettingType::from('json'));
        self::assertSame(SettingType::Integer, SettingType::from('integer'));
    }
}
