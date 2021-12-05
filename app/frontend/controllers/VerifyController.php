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
                ->limit(2500)
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
                    
                    if ($result->nmap_status === "Done.") $nmap = 1;

                    if ($result->amass_status === "Done.") $amass = 1;

                    if ($result->dirscan_status === "Done.") $dirscan = 1;

                    if ($result->gitscan_status === "Done.") $gitscan = 1;

                    if ($result->reverseip_status === "Done.") $reverseip = 1;

                    if ($result->ips_status === "Done.") $ips = 1;

                    if ($result->vhost_status === "Done.") $vhost = 1;

                    /*if ($result->notify_instrument == "3") {
                        Tasks::deleteAll(['notify_instrument' => 3, 'wayback' => "[]", 'nuclei' => NULL, 'dirscan' => NULL, 'jsa' => NULL, 'taskid' => $result->taskid]);
                        Tasks::deleteAll(['notify_instrument' => 3, 'wayback' => NULL, 'nuclei' => NULL, 'dirscan' => NULL, 'jsa' => NULL, 'taskid' => $result->taskid]);
                    }*/

                    if ( $nmap===1 || $amass===1 || $dirscan===1 || $ips === 1 || $vhost===1 ){
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

                            //$this->sendslack($result->taskid, $diff);
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

        //instrument id 1=nmap, 2=amass, 3=dirscan, 4=git, 5=reverseip, 5=whatweb, 6=ips, 7=vhost, 8=nuclei, 9=jsa

        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
        $auth = getenv('Authorization', 'Basic bmdpbng6QWRtaW4=');

        $secretIN = Yii::$app->request->post('secret');

        if ($secret === $secretIN) {

            $tools_amount = ToolsAmount::find()
                ->where(['id' => 1])
                ->one();

            $nucleiurls = array(); $nuclei_in_task = 0; $queues_array = array(); $whatweburls = array(); $nmapips = array();

            $tools_amount_nmap    = (int) exec('sudo docker ps | grep "nmap" | wc -l');  

            $tools_amount_amass   = (int) exec('sudo docker ps | grep "amass" | wc -l');

            $tools_amount_ffuf    = (int) exec('sudo docker ps | grep "ffuf" | wc -l');      

            $tools_amount_jsa     = (int) exec('sudo docker ps | grep "jsa" | wc -l');

            $tools_amount_ips     = (int) exec('sudo docker ps | grep "passivequery" | wc -l');

            $tools_amount_nuclei  = (int) exec('sudo docker ps | grep "nuclei" | wc -l');   

            $tools_amount_whatweb = (int) exec('sudo docker ps | grep "whatweb" | wc -l');   

            

            //$max_amass = 0; $max_ffuf = 0; $max_vhost = 0; $max_jsa = 0; $max_nuclei = 0; $max_nmap = 0; $max_nuclei_in_task = 500; $max_ips = 0; $max_whatweb = 0; $max_whatweb_in_task = 300;


            $max_amass = 1; $max_ffuf = 250; $max_vhost = 20; $max_nuclei = 1; $max_nuclei_in_task = 200; $max_jsa = 0; $max_ips = 1; $max_whatweb = 2; $max_whatweb_in_task = 50;

            $max_nmap = 5; $max_nmap_in_task = 100;

            if( $tools_amount_nmap < $max_nmap ){
                //Nmaps
                $queues = Queue::find()
                    ->andWhere(['working' => "0"])
                    ->andWhere(['todelete' => "0"])
                    ->andWhere(['instrument' => "1"])
                    ->andWhere(['passivescan' => "0"])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit($max_nmap_in_task)
                    ->all();

                foreach ($queues as $results) {

                    if ($results != NULL) {

                        if ($tools_amount_nmap < $max_nmap && count($nmapips) < $max_nmap_in_task) {

                            $results->working = 1;

                            $nmapintask = explode(" ", $results->nmap);

                            foreach($nmapintask as $id){
                                $nmapips[] = $id;
                            }

                            $nmapips = array_unique($nmapips);

                            $queues_array_nmap[] = $results->id;

                            $results->save();
                        }
                    }
                }
            }
            
            if( $tools_amount_amass < $max_amass ){
                //Amasses
                $queues = Queue::find()
                    ->andWhere(['working' => "0"])
                    ->andWhere(['todelete' => "0"])
                    ->andWhere(['instrument' => "2"])
                    ->andWhere(['passivescan' => "0"])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit($max_amass)
                    ->all();

                foreach ($queues as $results) {

                    if ($results != NULL) {

                        if ($tools_amount_amass < $max_amass ) {

                            $results->working  = 1;

                            $url = $results->amassdomain;

                            exec('curl --insecure -H \'Authorization: ' . $auth . '\' --data "url=' . $url . ' &queueid=' . $results->id . '&taskid=' . $results->taskid . '&secret=' . $secret . '" https://dev.localhost.soft/scan/amass > /dev/null 2>/dev/null &');

                            $results->save();

                            $tools_amount_amass++;
                        }
                    }
                }
            }

            //dirscan from the end of the queue
            if( $tools_amount_ffuf < $max_ffuf-80 ){
                //Dirscans
                $queues = Queue::find()
                    ->andWhere(['working' => "0"])
                    ->andWhere(['todelete' => "0"])
                    ->andWhere(['instrument' => "3"])
                    ->andWhere(['passivescan' => "0"])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit($max_ffuf-$tools_amount_ffuf-80)
                    ->all();

                foreach ($queues as $results) {

                    if ($results != NULL) {

                        if ( $tools_amount_ffuf < $max_ffuf ) {

                            $results->working = 1;

                            $dirscanurl = $results->dirscanUrl;
                            $dirscanip = $results->dirscanIP;

                            if ($dirscanip != "") {
                                exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "url=' . $dirscanurl . ' &ip=' . $dirscanip . ' &queueid=' . $results->id . '&taskid=' . $results->taskid . ' &wordlist=' . $results->wordlist . ' &secret=' . $secret . '" https://dev.localhost.soft/scan/dirscan > /dev/null 2>/dev/null &');
                            } else {
                                exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "url=' . $dirscanurl . ' &taskid=' . $results->taskid . ' &queueid=' . $results->id . '&wordlist=' . $results->wordlist . ' &secret=' . $secret . '" https://dev.localhost.soft/scan/dirscan > /dev/null 2>/dev/null &');
                            }
                            $results->save();

                            $tools_amount_ffuf++;
                        }
                    }
                }
            }

            //dirscan from the start of the queue
            if( $tools_amount_ffuf < $max_ffuf ){
                //Dirscans
                $queues = Queue::find()
                    ->andWhere(['working' => "0"])
                    ->andWhere(['todelete' => "0"])
                    ->andWhere(['instrument' => "3"])
                    ->andWhere(['passivescan' => "0"])
                    ->orderBy(['id' => SORT_ASC])
                    ->limit($max_ffuf-$tools_amount_ffuf)
                    ->all();

                foreach ($queues as $results) {

                    if ($results != NULL) {

                        if ( $tools_amount_ffuf < $max_ffuf ) {

                            $results->working = 1;

                            $dirscanurl = $results->dirscanUrl;
                            $dirscanip = $results->dirscanIP;

                            if ($dirscanip != "") {
                                exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "url=' . $dirscanurl . ' &ip=' . $dirscanip . ' &queueid=' . $results->id . '&taskid=' . $results->taskid . ' &wordlist=' . $results->wordlist . ' &secret=' . $secret . '" https://dev.localhost.soft/scan/dirscan > /dev/null 2>/dev/null &');
                            } else {
                                exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "url=' . $dirscanurl . ' &taskid=' . $results->taskid . ' &queueid=' . $results->id . '&wordlist=' . $results->wordlist . ' &secret=' . $secret . '" https://dev.localhost.soft/scan/dirscan > /dev/null 2>/dev/null &');
                            }
                            $results->save();

                            $tools_amount_ffuf++;
                        }
                    }
                }
            }

            /*
            //Gitscan
            $queues = Queue::find()
                ->andWhere(['working' => "0"])
                ->andWhere(['todelete' => "0"])
                ->andWhere(['instrument' => "4"])
                ->orderBy(['passivescan' => SORT_ASC, 'id' => SORT_DESC])
                ->limit(1)
                ->all();

            foreach ($queues as $results) {

                if ($results != NULL) {

                    

                        if ($tools_amount->gitscan < 0) {

                            if ($tools_amount_amass < 1) {

                                if ($tools_amount_ffuf < $max_ffuf && $tools_amount_jsa <= $max_jsa ) {

                                    $results->working  = 1;

                                    exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "active=1&queueid=' . $results->id . '&taskid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/gitscan > /dev/null 2>/dev/null &');

                                    $results->save();

                                    $tools_amount->gitscan = $tools_amount->gitscan+1;
                                }
                            }
                        }
                    
                }
            } */

            if( $tools_amount_whatweb < $max_whatweb ){
                //Whatweb
                $queues = Queue::find()
                    ->andWhere(['working' => "0"])
                    ->andWhere(['todelete' => "0"])
                    ->andWhere(['instrument' => "5"])
                    ->andWhere(['passivescan' => "0"])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit($max_whatweb_in_task)
                    ->all();

                foreach ($queues as $results) {

                    if ($results != NULL) {

                        if ($tools_amount_whatweb < $max_whatweb && $whatweb_in_task <= $max_whatweb_in_task ) {

                            $results->working = 1;

                            $whatweburls[] = $results->dirscanUrl;

                            $queues_array_whatweb[] = $results->id;

                            $results->save();

                            $whatweb_in_task++;
                        }
                    }
                }
            }

            if( $tools_amount_ips < $max_ips ){

                //execute only several times per day because of the API keys limitations per day
                if ( date('H')/10==2 || date('H')==19 || date('H')==15){

                    //Ipscan
                    $queues = Queue::find()
                        ->andWhere(['working' => "0"])
                        ->andWhere(['todelete' => "0"])
                        ->andWhere(['instrument' => "6"])
                        ->andWhere(['passivescan' => "0"])
                        ->orderBy(['id' => SORT_DESC])
                        ->limit($max_ips)
                        ->all();

                    foreach ($queues as $results) {

                        if ($results != NULL) {

                            if ($tools_amount_ips < $max_ips) {

                                $results->working  = 1;

                                $query = $results->ipscan;

                                if($query=="") $query=$results->dirscanUrl;

                                exec('curl --insecure -H \'Authorization: ' . $auth . '\' --data "query=' . $query . ' &queueid=' . $results->id . '&taskid=' . $results->taskid . '&secret=' . $secret . '" https://dev.localhost.soft/scan/ipscan > /dev/null 2>/dev/null &');

                                $results->save();

                                $tools_amount_ips++;
                            }
                        }
                    }
                }
            }

            if( $tools_amount_ffuf < $max_vhost+$max_ffuf ){
                //Vhost
                $queues = Queue::find()
                    ->andWhere(['working' => "0"])
                    ->andWhere(['todelete' => "0"])
                    ->andWhere(['instrument' => "7"])
                    ->andWhere(['passivescan' => "0"])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit($max_vhost-$tools_amount_ffuf)
                    ->all();

                foreach ($queues as $results) {

                    if ($results != NULL) {

                        if ($tools_amount_ffuf < $max_vhost) {

                            $results->working = 1;

                            if ($results->vhostport != "" && $results->vhostdomain != "" && $results->vhostip != ""){
                                if ( $results->vhostssl == 1 ) $ssl = "1"; else $ssl = "0";

                                exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "queueid=' . $results->id . '&taskid=' . $results->taskid
                                        . ' & secret=' . $secret . '& domain=' . $results->vhostdomain . ' & ip=' . $results->vhostip
                                        . ' & port=' . $results->vhostport . ' & ssl=' . $ssl .'" https://dev.localhost.soft/scan/vhostscan > /dev/null 2>/dev/null &');

                            } else exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "queueid=' . $results->id . '&taskid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/vhostscan > /dev/null 2>/dev/null &');

                            $results->save();

                            $tools_amount_ffuf++;                 
                        }
                    }
                }
            }

            if( $tools_amount_nuclei < $max_nuclei ){
                //Nuclei
                $queues = Queue::find()
                    ->andWhere(['working' => "0"])
                    ->andWhere(['todelete' => "0"])
                    ->andWhere(['instrument' => "8"])
                    ->andWhere(['passivescan' => "0"])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit($max_nuclei_in_task)
                    ->all();

                if (count($queues) > 200) {
                    foreach ($queues as $results) {

                        if ($results != NULL) {

                            if ($tools_amount_nuclei < $max_nuclei && $tools_amount_amass <= $max_amass && $nuclei_in_task <= $max_nuclei_in_task ) {

                                $results->working = 1;

                                $nucleiurls[] = $results->dirscanUrl;

                                $queues_array_nuclei[] = $results->id;

                                $results->save();

                                $nuclei_in_task++;
                            }
                        }
                    }
                }
            }

            if( $tools_amount_jsa < $max_jsa ){
                //JS Analysis
                $queues = Queue::find()
                    ->andWhere(['working' => "0"])
                    ->andWhere(['todelete' => "0"])
                    ->andWhere(['instrument' => "9"])
                    ->andWhere(['passivescan' => "0"])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit($max_jsa)
                    ->all();

                foreach ($queues as $results) {

                    if ($results != NULL) {

                        if ($tools_amount_jsa < $max_jsa ) {

                            $results->working = 1;

                            $url = $results->dirscanUrl;

                            if ($url != "") {
                                exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "url=' . $url . '&queueid=' . $results->id . '&taskid=' . $results->taskid . '&secret=' . $secret 
                                    . '" https://dev.localhost.soft/scan/jsa > /dev/null 2>/dev/null &');
                            }
                            $results->save();

                            $tools_amount_jsa++;
                        }
                    }
                }
            }



                    /*
                    if ($results->passivescan == 1){

                            if (strpos($results->instrument, "1") !== false) {

                                if ($tools_amount->nmap < 0) { //turned off

                                    $results->working = 1;

                                    exec('curl --insecure -H \'Authorization: ' . $auth . '\' --data "url=' . $results->nmap . ' & scanid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/passive/nmap > /dev/null 2>/dev/null &');

                                    $results->save();

                                    $tools_amount->nmap = $tools_amount->nmap+1;
                                }
                            }

                            if (strpos($results->instrument, "2") !== false) {

                                if ($tools_amount_amass < 0) { //turned off

                                    $results->working  = 1;

                                    exec('curl --insecure -H \'Authorization: ' . $auth . '\' --data "url=' . $results->amassdomain . ' & scanid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/passive/amass > /dev/null 2>/dev/null &');

                                    $results->save();

                                    $tools_amount_amass++;
                                }
                            }

                            if (strpos($results->instrument, "3") !== false) {

                                if ($tools_amount_ffuf < 0) {

                                    $results->working = 1;

                                    if ($dirscanip != "") {

                                        exec('curl --insecure -H \'Authorization: ' . $auth . '\' --data "ip=' . $results->dirscanIP . ' & url= ' . $results->dirscanUrl . ' & scanid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/passive/dirscan > /dev/null 2>/dev/null &');

                                    } else {
                                        
                                        exec('curl --insecure -H \'Authorization: ' . $auth . '\' --data "url=' . $results->dirscanUrl . ' & scanid=' . $results->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/passive/dirscan > /dev/null 2>/dev/null &');

                                    }
                                    $results->save();

                                    $tools_amount_ffuf++;
                                }
                            }
                    }*/

            $tools_amount->amass = $tools_amount_amass;

            $tools_amount->dirscan = $tools_amount_ffuf;

            
            if ( !empty($nucleiurls) ) {
                exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "url=' . implode( PHP_EOL, $nucleiurls ) . '&queueid=' . implode( PHP_EOL, $queues_array_nuclei )
                    . '&secret=' . $secret  . '" https://dev.localhost.soft/scan/nuclei >/dev/null 2>/dev/null &');

                $tools_amount_nuclei++;

                //first we get a lot of nuclei results into one big list and then create only 1 docker container to scan all these links. it really saves a lot of memory.
            }

            if ( !empty($whatweburls) ) {
                exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "url=' . implode( PHP_EOL, $whatweburls ) . '&queueid=' . implode( PHP_EOL, $queues_array_whatweb )
                    . '&secret=' . $secret  . '" https://dev.localhost.soft/scan/whatweb >/dev/null 2>/dev/null &');

                $tools_amount_whatweb++;

                //first we get a lot of whatweb results into one big list and then create only 1 docker container to scan all these links. it really saves a lot of memory.
            }

            if ( !empty($nmapips) ) {
                exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "ips=' . implode( PHP_EOL, $nmapips ) . '&queueid=' . implode( PHP_EOL, $queues_array_nmap )
                    . '&secret=' . $secret  . '" https://dev.localhost.soft/scan/nmap >/dev/null 2>/dev/null &');

                $tools_amount_nmap++;
            }

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
        $slack_url = getenv('SLACK_WEBHOOK_URL', '');

        $text = "Your scan with ID: " . $scanid . " was successfully done and waiting for you in your profile!";

        exec("curl -X POST -H 'Content-type: application/json' --data '{'text':". json_encode($text) . "'} " . $slack_url . "");


        return 1;
    }

    private function sendPassiveSlack($scanid, $diff)
    {
        $slack_url = getenv('SLACK_WEBHOOK_URL', '');

        $text = "PASSIVE SCAN ID: " . $scanid . " DIFF:" . json_encode($diff);

        exec("curl -X POST -H 'Content-type: application/json' --data '{'text':" . json_encode($text) . "}' " . $slack_url . "");

        return 1;

    }


}
