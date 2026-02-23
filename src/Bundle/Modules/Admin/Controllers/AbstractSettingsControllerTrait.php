<?php

declare(strict_types=1);

namespace Marktic\Settings\Bundle\Modules\Admin\Controllers;

use Marktic\Settings\Utility\ViewUtility;
use Nip\Controllers\Response\ResponsePayload;
use Nip\View\View;

/**
 * @method ResponsePayload payload()
 */
trait AbstractSettingsControllerTrait
{
    /**
     * Register view paths for the settings package.
     */
    public function registerViewPaths(View $view): void
    {
        parent::registerViewPaths($view);

        ViewUtility::registerAdminPaths($view);
    }
}
