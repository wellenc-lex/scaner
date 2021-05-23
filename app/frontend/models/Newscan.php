<?php

namespace frontend\models;

use yii\base\Model;

class Newscan extends Model
{
    public $url;
    public $agreed;
    public $verifyCode;

    public $nmapDomain;
    public $amassDomain;
    public $dirscanUrl;
    public $dirscanIp;

    public $gitUrl;
    public $raceUrl;
    public $raceCookies;
    public $raceBody;
    public $raceHeaders;
    public $vhostDomain;
    public $vhostIp;
    public $vhostPort;
    public $vhostSsl;
    public $reverseip;

    public $ips;

    public $activescan;
    public $passivescan;
    public $notify;
    public $passive;

    public $gitPassiveUrl;
    public $gitCompany;

    public function rules()
    {
        return [

            [['nmapDomain'], 'string', 'length' => [5, 5555]],
            [['nmapDomain'], 'match', 'pattern' => '/^([a-zA-Z0-9, \.\-\/\:]+)$/'],

            [['amassDomain'], 'string', 'length' => [5, 255]],
            [['amassDomain'], 'match', 'pattern' => '/^([a-zA-Z0-9, \.\-\/\:]+)$/'],

            [['dirscanUrl'], 'string', 'length' => [5, 255]],
            [['dirscanUrl'], 'match', 'pattern' => '/^([a-zA-Z0-9\.\-\/\:]+)$/'],

            [['dirscanIp'], 'string', 'length' => [5, 255]],
            [['dirscanIp'], 'ip'],

            [['gitUrl'], 'string', 'length' => [5, 255]],
            [['gitUrl'], 'match', 'pattern' => '/^(https:\/\/github\.com\/)+([a-zA-Z0-9-]+\/)+([a-zA-Z0-9-]+|[a-zA-Z0-9-]+\/)$/'],

            [['gitPassiveUrl'], 'match', 'pattern' => '/^(https:\/\/github\.com\/)+([a-zA-Z0-9-]+\/)+([a-zA-Z0-9-]+|[a-zA-Z0-9-]+\/)$/'],
            [['gitCompany'], 'match', 'pattern' => '/^(https:\/\/github\.com\/)+([a-zA-Z0-9]+)$/'],

            [['raceUrl'], 'string', 'length' => [5, 255]],
            [['raceUrl'], 'match', 'pattern' => '/^([a-zA-Z0-9 \.\-\/\:]+)$/'],

            [['raceCookies'], 'string', 'length' => [3, 20000]],
            [['raceCookies'], 'match', 'pattern' => '/^([a-zA-Z0-9=,_ %\.\-\/\:;]+)$/'],

            [['raceHeaders'], 'string', 'length' => [3, 1000]],
            [['raceHeaders'], 'match', 'pattern' => '/^([a-zA-Z0-9= ,_ %\.\-\/\:;\-/().]+)$/'],

            [['raceBody'], 'string', 'length' => [3, 20000]],
            [['raceBody'], 'match', 'pattern' => '/^([a-zA-Z0-9,=  %&\.\-\/\:;]+)$/'],

            [['vhostDomain'], 'string'],
            [['vhostDomain'], 'match', 'pattern' => '/^([a-zA-Z0-9\.,\-\/\r\n]+)$/'],

            [['vhostIp'], 'string'],
            [['vhostIp'], 'match', 'pattern' => '/^([a-zA-Z0-9, \.\-\/\r\n]+)$/'],

            [['vhostPort'], 'string'],
            [['vhostPort'], 'match', 'pattern' => '/^([0-9\.\:\r\n]+)$/'],

            [['vhostSsl'], 'boolean'],

            [['ips'], 'string', 'length' => [5, 255]],
            [['ips'], 'match', 'pattern' => '/^([a-zA-Z0-9\.\-\/\:]+)$/'],

            [['reverseip'], 'ip'],

            [['agreed'], 'required'],
            [['activescan'], 'boolean'],
            [['passivescan'], 'boolean'],
            [['notify'], 'boolean'],
            [['passive'], 'boolean'],
            [['activescan'], 'required'],
            [['passivescan'], 'required'],
            //['verifyCode', 'captcha'],

        ];
    }

    public function attributeLabels()
    {
        return [
            'verifyCode' => 'Verification Code',
        ];
    }

}