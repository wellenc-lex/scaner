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

    public function actionIndex()
    {
        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
        $auth = getenv('Authorization', 'Basic bmdpbng6QWRtaW4=');

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
    public function actionNmap()
    {
        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
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

                if ($scan->notify_instrument != "") {

                    $scan->notify_instrument = $scan->notify_instrument . ", Nmap";
                } else {
                    $scan->notify_instrument = "Nmap";
                }

                return $scan->save();
            }
            return 1;
        }
        return 0;
    }

    public function actionAmass()
    {
        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
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

                if ($scan->notify_instrument != "") {
                    $scan->notify_instrument = $scan->notify_instrument . ", amass";
                } else {
                    $scan->notify_instrument = "amass";
                }

                return $scan->save();
            }
            return 1;
        }
        return 0;
    }

    public function actionDirscan()
    {
        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
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

                if ($scan->notify_instrument != "") {

                    $scan->notify_instrument = $scan->notify_instrument . ", Dirscan";
                } else {
                    $scan->notify_instrument = "Dirscan";
                }

                return $scan->save();
            }
            return 1;
        }
        return 0;
    }

}
