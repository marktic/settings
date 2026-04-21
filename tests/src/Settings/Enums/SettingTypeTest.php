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

    public function testCastDate(): void
    {
        self::assertSame('2026-04-21', SettingType::Date->cast('2026-04-21'));
    }

    public function testCastDateTime(): void
    {
        self::assertSame('2026-04-21 18:30:00', SettingType::DateTime->cast('2026-04-21T18:30'));
    }

    public function testCastEmailAndUrl(): void
    {
        self::assertSame('user@example.com', SettingType::Email->cast(' user@example.com '));
        self::assertSame('https://example.com', SettingType::Url->cast(' https://example.com '));
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

    public function testEncodeDateAndDateTime(): void
    {
        self::assertSame('2026-04-21', SettingType::Date->encode('2026-04-21'));
        self::assertSame('2026-04-21 18:30:00', SettingType::DateTime->encode('2026-04-21 18:30'));
    }

    public function testFromValue(): void
    {
        self::assertSame(SettingType::String, SettingType::from('string'));
        self::assertSame(SettingType::Json, SettingType::from('json'));
        self::assertSame(SettingType::Integer, SettingType::from('integer'));
        self::assertSame(SettingType::Date, SettingType::from('date'));
        self::assertSame(SettingType::DateTime, SettingType::from('datetime'));
        self::assertSame(SettingType::Email, SettingType::from('email'));
        self::assertSame(SettingType::Url, SettingType::from('url'));
    }
}
