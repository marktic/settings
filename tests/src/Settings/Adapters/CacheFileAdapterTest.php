<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Settings\Adapters;

use Marktic\Settings\Settings\Adapters\CacheFileAdapter;
use Marktic\Settings\Settings\Dto\SettingDto;
use Marktic\Settings\Settings\Enums\SettingType;
use Marktic\Settings\Settings\Mapper\SettingMapper;
use Marktic\Settings\Tests\AbstractTest;

class CacheFileAdapterTest extends AbstractTest
{
    private string $cacheFile;
    private CacheFileAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheFile = sys_get_temp_dir() . '/mkt_settings_test_' . uniqid() . '.json';
        $this->adapter = new CacheFileAdapter($this->cacheFile, new SettingMapper());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (is_file($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    public function testSaveAndFind(): void
    {
        $dto = new SettingDto();
        $dto->name = 'site.title';
        $dto->group = 'general';
        $dto->type = SettingType::String;
        $dto->value = 'My Site';

        $saved = $this->adapter->save($dto);

        self::assertNotNull($saved->id);
        self::assertNotNull($saved->createdAt);
        self::assertNotNull($saved->updatedAt);

        $found = $this->adapter->find('site.title', 'general');

        self::assertNotNull($found);
        self::assertSame('site.title', $found->name);
        self::assertSame('My Site', $found->value);
    }

    public function testFindReturnsNullForMissing(): void
    {
        $result = $this->adapter->find('nonexistent', 'default');
        self::assertNull($result);
    }

    public function testDelete(): void
    {
        $dto = new SettingDto();
        $dto->name = 'to.delete';
        $dto->group = 'default';
        $dto->type = SettingType::String;
        $dto->value = 'temp';

        $saved = $this->adapter->save($dto);
        $this->adapter->delete($saved);

        $found = $this->adapter->find('to.delete', 'default');
        self::assertNull($found);
    }

    public function testAll(): void
    {
        $dto1 = new SettingDto();
        $dto1->name = 'setting1';
        $dto1->group = 'group_a';
        $dto1->type = SettingType::String;
        $dto1->value = 'value1';

        $dto2 = new SettingDto();
        $dto2->name = 'setting2';
        $dto2->group = 'group_b';
        $dto2->type = SettingType::String;
        $dto2->value = 'value2';

        $this->adapter->save($dto1);
        $this->adapter->save($dto2);

        $all = $this->adapter->all();
        self::assertCount(2, $all);

        $filtered = $this->adapter->all('group_a');
        self::assertCount(1, $filtered);
        self::assertSame('setting1', $filtered[0]->name);
    }

    public function testPersistsToDisk(): void
    {
        $dto = new SettingDto();
        $dto->name = 'persistent';
        $dto->group = 'test';
        $dto->type = SettingType::String;
        $dto->value = 'data';

        $this->adapter->save($dto);

        self::assertFileExists($this->cacheFile);

        // Create a fresh adapter from same file
        $newAdapter = new CacheFileAdapter($this->cacheFile, new SettingMapper());
        $found = $newAdapter->find('persistent', 'test');

        self::assertNotNull($found);
        self::assertSame('data', $found->value);
    }

    public function testTenantFilter(): void
    {
        $dto1 = new SettingDto();
        $dto1->name = 'key';
        $dto1->group = 'default';
        $dto1->type = SettingType::String;
        $dto1->value = 'tenant1_value';
        $dto1->tenantType = 'User';
        $dto1->tenantId = 1;

        $dto2 = new SettingDto();
        $dto2->name = 'key';
        $dto2->group = 'default';
        $dto2->type = SettingType::String;
        $dto2->value = 'tenant2_value';
        $dto2->tenantType = 'User';
        $dto2->tenantId = 2;

        $this->adapter->save($dto1);
        $this->adapter->save($dto2);

        $result = $this->adapter->find('key', 'default', 'User', 1);
        self::assertNotNull($result);
        self::assertSame('tenant1_value', $result->value);

        $all = $this->adapter->all(null, 'User', 1);
        self::assertCount(1, $all);
    }
}
