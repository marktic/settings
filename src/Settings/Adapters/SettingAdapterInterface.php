<?php

declare(strict_types=1);

namespace Marktic\Settings\Settings\Adapters;

use Marktic\Settings\Settings\Dto\SettingDto;

interface SettingAdapterInterface
{
    public function find(
        string $name,
        string $group = 'default',
        ?string $tenantType = null,
        string|int|null $tenantId = null
    ): ?SettingDto;

    public function save(SettingDto $dto): SettingDto;

    public function delete(SettingDto $dto): void;

    /**
     * @return SettingDto[]
     */
    public function all(
        ?string $group = null,
        ?string $tenantType = null,
        string|int|null $tenantId = null
    ): array;
}
