<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\admin\models\PassiveScan */

$this->title = 'Create Passive Scan';
$this->params['breadcrumbs'][] = ['label' => 'Passive Scans', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="passive-scan-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
