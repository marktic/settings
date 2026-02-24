<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Fixtures\Settings;

use Marktic\Settings\AbstractSettings;

/**
 * Tests NAMESPACE const for settings namespace.
 * Expected group: "namespaced" (auto-derived), namespace: "mymodule"
 */
class NamespacedSettings extends AbstractSettings
{
    public const NAMESPACE = 'mymodule';

    public string $mode = 'production';
}
