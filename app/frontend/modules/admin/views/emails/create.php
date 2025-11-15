<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\admin\models\SentEmail */

$this->title = 'Create Sent Email';
$this->params['breadcrumbs'][] = ['label' => 'Sent Emails', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sent-email-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
