<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sent Emails';
?>
<nav class="navbar navbar-inverse">
    <ul class="nav navbar-nav">
        <li><a href="/admin">Users</a></li>
        <li><a href="/admin/tasks">Active scans</a></li>
        <li><a href="/admin/passive">Passive scans</a></li>
        <li class="active"><a href="#">Sent emails</a></li>

    </ul>
</nav>
<div class="sent-email-index">

    <h1 style="text-align: center"><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'emailid:email',
            'content:ntext',
            'email:email',
            'date',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
