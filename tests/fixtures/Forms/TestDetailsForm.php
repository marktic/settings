<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Fixtures\Forms;

use Marktic\Settings\Bundle\Modules\Admin\Forms\Settings\DetailsForm;

/**
 * A DetailsForm subclass for use in unit tests.
 *
 * Overrides initialize() to skip the translator() call that adds the submit
 * button – which is not needed to test populateSettingsFromForm().
 * All form elements for the settings properties are still added via the
 * inherited initializeSettingsFields() method.
 */
class TestDetailsForm extends DetailsForm
{
    public function initialize(): void
    {
        $this->setMethod('post');
        $this->addHidden('_trigger', '_trigger');
        $this->getElement('_trigger')->setValue('edit');

        $this->setAttrib('id', 'mkt-settings-form');

        $this->initializeSettingsFields();
        // Button deliberately omitted to avoid translator() dependency in tests
    }
}
