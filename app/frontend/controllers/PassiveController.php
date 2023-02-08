<?php

namespace frontend\controllers;

use frontend\models\passive\Amass;
use frontend\models\passive\Dirscan;
use frontend\models\passive\Nmap;
use frontend\models\PassiveScan;
use frontend\models\ToolsAmount;
use frontend\models\Queue;
use Yii;
use yii\web\Controller;

/**
 * Passivescan controller
 */
class PassiveController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * Calls instruments for passive scanning
     */

    public static function actionIndex()
    {
        $secret = getenv('api_secret') ?: 'secretkeyzzzzcbv55';
        $auth = getenv('Authorization') ?: 'Basic bmdpbng6QWRtaW4=';

        $secretIN = Yii::$app->request->post('secret');

        if ($secret === $secretIN) {

            $allresults = PassiveScan::find()
                ->where(['is_active' => 1])
                ->andWhere(['!=', 'last_scan_monthday', date("d")])
                ->andWhere(['scanday' => date("d")])
                ->all();

            foreach ($allresults as $result) {    

                if ($result != NULL) {

                    if ($result->nmapDomain != "") {

                        $queue = new Queue();
                        $queue->passivescan = 1;
                        $queue->taskid = $result->PassiveScanid;
                        $queue->instrument = 1;
                        $queue->save();
                    }

                    if ($result->amassDomain != "") {

                        $queue = new Queue();
                        $queue->passivescan = 1;
                        $queue->taskid = $result->PassiveScanid;
                        $queue->amassdomain = $result->amassDomain;
                        $queue->instrument = 2;
                        $queue->save();
                    }

                    if ($result->dirscanUrl != "" && $result->dirscanIP != "") {

                        $queue = new Queue();
                        $queue->passivescan = 1;
                        $queue->taskid = $result->PassiveScanid;
                        $queue->instrument = 3;
                        $queue->dirscanUrl = $result->dirscanUrl;
                        $queue->dirscanIP = $result->dirscanIP;
                        $queue->save();

                    } elseif ($result->dirscanUrl != "") {

                        $queue = new Queue();
                        $queue->passivescan = 1;
                        $queue->taskid = $result->PassiveScanid;
                        $queue->instrument = 3;
                        $queue->dirscanUrl = $result->dirscanUrl;
                        $queue->save();
                    }

                    $result->last_scan_monthday = date("d");
                    $result->save(false);
                }

            }

            return 1;    
        }
        return 0;
    }

    //TODO:вызывать функцию diff, которая ищет и меняет отличия в прошлом и новом скане для всех инструментов и добавлять ее результаты в скан
    public static function actionNmap()
    {
        $secret = getenv('api_secret') ?: 'secretkeyzzzzcbv55';
        $model = new Nmap();

        $secretIN = Yii::$app->request->post('secret');

        if ($secretIN === $secret) {

            $nmap = $model::scanhost(Yii::$app->request->post());

            if ($nmap == 1) {
                //действия, если старый скан != новому

                $scanid = Yii::$app->request->post('scanid');

                $scan = PassiveScan::find()
                    ->where(['PassiveScanid' => $scanid])
                    ->limit(1)
                    ->one();

                $scan->needs_to_notify = 1;

                if ($scan->notify_instrument != "" && $scan->notify_instrument != "1") {

                    $scan->notify_instrument = $scan->notify_instrument . "1";
                } else {
                    $scan->notify_instrument = "1";
                }

                return $scan->save();
            }
            return 1;
        }
        return 0;
    }

    public static function actionAmass()
    {
        $secret = getenv('api_secret') ?: 'secretkeyzzzzcbv55';
        $model = new amass();

        $secretIN = Yii::$app->request->post('secret');

        if ($secretIN === $secret) {

            $amass = $model::amassscan(Yii::$app->request->post());

            if ($amass == 1) {
                //действия, если старый скан != новому -> notify user about changes
                //todo: slack/tg

                $scanid = Yii::$app->request->post('scanid');

                $scan = PassiveScan::find()
                    ->where(['PassiveScanid' => $scanid])
                    ->limit(1)
                    ->one();

                $scan->needs_to_notify = 1;

                if ($scan->notify_instrument != "" && $scan->notify_instrument != "2") {
                    $scan->notify_instrument = $scan->notify_instrument . "2";
                } else {
                    $scan->notify_instrument = "2";
                }

                return $scan->save();
            }
            return 1;
        }
        return 0;
    }

    public static function actionDirscan()
    {
        $secret = getenv('api_secret') ?: 'secretkeyzzzzcbv55';
        $model = new Dirscan();

        $secretIN = Yii::$app->request->post('secret');

        if ($secretIN === $secret) {

            $dirscan = $model::dirscan(Yii::$app->request->post());

            if ($dirscan == 1) {
                //действия, если старый скан != новому

                $scanid = Yii::$app->request->post('scanid');

                $scan = PassiveScan::find()
                    ->where(['PassiveScanid' => $scanid])
                    ->limit(1)
                    ->one();

                $scan->needs_to_notify = 1;

                if ($scan->notify_instrument != "" && $scan->notify_instrument != "3") {

                    $scan->notify_instrument = $scan->notify_instrument . "3";
                } else {
                    $scan->notify_instrument = "3";
                }

                return $scan->save();
            }
            return 1;
        }
        return 0;
    }

    public static function actionScanall()
    {
        $secret = getenv('api_secret') ?: 'secretkeyzzzzcbv55';
        $auth = getenv('Authorization') ?: 'Basic bmdpbng6QWRtaW4=';

        $secretIN = 'secretkeyzzzzcbv55';//Yii::$app->request->get('secret');

        if ($secret === $secretIN) {

            $allresults = PassiveScan::find()
                ->where(['is_active' => 1])
                ->all();

            foreach ($allresults as $result) {    

                if ($result != NULL) {

                    if ($result->nmapDomain != "") {

                        $queue = new Queue();
                        $queue->passivescan = 1;
                        $queue->taskid = $result->PassiveScanid;
                        $queue->instrument = 1;
                        $queue->save();
                    }

                    if ($result->amassDomain != "") {

                        $queue = new Queue();
                        $queue->passivescan = 1;
                        $queue->taskid = $result->PassiveScanid;
                        $queue->amassdomain = $result->amassDomain;
                        $queue->instrument = 2;
                        $queue->save();
                    }

                    if ($result->dirscanUrl != "" && $result->dirscanIP != "") {

                        $queue = new Queue();
                        $queue->passivescan = 1;
                        $queue->taskid = $result->PassiveScanid;
                        $queue->instrument = 3;
                        $queue->dirscanUrl = $result->dirscanUrl;
                        $queue->dirscanIP = $result->dirscanIP;
                        $queue->save();

                    } elseif ($result->dirscanUrl != "") {

                        $queue = new Queue();
                        $queue->passivescan = 1;
                        $queue->taskid = $result->PassiveScanid;
                        $queue->instrument = 3;
                        $queue->dirscanUrl = $result->dirscanUrl;
                        $queue->save();
                    }

                    $result->last_scan_monthday = $result->scanday;
                    $result->save(false);
                }

            }

            return 1;    
        }
        return 0;
    }

}
