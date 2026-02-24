<?php

declare(strict_types=1);

namespace Marktic\Settings\Settings\Models;

use Marktic\Settings\AbstractBase\Models\SettingsRecord;
use Marktic\Settings\Settings\Enums\SettingType;

/**
 * Class Setting
 * @package Marktic\Settings\Settings\Models
 *
 * @property int $id
 * @property string $name
 * @property string $group
 * @property string|null $namespace
 * @property string $value
 * @property string $type
 * @property string|null $tenant_type
 * @property string|int|null $tenant_id
 * @property string $created_at
 * @property string $updated_at
 */
class Setting extends SettingsRecord
{
    public ?string $name = null;

    public ?string $group = 'default';

    public ?string $namespace = null;

    public ?string $value = null;

    public ?string $type = 'string';

    public ?string $tenant_type = null;

    public string|int|null $tenant_id = null;

    public function getType(): SettingType
    {
        return SettingType::from($this->type ?? 'string');
    }

    public function getCastValue(): mixed
    {
        return $this->getType()->cast((string) $this->value);
    }
}
