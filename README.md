# Marktic Settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/marktic/settings.svg?style=flat-square)](https://packagist.org/packages/marktic/settings)
[![Tests](https://img.shields.io/github/actions/workflow/status/marktic/settings/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/marktic/settings/actions/workflows/run-tests.yml)
[![License](https://img.shields.io/packagist/l/marktic/settings.svg?style=flat-square)](https://packagist.org/packages/marktic/settings)

A multi-tenant settings management package for PHP 8.2+ applications. Supports typed values (string, JSON, integer, float, boolean), grouped settings, tenant-scoped configuration, and multiple storage adapters (database, cache file).

## Features

- **Typed settings**: PHP property types (`string`, `bool`, `int`, `float`, `array`) automatically determine the cast — no manual configuration needed
- **Class-based settings**: Define settings as plain PHP classes extending `AbstractSettings`; properties with default values are automatically used as fallbacks
- **Grouped settings**: Each settings class declares its group via `group()`
- **Multi-tenant support**: Scope settings to any tenant by passing a tenant record to `SettingsManager::get()`
- **Instance caching**: `SettingsManager::get()` always returns the same object for the same class+tenant combination
- **Multiple storages**: Persist settings to a relational database (`DatabaseStorage`) or a JSON cache file (`FileStorage`)
- **Mapper**: `SettingMapper` handles low-level conversion between `SettingDto` and database/array representations
- **Trait-based integration**: Use `HasSettingsRecordTrait` to add per-model setting capabilities
- **Timestamps**: Every stored setting entry tracks `created_at` and `updated_at`

## Installation

```bash
composer require marktic/settings
```

Register the service provider (if not using auto-discovery):

```php
// config/app.php (Laravel)
'providers' => [
    Marktic\Settings\SettingsServiceProvider::class,
],
```

Run migrations:

```bash
php artisan migrate
```

## Usage

### Defining a Settings Class

```php
use Marktic\Settings\AbstractSettings;

class GeneralSettings extends AbstractSettings
{
    // PHP property types determine the storage cast automatically:
    // string → SettingType::String
    // bool   → SettingType::Boolean
    // int    → SettingType::Integer
    // float  → SettingType::Float
    // array  → SettingType::Json (stored as JSON)

    public string $site_name = 'My App';

    public bool $site_active = true;

    public int $max_items = 20;

    public float $tax_rate = 0.2;

    public array $supported_locales = ['en'];

    // Determines the group used in storage
    public static function group(): string
    {
        return 'general';
    }

    // Returns the named storage to use, or null for the package default
    public static function repository(): ?string
    {
        return null;
    }
}
```

### Setting up the SettingsManager

```php
use Marktic\Settings\Settings\Hydrator\SettingsHydrator;
use Marktic\Settings\Settings\Mapper\SettingMapper;
use Marktic\Settings\Settings\Storages\DatabaseStorage;
use Marktic\Settings\Settings\Storages\FileStorage;
use Marktic\Settings\SettingsManager;
use Marktic\Settings\Utility\SettingsModels;

// Database storage (requires bytic/orm set up)
$storage = SettingsModels::createDatabaseStorage();

// File storage (no database required)
$storage = new FileStorage('/path/to/settings.json', new SettingMapper());

$manager = new SettingsManager($storage, new SettingsHydrator());

// Register optional named storages referenced by repository()
$manager->addStorage('file', new FileStorage('/path/to/file.json', new SettingMapper()));
```

### Retrieving and Saving Settings

```php
// Get hydrated settings (default values are used if nothing is stored yet)
$settings = $manager->get(GeneralSettings::class);
echo $settings->site_name; // "My App"

// Repeated calls return the SAME object instance
$settings1 = $manager->get(GeneralSettings::class);
$settings2 = $manager->get(GeneralSettings::class);
var_dump($settings1 === $settings2); // true

// Modify and save
$settings->site_name = 'New Name';
$settings->max_items = 50;
$manager->save($settings);
```

### Tenant-Scoped Settings

```php
use Marktic\Settings\SettingsTenantInterface;

// Your model can implement SettingsTenantInterface
class Organization implements SettingsTenantInterface
{
    public function getSettingTenantType(): string
    {
        return static::class;
    }

    public function getSettingTenantId(): string|int|null
    {
        return $this->id;
    }
}

$org = Organization::find(42);

// Each tenant gets its own isolated instance and storage scope
$tenantSettings = $manager->get(GeneralSettings::class, $org);
$tenantSettings->site_name = 'Org Site';

$manager->save($tenantSettings);

// Another tenant is isolated
$otherOrg = Organization::find(99);
$otherSettings = $manager->get(GeneralSettings::class, $otherOrg);
echo $otherSettings->site_name; // "My App" (its own defaults)
```

### Low-level: Using Storages Directly

```php
use Marktic\Settings\Settings\Dto\SettingDto;
use Marktic\Settings\Settings\Enums\SettingType;
use Marktic\Settings\Settings\Mapper\SettingMapper;
use Marktic\Settings\Settings\Storages\FileStorage;

$storage = new FileStorage('/path/to/settings.json', new SettingMapper());

$dto = new SettingDto();
$dto->name = 'site.title';
$dto->group = 'general';
$dto->type = SettingType::String;
$dto->setValue('My Application');

$storage->save($dto);

$found = $storage->find('site.title', 'general');
echo $found->getCastValue(); // "My Application"
```

## Database Schema

Table: `mkt_settings`

| Column | Type | Description |
|--------|------|-------------|
| `id` | `BIGINT UNSIGNED` | Primary key |
| `name` | `VARCHAR(191)` | Setting name/key |
| `group` | `VARCHAR(100)` | Logical group, default: `"default"` |
| `value` | `TEXT` | Raw stored value |
| `type` | `VARCHAR(20)` | Value type: `string`, `json`, `integer`, `float`, `boolean` |
| `tenant_type` | `VARCHAR(191)` | Tenant class/type (nullable) |
| `tenant_id` | `BIGINT UNSIGNED` | Tenant identifier (nullable) |
| `created_at` | `DATETIME` | Creation timestamp |
| `updated_at` | `DATETIME` | Last update timestamp |

A unique index on `(name, group, tenant_type, tenant_id)` ensures no duplicate settings per scope.

## Architecture

```
src/
├── AbstractSettings.php     # Base class for user-defined settings (extend this)
├── SettingsManager.php      # Manager: get(class, tenant?) with caching + save()
├── SettingsTenantInterface.php  # Interface for tenant objects
├── Settings/
│   ├── Models/              # ORM Record (Setting) + RecordManager (Settings)
│   ├── Dto/                 # SettingDto — low-level value object
│   ├── Enums/               # SettingType — typed enum with cast/encode
│   ├── Mapper/              # SettingMapper — DTO ↔ DB / array conversion
│   ├── Hydrator/            # SettingsHydrator — reflection-based property mapping
│   └── Storages/            # SettingStorageInterface, DatabaseStorage, FileStorage
├── AbstractBase/            # Base Record and Repository classes
├── ModelsRelated/           # Cross-cutting HasSettings traits
├── Utility/                 # SettingsModels (ModelFinder), PackageConfig
└── SettingsServiceProvider.php
```

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for recent changes.

## License

The MIT License (MIT). See [LICENSE](LICENSE).

---

## Inspiration

This package was inspired by and takes ideas from the following open-source projects:

- **[spatie/laravel-settings](https://github.com/spatie/laravel-settings)** — DTO-based settings with casts, migrations support, and per-repository design.
- **[akaunting/laravel-setting](https://github.com/akaunting/laravel-setting)** — Simple key-value settings with driver-based persistence.
- **[phemellc/yii2-settings](https://github.com/phemellc/yii2-settings)** — Group-based settings with ActiveRecord integration for Yii2.
- **[jbtronics/settings-bundle](https://github.com/jbtronics/settings-bundle)** — Attribute-driven settings with storage adapters and Symfony DI integration.
- **[ScriptingBeating/laravel-global-settings](https://github.com/ScriptingBeating/laravel-global-settings)** — Global application settings with a simple API.
- **[appstract/laravel-options](https://github.com/appstract/laravel-options)** — Fluent options/settings stored in the database.
