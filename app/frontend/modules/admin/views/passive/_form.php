<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\admin\models\PassiveScan */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="passive-scan-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'userid')->textInput() ?>

    <?= $form->field($model, 'scanday')->textInput() ?>

    <?= $form->field($model, 'dirscanUrl')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'amassDomain')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'nmapDomain')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'amass_previous')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'amass_new')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'nmap_previous')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'nmap_new')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'dirscan_previous')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'dirscan_new')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'is_active')->textInput() ?>

    <?= $form->field($model, 'user_notified')->textInput() ?>

    <?= $form->field($model, 'needs_to_notify')->textInput() ?>

    <?= $form->field($model, 'notify_instrument')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'last_scan_monthday')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
