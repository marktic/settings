<?php

declare(strict_types=1);

namespace Marktic\Settings\Storages;

use Marktic\Settings\Mapper\SettingMapper;
use Marktic\Settings\Settings\Dto\SettingDto;

class FileStorage implements SettingStorageInterface
{
    private array $cache = [];

    private bool $loaded = false;

    public function __construct(
        private readonly string $cachePath,
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
        $this->load();
        $key = $this->buildKey($name, $group, $tenantType, $tenantId, $namespace);

        if (!isset($this->cache[$key])) {
            return null;
        }

        return $this->mapper->fromArray($this->cache[$key]);
    }

    public function save(SettingDto $dto): SettingDto
    {
        $this->load();

        if ($dto->id === null) {
            $dto->id = $this->generateId();
            $dto->createdAt = new \DateTimeImmutable();
        }
        $dto->updatedAt = new \DateTimeImmutable();

        $key = $this->buildKey($dto->name, $dto->group, $dto->tenantType, $dto->tenantId, $dto->namespace);
        $this->cache[$key] = $this->mapper->toArray($dto);
        $this->persist();

        return $dto;
    }

    public function delete(SettingDto $dto): void
    {
        $this->load();
        $key = $this->buildKey($dto->name, $dto->group, $dto->tenantType, $dto->tenantId, $dto->namespace);
        unset($this->cache[$key]);
        $this->persist();
    }

    public function all(
        ?string $group = null,
        ?string $tenantType = null,
        string|int|null $tenantId = null,
        ?string $namespace = null
    ): array {
        $this->load();

        $dtos = [];
        foreach ($this->cache as $data) {
            $dto = $this->mapper->fromArray($data);

            if ($group !== null && $dto->group !== $group) {
                continue;
            }
            if ($namespace !== null && $dto->namespace !== $namespace) {
                continue;
            }
            if ($tenantType !== null && $dto->tenantType !== $tenantType) {
                continue;
            }
            if ($tenantId !== null && (string) $dto->tenantId !== (string) $tenantId) {
                continue;
            }

            $dtos[] = $dto;
        }

        return $dtos;
    }

    private function buildKey(
        string $name,
        string $group,
        ?string $tenantType,
        string|int|null $tenantId,
        ?string $namespace = null
    ): string {
        return implode('|', [$namespace ?? '', $group, $name, $tenantType ?? '', (string) ($tenantId ?? '')]);
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        if (is_file($this->cachePath)) {
            $content = file_get_contents($this->cachePath);
            if ($content !== false) {
                $this->cache = json_decode($content, true) ?? [];
            }
        }

        $this->loaded = true;
    }

    private function persist(): void
    {
        $dir = dirname($this->cachePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $this->cachePath,
            json_encode($this->cache, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
        );
    }

    private function generateId(): int
    {
        return max(
            array_map(
                static fn(array $item): int => (int) ($item['id'] ?? 0),
                $this->cache
            ) ?: [0]
        ) + 1;
    }
}
