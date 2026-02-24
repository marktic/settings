<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Fixtures\Settings;

use Marktic\Settings\AbstractSettings;

/**
 * Tests auto-derivation of group from class name (no group() override, no NAME const).
 * Expected group: "auto_derived"
 */
class AutoDerivedSettings extends AbstractSettings
{
    public string $color = 'blue';
}
