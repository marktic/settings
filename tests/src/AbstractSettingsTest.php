<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests;

use Marktic\Settings\Tests\Fixtures\Settings\AutoDerivedSettings;
use Marktic\Settings\Tests\Fixtures\Settings\GeneralSettings;
use Marktic\Settings\Tests\Fixtures\Settings\NameOverrideSettings;
use Marktic\Settings\Tests\Fixtures\Settings\NamespacedSettings;
use Marktic\Settings\Tests\Fixtures\Settings\TenantSettings;
use Marktic\Settings\Settings\Enums\SettingType;

class AbstractSettingsTest extends AbstractTest
{
    public function testGroupAutoDerivesFromClassName(): void
    {
        self::assertSame('auto_derived', AutoDerivedSettings::group());
    }

    public function testGroupAutoDerivesRemovesSettingsSuffix(): void
    {
        self::assertSame('general', GeneralSettings::group());
        self::assertSame('tenant', TenantSettings::group());
    }

    public function testGroupIsOverriddenByNameConst(): void
    {
        self::assertSame('custom_group', NameOverrideSettings::group());
    }

    public function testSettingsNamespaceReturnsNullByDefault(): void
    {
        self::assertNull(GeneralSettings::settingsNamespace());
        self::assertNull(AutoDerivedSettings::settingsNamespace());
    }

    public function testSettingsNamespaceReturnsNamespaceConst(): void
    {
        self::assertSame('mymodule', NamespacedSettings::settingsNamespace());
    }

    public function testSettingTypeDefaultsToNullWhenNotConfigured(): void
    {
        self::assertNull(AutoDerivedSettings::settingType('unknown'));
    }

    public function testSettingTypeReturnsConfiguredType(): void
    {
        self::assertSame(SettingType::Date, GeneralSettings::settingType('launch_date'));
        self::assertSame(SettingType::DateTime, GeneralSettings::settingType('maintenance_at'));
        self::assertSame(SettingType::Email, GeneralSettings::settingType('support_email'));
        self::assertSame(SettingType::Url, GeneralSettings::settingType('homepage_url'));
    }
}
