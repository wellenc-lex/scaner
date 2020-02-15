<?php

namespace frontend\controllers;

use common\models\User;
use frontend\models\PassiveScan;
use frontend\models\Queue;
use frontend\models\SentEmail;
use frontend\models\Tasks;
use Yii;
use yii\web\Controller;


/**
 * Scan controller
 */
class VerifyController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $enableCsrfValidation = false;


    /**
     *
     * checks active scan result and sends emails
     *
     */

    public function actionActive()
    {

        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');

        $secretIN = Yii::$app->request->post('secret');

        if ($secret === $secretIN) {

            $results = Tasks::find()
                ->where(['!=', 'status', 'Done.'])
                ->all();

            if ($results != NULL) {

                foreach ($results as $result) {

                    $nmap = 0;
                    $amass = 0;
                    $dirscan = 0;
                    $vhost = 0;
                    $gitscan = 0;
                    $ips = 0;
                    $reverseip = 0;

                    if (($pos1 = strpos($result->notify_instrument, "1")) !== false) {
                        if ($result->nmap_status == "Done.") $nmap = 1;
                    } else $nmap = 1;

                    if ($pos = strpos($result->notify_instrument, "2") !== false) {
                        if ($result->amass_status === "Done.") $amass = 1;
                    } else $amass = 1;

                    if ($pos = strpos($result->notify_instrument, "3") !== false) {
                        if ($result->dirscan_status === "Done.") $dirscan = 1;
                    } else $dirscan = 1;

                    if ($pos = strpos($result->notify_instrument, "4") !== false) {
                        if ($result->gitscan_status === "Done.") $gitscan = 1;
                    } else $gitscan = 1;

                    if ($pos = strpos($result->notify_instrument, "5") !== false) {
                        if ($result->reverseip_status === "Done.") $reverseip = 1;
                    } else $reverseip = 1;

                    if ($pos = strpos($result->notify_instrument, "6") !== false) {
                        if ($result->ips_status === "Done.") $ips = 1;
                    } else $ips = 1;

                    if ($pos = strpos($result->notify_instrument, "7") !== false) {
                        if ($result->vhost_status == "Done.") $vhost = 1;
                    } else $vhost = 1;

                    if ($nmap == 1 && $amass == 1 && $dirscan == 1 && $gitscan == 1 && $vhost == 1 && $ips == 1 && $reverseip == 1) {

                        if ($result->notified == 0 && $result->notification_enabled == 1) {

                            $result->status = "Done.";

                            $result->notified = 1;

                            $user = User::find()
                                ->where(['id' => $result->userid])
                                ->limit(1)
                                ->one();

                            $email = $user->email;

                            $result->save(false);

                            $this->sendactiveemail($result->taskid, $email, $user->id);
                        } elseif ($result->notified == 0 && $result->notification_enabled == 0) {

                            $result->status = "Done.";

                            $result->notified = 1;

                            $result->save(false);
                        }
                    }
                }
            }
            return 1;
        } else return Yii::$app->response->statusCode = 403;
    }

    /**
     *
     * checks passive scan results and sends emails
     *
     */
    public function actionPassive()
    {
        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');

        $secretIN = Yii::$app->request->post('secret');

        if ($secret === $secretIN) {

            $results = PassiveScan::find()
                ->where(['is_active' => 1])
                ->andWhere(['needs_to_notify' => 1])
                ->andWhere(['user_notified' => 0])
                ->all();

            if ($results != NULL) {
                foreach ($results as $result) {

                    if ($result->notifications_enabled == 1) {

                        $instruments = "";
                        if ($pos = strpos($result->notify_instrument, "1") !== false) {

                            $instruments = $instruments . "Nmap, ";
                        }

                        if ($pos = strpos($result->notify_instrument, "2") !== false) {

                            $instruments = $instruments . "Amass, ";
                        }

                        if ($pos = strpos($result->notify_instrument, "3") !== false) {

                            $instruments = $instruments . "Dirscan, ";

                        }
                        /*
                        if ($pos = strpos($result->notify_instrument, "4") != false) {

                            $instruments = $instruments."Vhost scan, ";
                        }*/

                        if ($result->needs_to_notify == 1 && $result->notifications_enabled == 1) {

                            $user = User::find()
                                ->where(['id' => $result->userid])
                                ->limit(1)
                                ->one();

                            $email = $user->email;

                            $result->needs_to_notify = 0;

                            $result->notify_instrument = 0;

                            $result->user_notified = 1;

                            $result->save(false);

                            $this->sendpassiveemail($instruments, $result->scanid, $email, $user->id);
                        }
                    }
                }
            }
        } else return Yii::$app->response->statusCode = 403;
    }


    public function actionQueue()
    {

        //1=nmap, 2=amass, 3=dirscan, 4=git, 5=reverseip, 6=ips,7=vhost

        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');

        $secretIN = Yii::$app->request->post('secret');

        if ($secret === $secretIN) {

            $results = Queue::find()
                ->limit(1)
                ->andWhere(['working' => "0"])
                ->andWhere(['todelete' => "0"])
                ->one();

            if ($results != NULL) {

                if (strpos($results->instrument, "1") !== false) {

                    //$countnmap = "pgrep -c nmap";

                    //exec($countnmap, $countnmap_returncode);

                    //if ($countnmap_returncode[0] < 8) {

//change to https+variable increment decrement in db
                        $results->working = 1;
                        $results->todelete = 1;

                        $nmapurl = $results->nmap;

                        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');

                        exec('curl --insecure  --data "url= ' . $nmapurl . ' & taskid=' . $results->taskid . ' & secret=' . $secret . '" http://dev.localhost.soft/scan/nmap > /dev/null 2>/dev/null &');

                        $results->save();
                    //}
                }

                if (strpos($results->instrument, "2") !== false) {

                    //$countamass = "pgrep -c amass";

                    //exec($countamass, $countamass_returncode);

                    //if ($countamass_returncode[0] < 2) {

                    //change to https+variable increment decrement in db

                        $results->working = 1;
                        $results->todelete =1;

                        $url = $results->amassdomain;

                        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');

                        exec('curl --insecure -H \'Authorization: Basic bmdpbng6U25pcGVydWx0cmEx\' --data "url=' . $url . ' &taskid=' . $results->taskid . '&secret=' . $secret . '" http://dev.localhost.soft/scan/amass > /dev/null 2>/dev/null &');

                        $results->save();;
                    //}
                }

                if (strpos($results->instrument, "3") !== false) {

                    $countdirscan = "pgrep -c python3";

                    exec($countdirscan, $countdirscan_returncode);

                    if ($countdirscan_returncode[0] < 40) {

                        $results->working = 1;
                        $results->todelete = 1;

                        $dirscanurl = $results->dirscanUrl;
                        $dirscanip = $results->dirscanIP;

                        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');

                        if ($dirscanip != "") {
                            exec('curl --insecure   -H \'Authorization: Basic bmdpbng6U25pcGVydWx0cmEx\' --data "url= ' . $dirscanurl . ' & ip=' . $dirscanip . ' & taskid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/dirscan > /dev/null 2>/dev/null &');
                        } else {
                            exec('curl --insecure   -H \'Authorization: Basic bmdpbng6U25pcGVydWx0cmEx\' --data "url= ' . $dirscanurl . ' & taskid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/dirscan > /dev/null 2>/dev/null &');
                        }
                        $results->save();
                    }
                }

                if (strpos($results->instrument, "7") !== false) {

                    $countvhost = "pgrep -c curl --insecure ";

                    exec($countvhost, $countvhost_returncode);

                    if ($countvhost_returncode[0] < 25) {

                        $results->working = 1;
                        $results->todelete = 1;

                        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');

                        exec($countvhost, $countvhost_returncode);

                        if ($countvhost_returncode[0] < 25) {

                            if ($results->vhostport != 0 && $results->vhostdomain != 0 && $results->vhostip != 0){
                                if ( $results->vhostport == 1 ) $ssl = "1"; else $ssl = "0";

                                exec('curl --insecure  -H \'Authorization: Basic bmdpbng6U25pcGVydWx0cmEx\' --data "taskid=' . $results->taskid
                                    . ' & secret=' . $secret . '& domain=' . $results->vhostdomain . ' & ip=' . $results->vhostip
                                    . ' & port=' . $results->vhostport . ' & ssl=' . $ssl .'" https://dev.localhost.soft/scan/vhostscan > /dev/null 2>/dev/null &');

                            } else exec('curl --insecure  -H \'Authorization: Basic bmdpbng6U25pcGVydWx0cmEx\' --data "taskid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/vhostscan > /dev/null 2>/dev/null &');

                        } else sleep(250);

                        $results->save();

                    } else return 0;
                }
            }
        } else return Yii::$app->response->statusCode = 403;
    }

    private function sendpassiveemail($instrument, $scanid, $email, $userid)
    {

        $text = "We have detected changes while doing your passive scan with ID: " . $scanid . " with instruments: " . $instrument . ". \n\r Please visit your passive scan results tab to see the changes.";

        Yii::$app->mailer->compose()
            ->setFrom('youremail@gmail.com')
            ->setTo($email)
            ->setSubject("Changes in Passive scan's infrastructure.")
            ->setTextBody($text)
            ->send();

        $mail = new SentEmail();

        $mail->content = $text;
        $mail->email = $email;
        $mail->type = "Passive.";
        $mail->date = date("Y-m-d H-i-s");
        $mail->scanid = $scanid;
        $mail->userid = $userid;

        return $mail->save();

    }

    private function sendactiveemail($scanid, $email, $userid)
    {

        $text = "Your scan with ID: " . $scanid . " was successfully done and waiting for you in your profile! \n\r Please visit your passive scan results tab to see the changes. \n\r Thanks for using our service, and have a nice day.";

        Yii::$app->mailer->compose()
            ->setFrom('youremail@gmail.com')
            ->setTo($email)
            ->setSubject("Active scan with id: " . $scanid . " is done.")
            ->setTextBody($text)
            ->send();

        $mail = new SentEmail();

        $mail->content = $text;
        $mail->email = $email;
        $mail->type = "Active.";
        $mail->date = date("Y-m-d H-i-s");
        $mail->scanid = $scanid;
        $mail->userid = $userid;

        return $mail->save();
    }


}