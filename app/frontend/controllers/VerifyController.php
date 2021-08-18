<?php

namespace frontend\controllers;

use common\models\User;
use frontend\models\PassiveScan;
use frontend\models\Queue;
use frontend\models\SentEmail;
use frontend\models\Tasks;
use frontend\models\ToolsAmount;
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
        $auth = getenv('Authorization', 'Basic bmdpbng6QWRtaW4=');

        $secretIN = Yii::$app->request->post('secret');

        if ($secret === $secretIN) {

            $results = Tasks::find()
                ->select(['tasks.taskid','tasks.notify_instrument', 'tasks.nmap_status','tasks.amass_status', 'tasks.dirscan_status','tasks.gitscan_status', 'tasks.reverseip_status','tasks.ips_status', 'tasks.vhost_status'])
                ->where(['!=', 'status', 'Done.'])
                ->limit(1000)
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
                        if ($result->nmap_status === "Done.") $nmap = 1;
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
                        if ($result->vhost_status === "Done.") $vhost = 1;
                    } else $vhost = 1;

                    if ($result->notify_instrument == "3") {
                        Tasks::deleteAll(['notify_instrument' => 3, 'wayback' => "[]", 'nuclei' => NULL, 'dirscan' => NULL, 'taskid' => $result->taskid]);
                        Tasks::deleteAll(['notify_instrument' => 3, 'wayback' => NULL, 'nuclei' => NULL, 'dirscan' => NULL, 'taskid' => $result->taskid]);
                    }

                    if ($nmap == 1 && $amass == 1 && $dirscan == 1 && $gitscan == 1 && $vhost == 1 && $ips == 1 && $reverseip == 1) {

                        if ($result->notified == 0 && $result->notification_enabled == 1) {

                            $result->status = "Done.";

                            $result->notified = 1;

                            $result->save(false);

                            $diff = 0;

                            /*$user = User::find()
                                ->where(['id' => $result->userid])
                                ->limit(1)
                                ->one();

                            $email = $user->email;

                            $result->save(false);

                            $this->sendactiveemail($result->taskid, $email, $user->id);*/

                            $this->sendslack($result->taskid, $diff);
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
                ->andWhere(['notifications_enabled' => 1])
                ->andWhere(['user_notified' => 0])
                ->limit(10)
                ->all();

            if ($results != NULL) {
                foreach ($results as $result) {

                    $diff = 0;

                    //- распарсить и вызвать для каждого нового поддомена дирскан
                    //- записать поддомены из array_diff в тхт и вызвать гитхаунд

                        if ($pos = strpos($result->notify_instrument, "1") !== false) {

                            if ($result->nmap_previous != null && $result->nmap_new != null){
                                $diff = array_unique(array_diff($result->nmap_new,$result->nmap_previous));
                            }
                        }

                        if ($pos = strpos($result->notify_instrument, "2") !== false) {

                            if ($result->amass_previous != null && $result->amass_new != null) {

                                $diff = array_unique(array_diff($result->amass_new,$result->amass_previous));
                            }
                        }

                        if ($pos = strpos($result->notify_instrument, "3") !== false) {

                            if ($result->dirscan_previous != null && $result->dirscan_new != null){
                                $diff = array_unique(array_diff($result->dirscan_new,$result->dirscan_previous));
                            }

                        }

                        $result->needs_to_notify = 0;

                        $result->notify_instrument = 0;

                        $result->user_notified = 1;

                        $result->save(false);

                        if ($diff != ""){
                            $this->sendPassiveSlack($result->scanid, $diff);
                        }
                        
                }
            }
        } else return Yii::$app->response->statusCode = 403;
    }


    public function actionQueue()
    {

        //instrument id 1=nmap, 2=amass, 3=dirscan, 4=git, 5=reverseip, 6=ips, 7=vhost

        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
        $auth = getenv('Authorization', 'Basic bmdpbng6QWRtaW4=');

        $secretIN = Yii::$app->request->post('secret');

        if ($secret === $secretIN) {

            /* $allresults = Queue::find()
                ->andWhere(['instrument' => "3"])
                ->andWhere(['passivescan' => "0"])
                ->andWhere(['todelete' => "1"])
                ->limit(10)
                ->all();

             foreach ($allresults as $results) {

                if ($results != NULL) {

                    $tools_amount_nuclei = (int) exec('sudo docker ps | grep "nuclei" | wc -l');

                    if ($tools_amount_nuclei < 80) {

                        $results->delete();

                        $dirscanurl = $results->dirscanUrl;
                        $dirscanip = $results->dirscanIP;

                        if ($dirscanip != "") {
                            exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "url=' . $dirscanurl . ' & ip=' . $dirscanip . ' & taskid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/nuclei > /dev/null 2>/dev/null &');
                        } else {
                            exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "url=' . $dirscanurl . ' & taskid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/nuclei > /dev/null 2>/dev/null &');
                        }
                                         
                    }
                }
            } */

            $allresults = Queue::find()
                ->andWhere(['working' => "0"])
                ->andWhere(['todelete' => "0"])
                ->orderBy(['passivescan' => SORT_ASC])
                ->limit(1000)
                ->all();

            $tools_amount = ToolsAmount::find()
                ->where(['id' => 1])
                ->one();

            $tools_amount_amass = (int) exec('sudo docker ps | grep "amass" | wc -l');

            $tools_amount_ffuf = (int) exec('sudo docker ps | grep "ffuf" | wc -l');      

            foreach ($allresults as $results) {

                if ($results != NULL) {

                    if ($results->passivescan == 0){ 
                    
                        if (strpos($results->instrument, "1") !== false) {

                            if ($tools_amount->nmap < 10) {

                                $results->working = 1;
                                $results->todelete = 1;

                                $nmapurl = $results->nmap;

                                exec('curl --insecure -H \'Authorization: ' . $auth . '\' --data "url=' . $nmapurl . ' & taskid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/nmap > /dev/null 2>/dev/null &');

                                $results->save();

                                $tools_amount->nmap = $tools_amount->nmap+1;
                            }
                        }

                        if (strpos($results->instrument, "2") !== false) {

                            if ($tools_amount_amass < 3) {

                                $results->working  = 1;
                                $results->todelete = 1;

                                $url = $results->amassdomain;

                                exec('curl --insecure -H \'Authorization: ' . $auth . '\' --data "url=' . $url . ' &taskid=' . $results->taskid . '&secret=' . $secret . '" https://dev.localhost.soft/scan/amass > /dev/null 2>/dev/null &');

                                $results->save();

                                $tools_amount_amass++;
                            }
                        }

                        if (strpos($results->instrument, "3") !== false) {

                            if ( ( ($tools_amount_ffuf < 50) && ($tools_amount_amass < 2) ) || ($tools_amount_ffuf < 35) ) {

                                $results->working = 1;
                                $results->todelete = 1;

                                $dirscanurl = $results->dirscanUrl;
                                $dirscanip = $results->dirscanIP;

                                if ($dirscanip != "") {
                                    exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "url=' . $dirscanurl . ' & ip=' . $dirscanip . ' & taskid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/dirscan > /dev/null 2>/dev/null &');
                                } else {
                                    exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "url=' . $dirscanurl . ' & taskid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/dirscan > /dev/null 2>/dev/null &');
                                }
                                $results->save();

                                $tools_amount_ffuf++;
                            }
                        }

                        if (strpos($results->instrument, "4") !== false) {

                            if ($tools_amount->gitscan < 1) {

                                if ($tools_amount_amass < 1) {

                                    if ($tools_amount_ffuf < 20) {

                                        $results->working  = 1;
                                        $results->todelete = 1;

                                        exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "active=1&taskid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/gitscan > /dev/null 2>/dev/null &');

                                        $results->save();

                                        $tools_amount->gitscan = $tools_amount->gitscan+1;
                                    }
                                }
                            }
                        }

                        if (strpos($results->instrument, "7") !== false) {

                            if ($tools_amount->vhosts < 50) {

                                $results->working = 1;
                                $results->todelete = 1;

                                if ($results->vhostport != "" && $results->vhostdomain != "" && $results->vhostip != ""){
                                    if ( $results->vhostssl == 1 ) $ssl = "1"; else $ssl = "0";

                                    exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "taskid=' . $results->taskid
                                            . ' & secret=' . $secret . '& domain=' . $results->vhostdomain . ' & ip=' . $results->vhostip
                                            . ' & port=' . $results->vhostport . ' & ssl=' . $ssl .'" https://dev.localhost.soft/scan/vhostscan > /dev/null 2>/dev/null &');

                                } else exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "taskid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/vhostscan > /dev/null 2>/dev/null &');

                                $results->save();
                                $tools_amount->vhosts = $tools_amount->vhosts+1;                   
                            }

                        } 
                    }

                    if ($results->passivescan == 1){

                            if (strpos($results->instrument, "1") !== false) {

                                if ($tools_amount->nmap < 10) {

                                    $results->working = 1;
                                    $results->todelete = 1;

                                    exec('curl --insecure -H \'Authorization: ' . $auth . '\' --data "url=' . $results->nmap . ' & scanid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/passive/nmap > /dev/null 2>/dev/null &');

                                    $results->save();

                                    $tools_amount->nmap = $tools_amount->nmap+1;
                                }
                            }

                            if (strpos($results->instrument, "2") !== false) {

                                if ($tools_amount_amass < 2) {

                                    $results->working  = 1;
                                    $results->todelete = 1;

                                    exec('curl --insecure -H \'Authorization: ' . $auth . '\' --data "url=' . $results->amassdomain . ' & scanid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/passive/amass > /dev/null 2>/dev/null &');

                                    $results->save();

                                    $tools_amount_amass++;
                                }
                            }

                            if (strpos($results->instrument, "3") !== false) {

                                if ($tools_amount_ffuf < 20) {

                                    $results->working = 1;
                                    $results->todelete = 1;

                                    if ($dirscanip != "") {

                                        exec('curl --insecure -H \'Authorization: ' . $auth . '\' --data "ip=' . $results->dirscanIP . ' & url= ' . $results->dirscanUrl . ' & scanid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/passive/dirscan > /dev/null 2>/dev/null &');

                                    } else {
                                        
                                        exec('curl --insecure -H \'Authorization: ' . $auth . '\' --data "url=' . $results->dirscanUrl . ' & scanid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/passive/dirscan > /dev/null 2>/dev/null &');

                                    }
                                    $results->save();

                                    $tools_amount_ffuf++;
                                }
                            }
                    }

                    if ($results->working = 1) sleep(3); // give os some time to properly create process
                }
            } 

            $tools_amount->amass = $tools_amount_amass;

            $tools_amount->dirscan = $tools_amount_ffuf;

            $tools_amount->save();    
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

    private function sendslack($scanid, $diff)
    {
        $slack_url = getenv('SLACK_WEBHOOK_URL', 'https://hooks.slack.com/services/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');

        $text = "Your scan with ID: " . $scanid . " was successfully done and waiting for you in your profile!";

        exec("curl -X POST -H 'Content-type: application/json' --data '{'text':". json_encode($text) . "'} " . $slack_url . "");


        return 1;
    }

    private function sendPassiveSlack($scanid, $diff)
    {
        $slack_url = getenv('SLACK_WEBHOOK_URL', 'https://hooks.slack.com/services/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');

        $text = "PASSIVE SCAN ID: " . $scanid . " DIFF:" . json_encode($diff);

        exec("curl -X POST -H 'Content-type: application/json' --data '{'text':" . json_encode($text) . "}' " . $slack_url . "");

        return 1;

    }


}
