<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Fixtures\Settings;

use Marktic\Settings\AbstractSettings;
use Marktic\Settings\Settings\Attributes\AsSettingType;
use Marktic\Settings\Settings\Enums\SettingType;

class AttributeTypedSettings extends AbstractSettings
{
    #[AsSettingType('date')]
    public string $launch_date = '2026-01-01';

    #[AsSettingType('datetime')]
    public string $maintenance_at = '2026-01-01 10:00:00';

    #[AsSettingType(SettingType::Email)]
    public string $support_email = 'support@marktic.test';

    #[AsSettingType(SettingType::Url)]
    public string $homepage_url = 'https://marktic.test';

    public string $site_name = 'Marktic';

    public bool $site_active = true;
}
