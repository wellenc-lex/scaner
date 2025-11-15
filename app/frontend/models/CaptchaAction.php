<?php

namespace frontend\models;

use Yii;
use yii\captcha\CaptchaAction as CaptchaActionBase;

class CaptchaAction extends CaptchaActionBase
{
    public function validate($input, $caseSensitive)
    {
        // Skip validation on AJAX requests, as it expires the captcha.
        if (Yii::$app->request->isAjax) {
            return true;
        }
        return parent::validate($input, $caseSensitive);
    }
}