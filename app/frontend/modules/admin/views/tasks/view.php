<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model frontend\modules\admin\models\Tasks */

$this->title = $model->taskid;
$this->params['breadcrumbs'][] = ['label' => 'Tasks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tasks-view">

    <h1 style="text-align: center"><?= Html::encode($this->title) ?></h1>

    <p align="right">
        <?= Html::a('Update', ['update', 'id' => $model->taskid], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->taskid], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <div style='max-width: 100%; white-space: normal; overflow: scroll;'>
    <?= DetailView::widget([
        'model' => $model,

        'attributes' => [
            'taskid',
            'userid',
            'status',
            'host',
            'nmap:ntext',
            'amass:ntext',
            'dirscan:ntext',
            'gitscan:ntext',
            'ips:ntext',
            'nmap_status',
            'amass_status',
            'dirscan_status',
            'gitscan_status',
            'ips_status',
            'date',
            'notified',
        ],
    ]) ?>
    </div>

</div>
