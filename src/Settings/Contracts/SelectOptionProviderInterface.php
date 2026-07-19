<?php

declare(strict_types=1);

namespace Marktic\Settings\Settings\Contracts;

/**
 * Implement this interface on a backed enum to provide human-readable labels
 * for its cases when rendered as a select field.
 *
 * Enums that do not implement this interface will use the case name as the label.
 */
interface SelectOptionProviderInterface
{
    public function label(): string;
}
