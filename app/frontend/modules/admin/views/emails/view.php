<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model frontend\modules\admin\models\SentEmail */

$this->title = $model->emailid;
$this->params['breadcrumbs'][] = ['label' => 'Sent Emails', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sent-email-view">

    <h1 style="text-align: center"><?= Html::encode($this->title) ?></h1>

    <p align="right">
        <?= Html::a('Update', ['update', 'id' => $model->emailid], ['class' => 'btn btn-primary']) ?>

    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'emailid:email',
            'content:ntext',
            'email:email',
            'date',
        ],
    ]) ?>

</div>
