<?php

declare(strict_types=1);

namespace Marktic\Settings\Settings\Dto;

use Marktic\Settings\Settings\Enums\SettingType;

class SettingDto
{
    public ?int $id = null;

    public string $name = '';

    public string $group = 'default';

    public ?string $namespace = null;

    public string $value = '';

    public SettingType $type = SettingType::String;

    public ?string $tenantType = null;

    public string|int|null $tenantId = null;

    public ?\DateTimeImmutable $createdAt = null;

    public ?\DateTimeImmutable $updatedAt = null;

    public function getCastValue(): mixed
    {
        return $this->type->cast($this->value);
    }

    public function setValue(mixed $value): self
    {
        $this->value = $this->type->encode($value);
        return $this;
    }
}
