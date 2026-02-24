<?php

declare(strict_types=1);

namespace Marktic\Settings\Settings\Storages;

use Marktic\Settings\Settings\Dto\SettingDto;
use Marktic\Settings\Settings\Mapper\SettingMapper;
use Marktic\Settings\Settings\Models\Settings;

class DatabaseStorage implements SettingStorageInterface
{
    public function __construct(
        private readonly Settings $repository,
        private readonly SettingMapper $mapper
    ) {
    }

    public function find(
        string $name,
        string $group = 'default',
        ?string $tenantType = null,
        string|int|null $tenantId = null,
        ?string $namespace = null
    ): ?SettingDto {
        $params = [
            'where' => [
                ['name = ?', $name],
                ['`group` = ?', $group],
            ],
        ];

        if ($namespace !== null) {
            $params['where'][] = ['namespace = ?', $namespace];
        }

        if ($tenantType !== null) {
            $params['where'][] = ['tenant_type = ?', $tenantType];
            $params['where'][] = ['tenant_id = ?', $tenantId];
        }

        $record = $this->repository->findOne($params);
        if ($record === null) {
            return null;
        }

        return $this->mapper->fromRecord($record);
    }

    public function save(SettingDto $dto): SettingDto
    {
        $record = null;
        if ($dto->id !== null) {
            $record = $this->repository->findById($dto->id);
        }

        $record = $this->mapper->toRecord($dto, $record);

        if ($dto->id !== null) {
            $this->repository->update($record);
        } else {
            $this->repository->insert($record);
            $dto->id = (int) $record->id;
        }

        return $dto;
    }

    public function delete(SettingDto $dto): void
    {
        if ($dto->id === null) {
            return;
        }

        $record = $this->repository->findById($dto->id);
        if ($record !== null) {
            $this->repository->delete($record);
        }
    }

    public function all(
        ?string $group = null,
        ?string $tenantType = null,
        string|int|null $tenantId = null,
        ?string $namespace = null
    ): array {
        $params = ['where' => []];

        if ($group !== null) {
            $params['where'][] = ['`group` = ?', $group];
        }

        if ($namespace !== null) {
            $params['where'][] = ['namespace = ?', $namespace];
        }

        if ($tenantType !== null) {
            $params['where'][] = ['tenant_type = ?', $tenantType];
            $params['where'][] = ['tenant_id = ?', $tenantId];
        }

        $records = $this->repository->findAll($params);
        $dtos = [];
        foreach ($records as $record) {
            $dtos[] = $this->mapper->fromRecord($record);
        }

        return $dtos;
    }
}
