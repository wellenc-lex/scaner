<?php

namespace frontend\models;

use yii\base\Model;


class Profile extends Model
{

    public $scanid;
    public $email;
    public $verifyCode;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'verifyCode' => 'Verification Code',
        ];
    }

}