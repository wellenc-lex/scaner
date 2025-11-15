<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model frontend\modules\admin\models\User */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">

    <h1 style="text-align: center"><?= Html::encode($this->title) ?></h1>

    <p align="right">
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'auth_key',
                'password_hash',
                'password_reset_token',
                'email:email',
                'status',
                'rights',
                'created_at',
                'updated_at',
                'scans_counter',
                'scan_timeout',
            ],
        ]) ?>
</div>
