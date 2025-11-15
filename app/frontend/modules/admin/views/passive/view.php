<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model frontend\modules\admin\models\PassiveScan */

$this->title = $model->scanid;
$this->params['breadcrumbs'][] = ['label' => 'Passive Scans', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="passive-scan-view">

    <h1 style="text-align: center"><?= Html::encode($this->title) ?></h1>

    <p align="right">
        <?= Html::a('Update', ['update', 'id' => $model->scanid], ['class' => 'btn btn-primary']) ?>
    </p>

    <div style='max-width: 100%; white-space: normal; overflow: scroll;'>
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'scanid',
                'userid',
                'scanday',
                'dirscanUrl:ntext',
                'amassDomain:ntext',
                'nmapDomain:ntext',
                'amass_previous:ntext',
                'amass_new:ntext',
                'nmap_previous:ntext',
                'nmap_new:ntext',
                'dirscan_previous:ntext',
                'dirscan_new:ntext',
                'is_active',
                'user_notified',
                'needs_to_notify',
                'notify_instrument',
                'last_scan_monthday',
            ],
        ]) ?>
    </div>
</div>
