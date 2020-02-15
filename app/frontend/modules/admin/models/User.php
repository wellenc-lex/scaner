<?php

namespace frontend\modules\admin\models;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property int $status
 * @property int $rights
 * @property int $created_at
 * @property int $updated_at
 * @property int $scans_counter
 * @property int $email_notify
 * @property int $scan_timeout
 *
 * @property Log[] $logs
 * @property PassiveScan[] $passiveScans
 * @property Tasks[] $tasks
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['auth_key', 'password_hash', 'email', 'created_at'], 'required'],
            [['status', 'rights', 'created_at', 'updated_at', 'scans_counter'], 'integer'],
            [['scan_timeout'], 'integer'],
            [['auth_key'], 'string', 'max' => 32],
            [['password_hash', 'password_reset_token', 'email'], 'string', 'max' => 255],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'rights' => 'Rights',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'scans_counter' => 'Scans Counter',
            'scan_timeout' => 'Scan Timeout',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogs()
    {
        return $this->hasMany(Log::className(), ['userid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPassiveScans()
    {
        return $this->hasMany(PassiveScan::className(), ['userid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Tasks::className(), ['userid' => 'id']);
    }
}
