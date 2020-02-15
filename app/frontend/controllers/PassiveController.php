<?php

namespace frontend\controllers;

use frontend\models\passive\Amass;
use frontend\models\passive\Dirscan;
use frontend\models\passive\Nmap;
use frontend\models\PassiveScan;
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

        $secretIN = Yii::$app->request->post('secret');

        if ($secret === $secretIN) {

            $result = PassiveScan::find()
                ->where(['is_active' => 1])
                ->andWhere(['!=', 'last_scan_monthday', date("d")])
                ->andWhere(['scanday' => date("N")])
                ->one();

            if ($result != NULL) {

                if ($result->nmapDomain != "") {
                    exec('curl --insecure  -H \'Authorization: Basic bmdpbng6U25pcGVydWx0cmEx\' --data "url=' . $result->nmapDomain . '& scanid=' . $result->scanid . '& secret=' . $secret . '" https://dev.localhost.soft/passive/nmap > /dev/null 2>/dev/null &');
                }

                if ($result->amassDomain != "") {
                    exec('curl --insecure  -H \'Authorization: Basic bmdpbng6U25pcGVydWx0cmEx\' --data "url=' . $result->amassDomain . '& scanid=' . $result->scanid . '& secret=' . $secret . '" https://dev.localhost.soft/passive/amass > /dev/null 2>/dev/null &');
                }

                if ($result->dirscanUrl != "") {
                    exec('curl --insecure  -H \'Authorization: Basic bmdpbng6U25pcGVydWx0cmEx\' --data "url=' . $result->dirscanUrl . '& scanid=' . $result->scanid . '& secret=' . $secret . '" https://dev.localhost.soft/passive/dirscan > /dev/null 2>/dev/null &');
                }

                $result->last_scan_monthday = date("d");
                $result->save(false);

            }

            return 1;
        }
        return 0;
    }

    //TODO:вызывать функцию diff, которая ищет и меняет отличия в прошлом и новом скане для всех инструментов
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
                    ->where(['scanid' => $scanid])
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
                //действия, если старый скан != новому

                $scanid = Yii::$app->request->post('scanid');

                $scan = PassiveScan::find()
                    ->where(['scanid' => $scanid])
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
                    ->where(['scanid' => $scanid])
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

    public function actionVhost()
    {
        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
        $model = new Vhostscan();

        $secretIN = Yii::$app->request->post('secret');

        if ($secretIN === $secret) {

            $vhost = $model::vhostscan(Yii::$app->request->post());

            if ($vhost == 1) {
                //действия, если старый скан != новому

                $scanid = Yii::$app->request->post('scanid');

                $scan = PassiveScan::find()
                    ->where(['scanid' => $scanid])
                    ->limit(1)
                    ->one();

                $scan->needs_to_notify = 1;

                if ($scan->notify_instrument != "") {

                    $scan->notify_instrument = $scan->notify_instrument . ", Vhostscan";
                } else {
                    $scan->notify_instrument = "Vhostscan";
                }

                return $scan->save();
            }
            return 1;
        }
        return 0;
    }


}
