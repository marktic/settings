<?php

declare(strict_types=1);

namespace Marktic\Settings;

interface SettingsTenantInterface
{
    public function getSettingTenantType(): string;

    public function getSettingTenantId(): string|int|null;
}
