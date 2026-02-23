# Marktic Settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/marktic/settings.svg?style=flat-square)](https://packagist.org/packages/marktic/settings)
[![Tests](https://img.shields.io/github/actions/workflow/status/marktic/settings/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/marktic/settings/actions/workflows/run-tests.yml)
[![License](https://img.shields.io/packagist/l/marktic/settings.svg?style=flat-square)](https://packagist.org/packages/marktic/settings)

A multi-tenant settings management package for PHP 8.2+ applications. Supports typed values (string, JSON, integer, float, boolean), grouped settings, tenant-scoped configuration, and multiple storage adapters (database, cache file).

## Features

- **Typed settings**: Store values as `string`, `json`, `integer`, `float`, or `boolean` with automatic casting on retrieval
- **Grouped settings**: Organize settings by logical group names
- **Multi-tenant support**: Scope settings to any tenant via `tenant_type` + `tenant_id`
- **DTO-based**: Settings are represented as `SettingDto` objects with typed properties and default values
- **Mapper**: `SettingMapper` converts between `SettingDto` and database/array representations
- **Multiple adapters**: Persist settings to a relational database (`DatabaseAdapter`) or a JSON cache file (`CacheFileAdapter`)
- **Trait-based integration**: Use `HasSettingsRecordTrait` to add setting capabilities to any model
- **Timestamps**: Every setting tracks `created_at` and `updated_at`

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

### Basic Usage with the Database Adapter

```php
use Marktic\Settings\Settings\Adapters\DatabaseAdapter;
use Marktic\Settings\Settings\Dto\SettingDto;
use Marktic\Settings\Settings\Enums\SettingType;
use Marktic\Settings\Settings\Mapper\SettingMapper;
use Marktic\Settings\Utility\SettingsModels;

// Get adapter via the model finder
$adapter = SettingsModels::createDatabaseAdapter();

// Create a setting
$dto = new SettingDto();
$dto->name = 'site.title';
$dto->group = 'general';
$dto->type = SettingType::String;
$dto->setValue('My Application');

$adapter->save($dto);

// Retrieve a setting
$setting = $adapter->find('site.title', 'general');
echo $setting->getCastValue(); // "My Application"
```

### JSON Settings

```php
$dto = new SettingDto();
$dto->name = 'features.flags';
$dto->group = 'app';
$dto->type = SettingType::Json;
$dto->setValue(['dark_mode' => true, 'beta' => false]);

$adapter->save($dto);

$setting = $adapter->find('features.flags', 'app');
$flags = $setting->getCastValue(); // ['dark_mode' => true, 'beta' => false]
```

### Tenant-Scoped Settings

```php
$dto = new SettingDto();
$dto->name = 'dashboard.theme';
$dto->group = 'ui';
$dto->type = SettingType::String;
$dto->tenantType = 'App\\Models\\Organization';
$dto->tenantId = 42;
$dto->setValue('dark');

$adapter->save($dto);

// Retrieve for specific tenant
$setting = $adapter->find('dashboard.theme', 'ui', 'App\\Models\\Organization', 42);
```

### Cache File Adapter

```php
use Marktic\Settings\Settings\Adapters\CacheFileAdapter;
use Marktic\Settings\Settings\Mapper\SettingMapper;

$adapter = new CacheFileAdapter('/path/to/settings.json', new SettingMapper());

$dto = new SettingDto();
$dto->name = 'maintenance.mode';
$dto->group = 'system';
$dto->type = SettingType::Boolean;
$dto->setValue(false);

$adapter->save($dto);
```

### Using HasSettingsRecordTrait in a Model

```php
use Marktic\Settings\ModelsRelated\HasSettings\HasSettingsRecordTrait;
use Marktic\Settings\Settings\Enums\SettingType;

class Organization extends \Nip\Records\Record
{
    use HasSettingsRecordTrait;

    public function getSettingTenantId(): string|int|null
    {
        return $this->id;
    }
}

$org = Organization::find(1);

// Store a setting
$org->setSetting('billing.currency', 'EUR', 'billing', SettingType::String);

// Retrieve a setting value (auto-cast)
$currency = $org->getSettingValue('billing.currency', 'billing'); // "EUR"

// Get all settings in a group
$billingSettings = $org->getSettingsByGroup('billing');
```

### DTO Structure

```php
$dto = new SettingDto();
$dto->id;           // ?int — null for new settings
$dto->name;         // string — setting key, e.g. "site.title"
$dto->group;        // string — logical group, default: "default"
$dto->value;        // string — raw stored value
$dto->type;         // SettingType — determines cast on retrieval
$dto->tenantType;   // ?string — tenant class/type identifier
$dto->tenantId;     // string|int|null — tenant identifier
$dto->createdAt;    // ?\DateTimeImmutable
$dto->updatedAt;    // ?\DateTimeImmutable

// Helper methods
$dto->getCastValue();         // returns the value cast to the proper PHP type
$dto->setValue(mixed $value); // encodes any PHP value to the raw string representation
```

### SettingType Enum

| Case | Stored As | Cast To |
|------|-----------|---------|
| `SettingType::String` | `"string"` | `string` |
| `SettingType::Json` | `"json"` | `array` (via `json_decode`) |
| `SettingType::Integer` | `"integer"` | `int` |
| `SettingType::Float` | `"float"` | `float` |
| `SettingType::Boolean` | `"boolean"` | `bool` |

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
├── Settings/
│   ├── Models/          # ORM Record (Setting) + RecordManager (Settings)
│   ├── Dto/             # SettingDto — plain value object
│   ├── Enums/           # SettingType — typed enum with cast/encode
│   ├── Mapper/          # SettingMapper — DTO ↔ DB / array conversion
│   └── Adapters/        # SettingAdapterInterface, DatabaseAdapter, CacheFileAdapter
├── AbstractBase/        # Base Record and Repository classes
├── ModelsRelated/       # Cross-cutting HasSettings traits
├── Utility/             # SettingsModels (ModelFinder), PackageConfig
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
