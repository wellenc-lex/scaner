<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Passive Scans';
?>
<nav class="navbar navbar-inverse">
    <ul class="nav navbar-nav">
        <li><a href="/admin">Users</a></li>
        <li><a href="/admin/tasks">Active scans</a></li>
        <li class="active"><a href="#">Passive scans</a></li>
        <li><a href="/admin/emails">Sent emails</a></li>

    </ul>
</nav>
<div class="passive-scan-index">

    <h1 style="text-align: center"><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>

    <p>
        <?= Html::a('Create Passive Scan', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'scanid',
            'userid',
            'dirscanUrl:ntext',
            'amassDomain:ntext',
            'nmapDomain:ntext',
            'is_active',
            'user_notified',
            'needs_to_notify',
            'scanday',
            'last_scan_monthday',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
