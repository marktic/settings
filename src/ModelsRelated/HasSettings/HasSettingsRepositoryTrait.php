<?php

declare(strict_types=1);

namespace Marktic\Settings\ModelsRelated\HasSettings;

use Marktic\Settings\Utility\MktSettings;

trait HasSettingsRepositoryTrait
{
    public function findSettingsByTenant(string $tenantType, string|int $tenantId, ?string $group = null): array
    {
        return MktSettings::databaseStorage()->all($group, $tenantType, $tenantId);
    }
}
