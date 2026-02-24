<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Settings\Mapper;

use Marktic\Settings\Mapper\SettingMapper;
use Marktic\Settings\Settings\Dto\SettingDto;
use Marktic\Settings\Settings\Enums\SettingType;
use Marktic\Settings\Settings\Models\Setting;
use Marktic\Settings\Tests\AbstractTest;

class SettingMapperTest extends AbstractTest
{
    private SettingMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new SettingMapper();
    }

    public function testFromRecord(): void
    {
        $record = new Setting();
        $record->id = 1;
        $record->name = 'site.title';
        $record->group = 'general';
        $record->value = 'My Site';
        $record->type = 'string';
        $record->tenant_type = null;
        $record->tenant_id = null;

        $dto = $this->mapper->fromRecord($record);

        self::assertSame(1, $dto->id);
        self::assertSame('site.title', $dto->name);
        self::assertSame('general', $dto->group);
        self::assertSame('My Site', $dto->value);
        self::assertSame(SettingType::String, $dto->type);
        self::assertNull($dto->tenantType);
        self::assertNull($dto->tenantId);
    }

    public function testToRecord(): void
    {
        $dto = new SettingDto();
        $dto->name = 'site.title';
        $dto->group = 'general';
        $dto->value = 'My Site';
        $dto->type = SettingType::String;

        $record = $this->mapper->toRecord($dto);

        self::assertSame('site.title', $record->name);
        self::assertSame('general', $record->group);
        self::assertSame('My Site', $record->value);
        self::assertSame('string', $record->type);
    }

    public function testToAndFromArray(): void
    {
        $dto = new SettingDto();
        $dto->id = 5;
        $dto->name = 'config.key';
        $dto->group = 'app';
        $dto->value = '{"debug":true}';
        $dto->type = SettingType::Json;
        $dto->tenantType = 'App\\Models\\User';
        $dto->tenantId = 42;

        $array = $this->mapper->toArray($dto);
        $restored = $this->mapper->fromArray($array);

        self::assertSame($dto->id, $restored->id);
        self::assertSame($dto->name, $restored->name);
        self::assertSame($dto->group, $restored->group);
        self::assertSame($dto->value, $restored->value);
        self::assertSame($dto->type, $restored->type);
        self::assertSame($dto->tenantType, $restored->tenantType);
        self::assertSame($dto->tenantId, $restored->tenantId);
    }

    public function testFromArray(): void
    {
        $array = [
            'id' => 1,
            'name' => 'foo',
            'group' => 'bar',
            'value' => '123',
            'type' => 'integer',
            'tenant_type' => null,
            'tenant_id' => null,
        ];

        $dto = $this->mapper->fromArray($array);

        self::assertSame(1, $dto->id);
        self::assertSame('foo', $dto->name);
        self::assertSame(SettingType::Integer, $dto->type);
    }
}
