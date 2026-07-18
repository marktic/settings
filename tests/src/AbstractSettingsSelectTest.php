<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests;

use Marktic\Settings\Settings\Dto\SelectOption;
use Marktic\Settings\Tests\Fixtures\Settings\SelectSettings;
use Marktic\Settings\Tests\Fixtures\Settings\Theme;

class AbstractSettingsSelectTest extends AbstractTest
{
    public function testSettingOptionReturnsNullWhenNotDeclared(): void
    {
        self::assertNull(SelectSettings::settingOption('theme'));
    }

    public function testSettingOptionReturnsInlineOptions(): void
    {
        $options = SelectSettings::settingOption('locale');

        self::assertIsArray($options);
        self::assertCount(3, $options);
        self::assertContainsOnlyInstancesOf(SelectOption::class, $options);

        self::assertSame('en', $options[0]->value);
        self::assertSame('English', $options[0]->label);
        self::assertSame('fr', $options[1]->value);
        self::assertSame('French', $options[1]->label);
        self::assertSame('ro', $options[2]->value);
        self::assertSame('Romanian', $options[2]->label);
    }

    public function testSettingOptionsCanDeclareEnumClass(): void
    {
        $options = EnumClassSettings::settingOption('theme');

        self::assertIsArray($options);
        self::assertCount(3, $options);
        self::assertSame('light', $options[0]->value);
        self::assertSame('Light Theme', $options[0]->label);
        self::assertSame('dark', $options[1]->value);
        self::assertSame('Dark Theme', $options[1]->label);
        self::assertSame('system', $options[2]->value);
        self::assertSame('System Default', $options[2]->label);
    }
}

/**
 * Local fixture: uses a backed enum class string in settingOptions().
 */
class EnumClassSettings extends \Marktic\Settings\AbstractSettings
{
    public string $theme = 'light';

    public static function settingOptions(): array
    {
        return [
            'theme' => Theme::class,
        ];
    }
}
