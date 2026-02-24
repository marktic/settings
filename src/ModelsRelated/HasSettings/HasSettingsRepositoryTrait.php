<?php

declare(strict_types=1);

namespace Marktic\Settings\ModelsRelated\HasSettings;

use Marktic\Settings\Settings\Adapters\SettingAdapterInterface;
use Marktic\Settings\Settings\Dto\SettingDto;
use Marktic\Settings\Utility\MktSettingsModels;

trait HasSettingsRepositoryTrait
{
    public function findSettingsByTenant(string $tenantType, string|int $tenantId, ?string $group = null): array
    {
        return MktSettingsModels::createDatabaseAdapter()->all($group, $tenantType, $tenantId);
    }
}
