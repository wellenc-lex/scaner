<?php

namespace frontend\modules\admin\models;

/**
 * This is the model class for table "tasks".
 *
 * @property int $taskid
 * @property int $userid
 * @property string $status
 * @property string $host
 * @property string $nmap
 * @property string $amass
 * @property string $dirscan
 * @property string $vhost
 * @property string $gitscan
 * @property string $ips
 * @property string $nmap_status
 * @property string $amass_status
 * @property string $dirscan_status
 * @property string $gitscan_status
 * @property string $vhost_status
 * @property string $ips_status
 * @property string $date
 * @property int $notified
 *
 * @property Log[] $logs
 * @property User $user
 */
class Tasks extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tasks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userid'], 'required'],
            [['userid', 'notified'], 'integer'],
            [['nmap', 'amass', 'dirscan', 'vhost', 'gitscan', 'ips'], 'string'],
            [['date'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            [['status', 'nmap_status', 'amass_status', 'dirscan_status', 'gitscan_status', 'ips_status'], 'string', 'max' => 20],
            [['host', 'vhost_status'], 'string', 'max' => 255],
            [['userid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['userid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'taskid' => 'Taskid',
            'userid' => 'Userid',
            'status' => 'Status',
            'host' => 'Host',
            'nmap' => 'Nmap',
            'amass' => 'amass',
            'dirscan' => 'Dirscan',
            'vhost' => 'Vhost',
            'gitscan' => 'Gitscan',
            'ips' => 'Ips',
            'nmap_status' => 'Nmap Status',
            'amass_status' => 'amass Status',
            'dirscan_status' => 'Dirscan Status',
            'gitscan_status' => 'Gitscan Status',
            'vhost_status' => 'Vhost Status',
            'ips_status' => 'Ips Status',
            'date' => 'Date',
            'notified' => 'Notified',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogs()
    {
        return $this->hasMany(Log::className(), ['taskid' => 'taskid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'userid']);
    }
}
