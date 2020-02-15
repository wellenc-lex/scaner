<?php

namespace frontend\modules\admin\models;

/**
 * This is the model class for table "sent_email".
 *
 * @property int $emailid
 * @property string $content
 * @property string $email
 * @property string $date
 */
class SentEmail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sent_email';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content', 'email', 'date'], 'required'],
            [['content'], 'string'],
            [['date'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            [['email'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'emailid' => 'Emailid',
            'content' => 'Email content',
            'email' => 'Email address',
            'date' => 'Date',
        ];
    }
}
