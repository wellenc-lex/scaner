<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\admin\models\SentEmail */

$this->title = 'Update Sent Email: ' . $model->emailid;
$this->params['breadcrumbs'][] = ['label' => 'Sent Emails', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->emailid, 'url' => ['view', 'id' => $model->emailid]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sent-email-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
