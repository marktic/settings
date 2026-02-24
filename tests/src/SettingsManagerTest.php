<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests;

use Marktic\Settings\Hydrator\SettingsHydrator;
use Marktic\Settings\Mapper\SettingMapper;
use Marktic\Settings\MktSettingsManager;
use Marktic\Settings\SettingsTenantInterface;
use Marktic\Settings\Storages\FileStorage;
use Marktic\Settings\Tests\Fixtures\Settings\AutoDerivedSettings;
use Marktic\Settings\Tests\Fixtures\Settings\GeneralSettings;
use Marktic\Settings\Tests\Fixtures\Settings\NameOverrideSettings;
use Marktic\Settings\Tests\Fixtures\Settings\NamespacedSettings;
use Marktic\Settings\Tests\Fixtures\Settings\TenantSettings;

class SettingsManagerTest extends AbstractTest
{
    private string $cacheFile;
    private FileStorage $storage;
    private MktSettingsManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheFile = sys_get_temp_dir() . '/mkt_settings_manager_test_' . uniqid() . '.json';
        $this->storage = new FileStorage($this->cacheFile, new SettingMapper());
        $this->manager = new MktSettingsManager($this->storage, new SettingsHydrator());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (is_file($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    public function testGetReturnsHydratedInstanceWithDefaults(): void
    {
        $settings = $this->manager->get(GeneralSettings::class);

        self::assertInstanceOf(GeneralSettings::class, $settings);
        self::assertSame('Marktic', $settings->site_name);
        self::assertTrue($settings->site_active);
        self::assertSame(10, $settings->max_items);
        self::assertSame(0.2, $settings->tax_rate);
        self::assertSame(['en'], $settings->supported_locales);
    }

    public function testGetReturnsSameInstanceOnRepeatedCalls(): void
    {
        $first = $this->manager->get(GeneralSettings::class);
        $second = $this->manager->get(GeneralSettings::class);

        self::assertSame($first, $second);
    }

    public function testSaveAndReload(): void
    {
        $settings = $this->manager->get(GeneralSettings::class);
        $settings->site_name = 'New Name';
        $settings->max_items = 99;

        $this->manager->save($settings);

        // Fresh manager from same storage to simulate reload
        $freshManager = new MktSettingsManager($this->storage, new SettingsHydrator());
        $reloaded = $freshManager->get(GeneralSettings::class);

        self::assertSame('New Name', $reloaded->site_name);
        self::assertSame(99, $reloaded->max_items);
    }

    public function testSavePreservesTypeCasting(): void
    {
        $settings = $this->manager->get(GeneralSettings::class);
        $settings->site_active = false;
        $settings->tax_rate = 0.15;
        $settings->supported_locales = ['en', 'de'];

        $this->manager->save($settings);

        $freshManager = new MktSettingsManager($this->storage, new SettingsHydrator());
        $reloaded = $freshManager->get(GeneralSettings::class);

        self::assertFalse($reloaded->site_active);
        self::assertSame(0.15, $reloaded->tax_rate);
        self::assertSame(['en', 'de'], $reloaded->supported_locales);
    }

    public function testGetWithTenantReturnsIsolatedInstance(): void
    {
        $tenant = $this->createTenant('App\\Models\\Org', 1);

        $global = $this->manager->get(TenantSettings::class);
        $tenanted = $this->manager->get(TenantSettings::class, $tenant);

        // Different objects for different tenant contexts
        self::assertNotSame($global, $tenanted);
    }

    public function testGetWithSameTenantReturnsSameInstance(): void
    {
        $tenant = $this->createTenant('App\\Models\\Org', 1);

        $first = $this->manager->get(TenantSettings::class, $tenant);
        $second = $this->manager->get(TenantSettings::class, $tenant);

        self::assertSame($first, $second);
    }

    public function testSaveAndReloadWithTenant(): void
    {
        $tenant = $this->createTenant('App\\Models\\Org', 42);

        $settings = $this->manager->get(TenantSettings::class, $tenant);
        $settings->theme = 'dark';
        $this->manager->save($settings);

        $freshManager = new MktSettingsManager($this->storage, new SettingsHydrator());
        $reloaded = $freshManager->get(TenantSettings::class, $tenant);

        self::assertSame('dark', $reloaded->theme);
    }

    public function testTenantContextIsIsolated(): void
    {
        $tenant1 = $this->createTenant('App\\Models\\Org', 1);
        $tenant2 = $this->createTenant('App\\Models\\Org', 2);

        $s1 = $this->manager->get(TenantSettings::class, $tenant1);
        $s1->theme = 'dark';
        $this->manager->save($s1);

        $s2 = $this->manager->get(TenantSettings::class, $tenant2);
        $s2->theme = 'light';
        $this->manager->save($s2);

        $freshManager = new MktSettingsManager($this->storage, new SettingsHydrator());
        $r1 = $freshManager->get(TenantSettings::class, $tenant1);
        $r2 = $freshManager->get(TenantSettings::class, $tenant2);

        self::assertSame('dark', $r1->theme);
        self::assertSame('light', $r2->theme);
    }

    public function testFlushClearsInstanceCache(): void
    {
        $first = $this->manager->get(GeneralSettings::class);
        $this->manager->flush();
        $second = $this->manager->get(GeneralSettings::class);

        self::assertNotSame($first, $second);
    }

    public function testNamedStorageIsUsedWhenRepositoryMatches(): void
    {
        $altFile = sys_get_temp_dir() . '/mkt_alt_storage_' . uniqid() . '.json';
        try {
            $altStorage = new FileStorage($altFile, new SettingMapper());
            $this->manager->addStorage('file', $altStorage);

            // GeneralSettings::repository() returns null → uses defaultStorage
            $settings = $this->manager->get(GeneralSettings::class);
            $settings->site_name = 'from default';
            $this->manager->save($settings);

            // Verify it went to the default storage
            $found = $this->storage->find('site_name', 'general');
            self::assertNotNull($found);
            self::assertSame('from default', $found->value);
        } finally {
            if (is_file($altFile)) {
                unlink($altFile);
            }
        }
    }

    public function testAutoDerivedGroupSaveAndReload(): void
    {
        $settings = $this->manager->get(AutoDerivedSettings::class);
        $settings->color = 'red';
        $this->manager->save($settings);

        $freshManager = new MktSettingsManager($this->storage, new SettingsHydrator());
        $reloaded = $freshManager->get(AutoDerivedSettings::class);

        self::assertSame('red', $reloaded->color);
    }

    public function testNameConstOverridesGroupInStorage(): void
    {
        $settings = $this->manager->get(NameOverrideSettings::class);
        $settings->title = 'New Title';
        $this->manager->save($settings);

        $found = $this->storage->find('title', 'custom_group');
        self::assertNotNull($found);
        self::assertSame('New Title', $found->value);
    }

    public function testNamespacedSettingsSaveAndReload(): void
    {
        $settings = $this->manager->get(NamespacedSettings::class);
        $settings->mode = 'debug';
        $this->manager->save($settings);

        $freshManager = new MktSettingsManager($this->storage, new SettingsHydrator());
        $reloaded = $freshManager->get(NamespacedSettings::class);

        self::assertSame('debug', $reloaded->mode);
    }

    public function testNamespacedSettingsDtoHasNamespace(): void
    {
        $settings = $this->manager->get(NamespacedSettings::class);
        $this->manager->save($settings);

        $found = $this->storage->find('mode', 'namespaced', null, null, 'mymodule');
        self::assertNotNull($found);
        self::assertSame('mymodule', $found->namespace);
    }

    private function createTenant(string $type, int $id): object
    {
        return new class ($type, $id) implements SettingsTenantInterface {
            public function __construct(
                private readonly string $type,
                private readonly int $id
            ) {
            }

            public function getSettingTenantType(): string
            {
                return $this->type;
            }

            public function getSettingTenantId(): string|int|null
            {
                return $this->id;
            }
        };
    }
}
