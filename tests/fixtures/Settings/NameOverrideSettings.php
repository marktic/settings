<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Fixtures\Settings;

use Marktic\Settings\AbstractSettings;

/**
 * Tests NAME const override for group name.
 * Expected group: "custom_group"
 */
class NameOverrideSettings extends AbstractSettings
{
    public const NAME = 'custom_group';

    public string $title = 'Default Title';
}
