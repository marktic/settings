<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests;

use Marktic\Settings\Settings\Enums\SettingType;
use Marktic\Settings\Tests\Fixtures\Settings\AttributeTypedSettings;
use Marktic\Settings\Tests\Fixtures\Settings\GeneralSettings;

class AsSettingTypeAttributeTest extends AbstractTest
{
    public function testSettingTypeReadsStringAttributeOnProperty(): void
    {
        self::assertSame('date', AttributeTypedSettings::settingType('launch_date'));
        self::assertSame('datetime', AttributeTypedSettings::settingType('maintenance_at'));
    }

    public function testSettingTypeReadsEnumAttributeOnProperty(): void
    {
        self::assertSame('email', AttributeTypedSettings::settingType('support_email'));
        self::assertSame('url', AttributeTypedSettings::settingType('homepage_url'));
    }

    public function testSettingTypeReturnsNullForPropertiesWithoutAttribute(): void
    {
        self::assertNull(AttributeTypedSettings::settingType('site_name'));
        self::assertNull(AttributeTypedSettings::settingType('site_active'));
    }

    public function testSettingTypesArrayTakesPrecedenceOverAttribute(): void
    {
        // GeneralSettings declares types via settingTypes() without using the attribute;
        // this verifies the existing mechanism is unaffected.
        self::assertSame('date', GeneralSettings::settingType('launch_date'));
        self::assertSame('email', GeneralSettings::settingType('support_email'));
    }

    public function testHydratorUsesAttributeTypeForExtract(): void
    {
        $settings = new AttributeTypedSettings();

        $hydrator = new \Marktic\Settings\Hydrator\SettingsHydrator();
        $dtos = $hydrator->extract($settings);

        $byName = [];
        foreach ($dtos as $dto) {
            $byName[$dto->name] = $dto;
        }

        self::assertSame(SettingType::Date, $byName['launch_date']->type);
        self::assertSame(SettingType::DateTime, $byName['maintenance_at']->type);
        self::assertSame(SettingType::Email, $byName['support_email']->type);
        self::assertSame(SettingType::Url, $byName['homepage_url']->type);
        self::assertSame(SettingType::String, $byName['site_name']->type);
        self::assertSame(SettingType::Boolean, $byName['site_active']->type);
    }
}
