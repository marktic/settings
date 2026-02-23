<?php

declare(strict_types=1);

namespace Marktic\Settings\Settings\Models;

use Marktic\Settings\AbstractBase\Models\SettingsRepository;

/**
 * Class Settings
 * @package Marktic\Settings\Settings\Models
 *
 * @method Setting getNew
 */
class Settings extends SettingsRepository
{
    public const TABLE = 'mkt_settings';

    public const CONTROLLER = 'mkt-settings';

    public function getModelNamespace(): string
    {
        return __NAMESPACE__;
    }

    public function findByName(string $name, string $group = 'default'): ?Setting
    {
        return $this->findOneByField(['name' => $name, 'group' => $group]);
    }

    public function findByTenant(string $tenantType, string|int $tenantId, ?string $group = null): array
    {
        $params = ['where' => [['tenant_type = ?', $tenantType], ['tenant_id = ?', $tenantId]]];
        if ($group !== null) {
            $params['where'][] = ['group = ?', $group];
        }
        return $this->findAll($params)->toArray();
    }
}
