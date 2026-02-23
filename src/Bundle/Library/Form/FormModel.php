<?php

declare(strict_types=1);

namespace Marktic\Settings\Bundle\Library\Form;

abstract class FormModel extends \Nip\Form\FormModel
{
    public function initialize()
    {
        parent::initialize();

        $this->setMethod('post');
        $this->addHidden('_trigger', '_trigger');
        $this->getElement('_trigger')->setValue('edit');

        $this->setRendererType('bootstrap5');
        $this->addClass('form-horizontal');
        $this->addClass('row-mb-3');
    }

    /**
     * {@inheritDoc}
     */
    public function submited()
    {
        if (false === parent::submited()) {
            return false;
        }

        return isset($_REQUEST['_trigger']) && $_REQUEST['_trigger'] == $this->getElement('_trigger')->getValue();
    }
}
