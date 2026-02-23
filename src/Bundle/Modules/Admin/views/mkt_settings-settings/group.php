<?php

/** @var \Marktic\Settings\AbstractSettings $settings */
/** @var \Marktic\Settings\Bundle\Modules\Admin\Forms\Settings\DetailsForm $form */

?>

<?= $this->Flash()->render($this->controller); ?>

<div class="d-grid gap-3">
    <div class="row">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <?= htmlspecialchars(translator()->trans('mkt_settings-settings.labels.title')); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?= $form->render(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
