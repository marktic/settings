<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Settings\Dto;

use Marktic\Settings\Settings\Dto\SettingDto;
use Marktic\Settings\Settings\Enums\SettingType;
use Marktic\Settings\Tests\AbstractTest;

class SettingDtoTest extends AbstractTest
{
    public function testDefaultValues(): void
    {
        $dto = new SettingDto();

        self::assertNull($dto->id);
        self::assertSame('', $dto->name);
        self::assertSame('default', $dto->group);
        self::assertSame('', $dto->value);
        self::assertSame(SettingType::String, $dto->type);
        self::assertNull($dto->tenantType);
        self::assertNull($dto->tenantId);
        self::assertNull($dto->createdAt);
        self::assertNull($dto->updatedAt);
    }

    public function testGetCastValueString(): void
    {
        $dto = new SettingDto();
        $dto->type = SettingType::String;
        $dto->value = 'hello world';

        self::assertSame('hello world', $dto->getCastValue());
    }

    public function testGetCastValueInteger(): void
    {
        $dto = new SettingDto();
        $dto->type = SettingType::Integer;
        $dto->value = '42';

        self::assertSame(42, $dto->getCastValue());
    }

    public function testGetCastValueJson(): void
    {
        $dto = new SettingDto();
        $dto->type = SettingType::Json;
        $dto->value = '{"foo":"bar"}';

        $result = $dto->getCastValue();
        self::assertIsArray($result);
        self::assertSame('bar', $result['foo']);
    }

    public function testSetValue(): void
    {
        $dto = new SettingDto();
        $dto->type = SettingType::Json;
        $dto->setValue(['key' => 'value']);

        self::assertSame('{"key":"value"}', $dto->value);
    }

    public function testSetValueInteger(): void
    {
        $dto = new SettingDto();
        $dto->type = SettingType::Integer;
        $dto->setValue(100);

        self::assertSame('100', $dto->value);
    }
}
