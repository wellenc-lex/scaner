<?php

namespace frontend\modules\admin\models;

/**
 * This is the model class for table "passive_scan".
 *
 * @property int $scanid
 * @property int $userid
 * @property int $scanday
 * @property string $dirscanUrl
 * @property string $amassDomain
 * @property string $nmapDomain
 * @property string $vhostDomain
 * @property string $amass_previous
 * @property string $amass_new
 * @property string $nmap_previous
 * @property string $nmap_new
 * @property string $dirscan_previous
 * @property string $dirscan_new
 * @property string $vhost_previous
 * @property string $vhost_new
 * @property int $is_active
 * @property int $user_notified
 * @property int $needs_to_notify
 * @property string $notify_instrument
 * @property int $last_scan_monthday
 *
 * @property User $user
 */
class PassiveScan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'passive_scan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userid', 'scanday'], 'required'],
            [['userid', 'scanday', 'last_scan_monthday'], 'integer'],
            [['is_active', 'user_notified', 'needs_to_notify'], 'boolean'],
            [['dirscanUrl', 'amassDomain', 'nmapDomain', 'amass_previous', 'amass_new', 'nmap_previous', 'nmap_new', 'dirscan_previous', 'dirscan_new', 'vhost_previous', 'vhost_new'], 'string'],
            [['vhostDomain', 'notify_instrument'], 'string', 'max' => 255],
            [['userid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['userid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'scanid' => 'Scanid',
            'userid' => 'Userid',
            'scanday' => 'Scanday',
            'dirscanUrl' => 'Dirscan Url',
            'amassDomain' => 'amass Domain',
            'nmapDomain' => 'Nmap Domain',
            'vhostDomain' => 'Vhost Domain',
            'amass_previous' => 'amass Previous',
            'amass_new' => 'amass New',
            'nmap_previous' => 'Nmap Previous',
            'nmap_new' => 'Nmap New',
            'dirscan_previous' => 'Dirscan Previous',
            'dirscan_new' => 'Dirscan New',
            'vhost_previous' => 'Vhost Previous',
            'vhost_new' => 'Vhost New',
            'is_active' => 'Is Active',
            'user_notified' => 'User Notified',
            'needs_to_notify' => 'Needs To Notify',
            'notify_instrument' => 'Notify Instrument',
            'last_scan_monthday' => 'Last Scan Monthday',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'userid']);
    }
}
