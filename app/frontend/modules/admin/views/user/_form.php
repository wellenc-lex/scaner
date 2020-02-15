<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\admin\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'rights')->textInput() ?>

    <?= $form->field($model, 'scans_counter')->textInput() ?>

    <?= $form->field($model, 'scan_timeout')->textInput() ?>

    <b href="#" data-toggle="tooltip" title="Status: 10 = On, 0 = Off">Status</b>
    <?= $form->field($model, 'status')->textInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
