<?php

declare(strict_types=1);

namespace Marktic\Settings\Mapper;

use Marktic\Settings\Settings\Dto\SettingDto;
use Marktic\Settings\Settings\Enums\SettingType;
use Marktic\Settings\Settings\Models\Setting;

class SettingMapper
{
    public function fromRecord(Setting $record): SettingDto
    {
        $dto = new SettingDto();
        $dto->id = $record->id ? (int) $record->id : null;
        $dto->name = (string) $record->name;
        $dto->group = (string) ($record->group ?? 'default');
        $dto->namespace = $record->namespace ?? null;
        $dto->value = (string) ($record->value ?? '');
        $dto->type = SettingType::from($record->type ?? 'string');
        $dto->tenantType = $record->tenant_type;
        $dto->tenantId = $record->tenant_id;

        if ($record->created_at) {
            $dto->createdAt = $record->created_at->toDateTimeImmutable();
        }
        if ($record->updated_at) {
            $dto->updatedAt = $record->updated_at->toDateTimeImmutable();
        }

        return $dto;
    }

    public function toRecord(SettingDto $dto, ?Setting $record = null): Setting
    {
        if ($record === null) {
            $record = new Setting();
        }

        if ($dto->id !== null) {
            $record->id = $dto->id;
        }

        $record->name = $dto->name;
        $record->group = $dto->group;
        $record->namespace = $dto->namespace;
        $record->value = $dto->value;
        $record->type = $dto->type->value;
        $record->tenant_type = $dto->tenantType;
        $record->tenant_id = $dto->tenantId;

        return $record;
    }

    public function fromArray(array $data): SettingDto
    {
        $dto = new SettingDto();
        $dto->id = isset($data['id']) ? (int) $data['id'] : null;
        $dto->name = (string) ($data['name'] ?? '');
        $dto->group = (string) ($data['group'] ?? 'default');
        $dto->namespace = $data['namespace'] ?? null;
        $dto->value = (string) ($data['value'] ?? '');
        $dto->type = SettingType::from($data['type'] ?? 'string');
        $dto->tenantType = $data['tenant_type'] ?? null;
        $dto->tenantId = $data['tenant_id'] ?? null;

        if (!empty($data['created_at'])) {
            $dto->createdAt = new \DateTimeImmutable($data['created_at']);
        }
        if (!empty($data['updated_at'])) {
            $dto->updatedAt = new \DateTimeImmutable($data['updated_at']);
        }

        return $dto;
    }

    public function toArray(SettingDto $dto): array
    {
        return [
            'id' => $dto->id,
            'name' => $dto->name,
            'group' => $dto->group,
            'namespace' => $dto->namespace,
            'value' => $dto->value,
            'type' => $dto->type->value,
            'tenant_type' => $dto->tenantType,
            'tenant_id' => $dto->tenantId,
            'created_at' => $dto->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $dto->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
