<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tasks';
?>

<style>

    ::-webkit-scrollbar {
        height: 7px;
        background-color: rgba(255, 255, 255, 0);
        margin-top: 2px;
    }

    ::-webkit-scrollbar-track,
    ::-webkit-scrollbar-thumb {
        border: 4px solid rgba(255, 255, 255, 0);
        background-clip: padding-box;
    }

    ::-webkit-scrollbar-track {
        background-color: #ccc;
    }

    ::-webkit-scrollbar-thumb {
        background-color: #212121;
    }

    ::-webkit-scrollbar-thumb:hover {
        border: 3px solid rgba(255, 255, 255, 0);
    }


</style>

<nav class="navbar navbar-inverse">
    <ul class="nav navbar-nav">
        <li><a href="/admin">Users</a></li>
        <li class="active"><a href="#">Active scans</a></li>
        <li><a href="/admin/passive">Passive scans</a></li>
        <li><a href="/admin/emails">Sent emails</a></li>
    </ul>
</nav>
<div class="tasks-index">

    <h1 style="text-align: center"><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>

    <div style="width: 110%; margin-left: -5%">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'emptyCell' => '-',
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                //'contentOptions' => ['style'=>'max-width: 100px; white-space: normal; overflow: hidden;'],
                'taskid',
                'userid',
                'status',
                [
                    'attribute' => 'host',
                    'contentOptions' => ['style' => 'max-width: 400px; overflow:auto;'],
                ],

                'nmap_status',
                'amass_status',
                'dirscan_status',
                'gitscan_status',
                'ips_status',
                'date',
                'notified',
                'hidden',
                'notify_instrument',

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update} {delete}',
                ],
            ],
        ]); ?>
    </div>
    <?php Pjax::end(); ?>
</div>
