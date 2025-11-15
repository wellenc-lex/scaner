<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\admin\models\PassiveScan */

$this->title = 'Update Passive Scan: ' . $model->scanid;
$this->params['breadcrumbs'][] = ['label' => 'Passive Scans', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->scanid, 'url' => ['view', 'id' => $model->scanid]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="passive-scan-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
