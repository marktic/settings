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
}
