<?php

namespace frontend\controllers;

use frontend\models\Amass;
use frontend\models\Dirscan;
use frontend\models\Gitscan;
use frontend\models\Ipscan;
use frontend\models\Nmap;
use frontend\models\passive\GitscanPassive;
use frontend\models\PassiveScan;
use frontend\models\Reverseip;
use frontend\models\Tasks;
use frontend\models\Vhost;
use Yii;
use yii\web\Controller;


/**
 * Scan controller
 */
class ScanController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $enableCsrfValidation = false;

    /**
     * Output active scan result
     */
    public function actionScanresult($id)
    {

        if (!Yii::$app->user->isGuest) {

            $result = Tasks::find()
                ->where(['taskid' => $id])
                ->limit(1)
                ->one();

            if (Yii::$app->user->id === $result['userid']) {

                $host = $result['host'];

                $js = $result['js'];
                $nmap = $result['nmap'];
                $amass = $result['amass'];
                $aquatone = $result['aquatone'];
                $gitscan = $result['gitscan'];
                $dirscan = $result['dirscan'];
                $ipscan = $result['ips'];
                $vhost = $result['vhost'];
                $reverseip = $result['reverseip'];
                $wayback = $result['wayback'];
                $subtakeover = $result['subtakeover'];

                return $this->render('scanresult', compact('nmap', 'amass', 'aquatone','dirscan', 'gitscan', 'ipscan', 'host', 'vhost', 'js', 'reverseip', 'wayback', 'subtakeover'));
            } else {
                Yii::$app->session->setFlash('error', 'This scan doesnt belong to you.');
                return $this->redirect(['/site/profile']);
            }
        }

    }

    /**
     * Output passive scan result
     */

    public function actionPassivescanresult($id)
    {

        if (!Yii::$app->user->isGuest) {

            $result = PassiveScan::find()
                ->where(['PassiveScanid' => $id])
                ->limit(1)
                ->one();

            if (Yii::$app->user->id === $result['userid']) {

                $aquatone = 0;
                $host = 0;
                $vhost = 0;
                $js = 0;
                $reverseip = 0;

                $nmap = $result['nmap_new'];
                $amass = $result['amass_new'];
                $dirscan = $result['dirscan_new'];

                $result->viewed = 1;
                $result->needs_to_notify = 0;
                $result->save();

                return $this->render('scanresult', compact('nmap', 'amass', 'dirscan', 'gitscan', 'aquatone', 'host', 'vhost', 'js', 'reverseip'));
            }
        }

    }

    public function actionGitpassivescanresult($id)
    {

        if (!Yii::$app->user->isGuest) {

            $result = GitscanPassive::find()
                ->where(['PassiveScanid' => $id])
                ->limit(1)
                ->one();

            if (Yii::$app->user->id === $result['userid']) {

                $aquatone = 0;
                $host = 0;
                $vhost = 0;
                $js = 0;
                $reverseip = 0;
                $nmap = 0;
                $amass = 0;
                $dirscan = 0;

                $gitscan = $result['gitscan_new'];

                $result->viewed = 1;
                $result->save();

                return $this->render('scanresult', compact('nmap', 'amass', 'dirscan', 'gitscan', 'aquatone', 'host', 'vhost', 'js', 'reverseip'));
            }
        }

    }


    public function actionDelete()
    {

        if (!Yii::$app->user->isGuest) {

            $scanid = Yii::$app->request->post('scanid');

            $task = Tasks::find()
                ->where(['taskid' => $scanid])
                ->limit(1)
                ->one();

            if (Yii::$app->user->id === $task['userid']) {

                Yii::$app->response->statusCode = 200;
                return $task->delete();

            } else Yii::$app->response->statusCode = 403;
        } else Yii::$app->response->statusCode = 403;
    }


    public function actionHide()
    {

        //0=hide, 1 == unhide
        if (!Yii::$app->user->isGuest) {

            $action = Yii::$app->request->post('action');
            $scanid = Yii::$app->request->post('scanid');

            $task = Tasks::find()
                ->where(['taskid' => $scanid])
                ->limit(1)
                ->one();

            if (Yii::$app->user->id === $task['userid']) {

                if ($action == 0) {
                    $task->hidden = 1;
                    Yii::$app->response->statusCode = 200;
                    return $task->save(false);
                } elseif ($action == 1) {
                    $task->hidden = 0;
                    Yii::$app->response->statusCode = 200;
                    return $task->save(false);
                }
            } else Yii::$app->response->statusCode = 403;
        } else Yii::$app->response->statusCode = 403;
    }

    public function actionActive()
    {
        if (!Yii::$app->user->isGuest) {

            $action = Yii::$app->request->post('action');
            $scanid = Yii::$app->request->post('scanid');

            $passive = PassiveScan::find()
                ->where(['PassiveScanid' => $scanid])
                ->limit(1)
                ->one();

            if (Yii::$app->user->id === $passive['userid']) {

                if ($action == 1) {
                    $passive->is_active = 1;
                    Yii::$app->response->statusCode = 200;
                    return $passive->save(false);
                } elseif ($action == 0) {
                    $passive->is_active = 0;
                    Yii::$app->response->statusCode = 200;
                    return $passive->save(false);
                }
            } else Yii::$app->response->statusCode = 403;
        } else Yii::$app->response->statusCode = 403;
    }


    public function actionNotifications()
    {
        if (!Yii::$app->user->isGuest) {

            $action = Yii::$app->request->post('action');
            $scanid = Yii::$app->request->post('scanid');

            $passive = PassiveScan::find()
                ->where(['PassiveScanid' => $scanid])
                ->limit(1)
                ->one();

            if (Yii::$app->user->id === $passive['userid']) {

                if ($action == 1) {
                    $passive->notifications_enabled = 1;
                    Yii::$app->response->statusCode = 200;
                    return $passive->save(false);
                } elseif ($action == 0) {
                    $passive->notifications_enabled = 0;
                    Yii::$app->response->statusCode = 200;
                    return $passive->save(false);
                }
            } else Yii::$app->response->statusCode = 403;
        } else Yii::$app->response->statusCode = 403;
    }

    public function actionNmap()
    {
        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
        $model = new Nmap();

        $secretIN = Yii::$app->request->post('secret');

        if ($secretIN === $secret) {
            return $model::scanhost(Yii::$app->request->post());
        }

    }

    public function actionAmass()
    {
        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
        $model = new Amass();

        $secretIN = Yii::$app->request->post('secret');

        if ($secretIN === $secret) {
            return $model::amassscan(Yii::$app->request->post());
        }

    }

    public function actionGitscan()
    {
        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
        $model = new Gitscan();

        $secretIN = Yii::$app->request->post('secret');

        if ($secretIN === $secret) {
            return $model::gitscan(Yii::$app->request->post());
        }

    }

    public function actionDirscan()
    {
        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
        $model = new Dirscan();

        $secretIN = Yii::$app->request->post('secret');

        if ($secretIN === $secret) {
            return $model::dirscan(Yii::$app->request->post());
        }

    }

    public function actionReverseipscan()
    {
        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
        $model = new Reverseip();

        $secretIN = Yii::$app->request->post('secret');

        if ($secretIN === $secret) {
            return $model::reverseipscan(Yii::$app->request->post());
        }

    }

    public function actionIpscan()
    {
        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
        $model = new Ipscan();

        $secretIN = Yii::$app->request->post('secret');

        if ($secretIN === $secret) {
            return $model::ipscan(Yii::$app->request->post());
        }

    }

    public function actionVhostscan()
    {
        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
        $model = new Vhost();

        $secretIN = Yii::$app->request->post('secret');

        if ($secretIN === $secret) {
            return $model::vhostscan(Yii::$app->request->post());
        }

    }


}
