<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Settings\Enums;

use Marktic\Settings\Settings\Enums\SettingType;
use Marktic\Settings\Tests\AbstractTest;
use Marktic\Settings\Tests\Fixtures\Settings\Theme;

class SettingTypeSelectTest extends AbstractTest
{
    public function testSelectCastReturnsRawString(): void
    {
        self::assertSame('dark', SettingType::Select->cast('dark'));
        self::assertSame('', SettingType::Select->cast(''));
    }

    public function testSelectEncodeReturnsBareString(): void
    {
        self::assertSame('light', SettingType::Select->encode('light'));
    }

    public function testSelectEncodeExtractsBackedEnumValue(): void
    {
        self::assertSame('dark', SettingType::Select->encode(Theme::Dark));
        self::assertSame('light', SettingType::Select->encode(Theme::Light));
    }

    public function testSelectFromValue(): void
    {
        self::assertSame(SettingType::Select, SettingType::from('select'));
    }

    public function testRadioCastReturnsRawString(): void
    {
        self::assertSame('dark', SettingType::Radio->cast('dark'));
        self::assertSame('', SettingType::Radio->cast(''));
    }

    public function testRadioEncodeReturnsBareString(): void
    {
        self::assertSame('light', SettingType::Radio->encode('light'));
    }

    public function testRadioEncodeExtractsBackedEnumValue(): void
    {
        self::assertSame('dark', SettingType::Radio->encode(Theme::Dark));
    }

    public function testRadioFromValue(): void
    {
        self::assertSame(SettingType::Radio, SettingType::from('radio'));
    }

    public function testMultiSelectCastReturnsArray(): void
    {
        $result = SettingType::MultiSelect->cast('["dark_mode","newsletter"]');
        self::assertIsArray($result);
        self::assertSame(['dark_mode', 'newsletter'], $result);
    }

    public function testMultiSelectCastEmptyJsonArray(): void
    {
        $result = SettingType::MultiSelect->cast('[]');
        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testMultiSelectEncodeStringArray(): void
    {
        $encoded = SettingType::MultiSelect->encode(['dark_mode', 'newsletter']);
        self::assertSame('["dark_mode","newsletter"]', $encoded);
    }

    public function testMultiSelectEncodeBackedEnumArray(): void
    {
        $encoded = SettingType::MultiSelect->encode([Theme::Dark, Theme::System]);
        self::assertSame('["dark","system"]', $encoded);
    }

    public function testMultiSelectEncodeEmptyArray(): void
    {
        self::assertSame('[]', SettingType::MultiSelect->encode([]));
    }

    public function testMultiSelectFromValue(): void
    {
        self::assertSame(SettingType::MultiSelect, SettingType::from('multiselect'));
    }
}
