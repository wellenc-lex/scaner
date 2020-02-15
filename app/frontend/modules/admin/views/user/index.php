<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
?>
<nav class="navbar navbar-inverse">
    <ul class="nav navbar-nav">
        <li class="active"><a href="#">Users</a></li>
        <li><a href="/admin/tasks">Active scans</a></li>
        <li><a href="/admin/passive">Passive scans</a></li>
        <li><a href="/admin/emails">Sent emails</a></li>
    </ul>
</nav>

<div class="user-index">

    <h1 style="text-align: center"><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>

    <p>
        <?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'email:email',
            'rights',
            'scans_counter',
            'created_at:date',
            [
                'attribute' => 'status',
                'value' => function ($data) {
                    if ($data->status = 10)
                        return "On";
                    else
                        return "Off";
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
