<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests;

use Marktic\Settings\Hydrator\SettingsHydrator;
use Marktic\Settings\Settings\Dto\SettingDto;
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

    public function testSettingTypeReadsRadioAttributeOnProperty(): void
    {
        self::assertSame('radio', AttributeTypedSettings::settingType('preferred_theme'));
    }

    public function testSettingTypeReadsMultiSelectAttributeOnProperty(): void
    {
        self::assertSame('multiselect', AttributeTypedSettings::settingType('tags'));
    }

    public function testSettingTypeReadsCheckboxGroupAttributeOnProperty(): void
    {
        self::assertSame('checkboxgroup', AttributeTypedSettings::settingType('active_features'));
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

        $hydrator = new SettingsHydrator();
        $dtos = $hydrator->extract($settings);

        $byName = [];
        foreach ($dtos as $dto) {
            $byName[$dto->name] = $dto;
        }

        self::assertSame(SettingType::Date, $byName['launch_date']->type);
        self::assertSame(SettingType::DateTime, $byName['maintenance_at']->type);
        self::assertSame(SettingType::Email, $byName['support_email']->type);
        self::assertSame(SettingType::Url, $byName['homepage_url']->type);
        self::assertSame(SettingType::Radio, $byName['preferred_theme']->type);
        self::assertSame(SettingType::MultiSelect, $byName['tags']->type);
        self::assertSame(SettingType::CheckboxGroup, $byName['active_features']->type);
        self::assertSame(SettingType::String, $byName['site_name']->type);
        self::assertSame(SettingType::Boolean, $byName['site_active']->type);
    }

    public function testCheckboxGroupEncodeAndCastRoundTrip(): void
    {
        $value = ['dark_mode', 'newsletter'];
        $encoded = SettingType::CheckboxGroup->encode($value);
        self::assertSame('["dark_mode","newsletter"]', $encoded);
        self::assertSame($value, SettingType::CheckboxGroup->cast($encoded));
    }

    public function testMultiSelectEncodeAndCastRoundTrip(): void
    {
        $value = ['php', 'laravel'];
        $encoded = SettingType::MultiSelect->encode($value);
        self::assertSame('["php","laravel"]', $encoded);
        self::assertSame($value, SettingType::MultiSelect->cast($encoded));
    }

    public function testHydrateCheckboxGroupRestoresArray(): void
    {
        $settings = new AttributeTypedSettings();

        $dto = new SettingDto();
        $dto->name = 'active_features';
        $dto->type = SettingType::CheckboxGroup;
        $dto->value = '["dark_mode","two_factor"]';

        $hydrator = new SettingsHydrator();
        $hydrator->hydrate($settings, [$dto]);

        self::assertSame(['dark_mode', 'two_factor'], $settings->active_features);
    }

    public function testHydrateMultiSelectRestoresArray(): void
    {
        $settings = new AttributeTypedSettings();

        $dto = new SettingDto();
        $dto->name = 'tags';
        $dto->type = SettingType::MultiSelect;
        $dto->value = '["php","laravel"]';

        $hydrator = new SettingsHydrator();
        $hydrator->hydrate($settings, [$dto]);

        self::assertSame(['php', 'laravel'], $settings->tags);
    }
}
