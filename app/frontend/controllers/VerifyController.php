<?php
namespace frontend\controllers;
use common\models\User;
use frontend\models\PassiveScan;
use frontend\models\Queue;
use frontend\models\SentEmail;
use frontend\models\Tasks;
use frontend\models\ToolsAmount;
use frontend\models\Nmap;
use frontend\models\Vhostscan;
use Yii;
use yii\web\Controller;
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
/**
 * Scan controller
 */
class VerifyController extends Controller
{
    public static function actionQueue()
    {
        //instrument id 1=nmap, 2=amass, 3=dirscan, 4=git, 5=whatweb, 6=ips, 7=vhost, 8=nuclei, 9=jsa
        $secret = getenv('api_secret') ?: 'secretkeyzzzzcbv55';
        $auth = getenv('Authorization') ?: 'Basic bmdpbng6QWRtaW4=';

        $secretIN = Yii::$app->request->post('secret');

        if ($secret === $secretIN) {

            $tools_amount = ToolsAmount::find()
                ->where(['id' => 1])
                ->one();

            $nucleiurls = array(); $nuclei_in_task = 0; $queues_array = array(); $whatweburls = array(); $nmapips = array(); $forbiddenbypassurls = array();

            $tools_amount_nmap    = (int) exec('sudo docker ps | grep "nmap" | wc -l');

            $tools_amount_amass   = (int) exec('sudo docker ps | grep "amass" | wc -l');

            $tools_amount_ffuf    = (int) exec('ps -ea | grep "shell.sh" | wc -l'); // ps aux | grep "ffuf" | wc -l

            $tools_amount_vhost   = (int) exec('ps -ea | grep "vhost" | wc -l');

            $tools_amount_ips     = (int) exec('sudo docker ps | grep "passivequery" | wc -l');

            $tools_amount_jsa     = (int) exec('sudo docker ps | grep "jsa" | wc -l');

            $tools_amount_nuclei  = (int) exec('sudo docker ps | grep "nuclei" | wc -l');

            $tools_amount_whatweb = (int) exec('sudo docker ps | grep "jsa" | wc -l');//whatweb

            $tools_amount_forbiddenbypass = (int) exec('sudo docker ps | grep "403bypass" | wc -l');

            $max_amass = 0; $max_ffuf = 0; $max_vhost = 0; $max_nuclei = 1; $max_nmap = 3; $max_nuclei_in_task = 200; $max_ips = 0; $max_whatweb = 0; $max_whatweb_in_task = 100; $max_jsa = 0; $max_nmap_in_task = 1000;

            $max_amass = 1; $max_ffuf = 0; $max_nmap = 8; $max_vhost = 0; $max_nuclei = 1; $max_nuclei_in_task = 500; $max_ips = 1; $max_whatweb = 5; $max_whatweb_in_task = 300;  $max_nmap_in_task = 1000; $max_forbiddenbypass = 0; $max_forbiddenbypass_in_task = 10;

            $max_passive_amass = 2;

            //$max_amass = 0; $max_ffuf = 0; $max_vhost = 0; $max_nuclei = 0; $max_nmap = 0; $max_nuclei_in_task = 1500; $max_ips = 0; $max_whatweb = 0; $max_whatweb_in_task = 100; $max_jsa = 0; $max_nmap_in_task = 5000;

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

                            foreach($nmapintask as $ip){

                                if (strpos($ip, ':') === false) { //TODO: add ipv6 support

                                    if (strpos($ip, '127.0.0.1') === false && strpos($ip, '0.0.0.0') === false) { //no need to scan local ip

                                        if ( vhostscan::ipCheck( $ip ) == 1 ) { // if IP is in blocked CDN mask - cloudflare ranges,etc
                                            $stop = 1;
                                        } else $stop = 0;

                                        if ($stop == 0) { //if ip is allowed
                                            $nmapips[] = $ip;
                                        }
                                    }
                                }
                            }

                            $nmapips = array_unique($nmapips);

                            $queues_array_nmap[] = $results->id;

                            $results->save();
                        }
                    }
                }

                $tools_amount_nmap++;
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

                            exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\' --data "url=' . $url . ' &queueid=' . $results->id . '&taskid=' . $results->taskid . '&secret=' . $secret . '" https://app/scan/amass > /dev/null 2>/dev/null &');

                            $results->save();

                            $tools_amount_amass++;
                        }
                    }
                }
            }

            //dirscan from the end of the queue
            if( $tools_amount_ffuf < $max_ffuf ){
                //Dirscans
                $queues = Queue::find()
                    ->andWhere(['working' => "0"])
                    ->andWhere(['todelete' => "0"])
                    ->andWhere(['instrument' => "3"])
                    ->andWhere(['passivescan' => "0"])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit(20)
                    ->all();

                $counter = 1; 

                foreach ($queues as $results) {

                    if ($results != NULL) {

                        $results->working = 1;

                        if ( VerifyController::dontscan($results->dirscanUrl) === 1 ) {
                            
                            $results->todelete = 1;
                            $results->save();
                            continue;
                        }

                        $ffufurls[$counter]["url"]= $results->dirscanUrl;
                        
                        $ffufurls[$counter]["ip"]= $results->dirscanIP ?: "0";

                        $ffufurls[$counter]["queueid"]= $results->id;

                        $ffufurls[$counter]["taskid"]= $results->taskid ?: "0";

                        $ffufurls[$counter]["wordlist"]= $results->wordlist ?: "0";

                        $counter++;

                        $results->save();
                    }
                }
            }

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

                            if ( VerifyController::dontscan($results->dirscanUrl) === 1 ) {
                                $results->todelete = 1;
                                $results->save();
                                continue;
                            }

                            $whatweburls[] = $results->dirscanUrl;

                            $queues_array_whatweb[] = $results->id;

                            $results->save();

                            $whatweb_in_task++;
                        }
                    }
                }

                $tools_amount_whatweb++;
            }

            if( $tools_amount_ips < $max_ips ){

                //execute only several times per day because of the API keys limitations per day
                if ( date('H')==19 || date('H')==15){

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

                                exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\' --data "query=' . $query . '&queueid=' . $results->id . '&taskid=' . $results->taskid . '&secret=' . $secret . '" https://app/scan/ipscan > /dev/null 2>/dev/null &');

                                $results->save();

                                $tools_amount_ips++;
                            }
                        }
                    }
                }
            }

            if( $tools_amount_vhost < $max_vhost ){
                //Vhost
                $queues = Queue::find()
                    ->andWhere(['working' => "0"])
                    ->andWhere(['todelete' => "0"])
                    ->andWhere(['instrument' => "7"])
                    ->andWhere(['passivescan' => "0"])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit(1)
                    ->all();

                foreach ($queues as $results) {

                    if ($results != NULL) {

                        if ($tools_amount_vhost < $max_vhost ) { // if currently already working ffufs amount less then vhost+max ffuf

                            $results->working = 1;

                            if ($results->vhostport != "" && $results->vhostdomain != "" && $results->vhostip != ""){
                                if ( $results->vhostssl == 1 ) $ssl = "1"; else $ssl = "0";

                                exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\'  --data "queueid=' . $results->id . '&taskid=' . $results->taskid
                                        . ' & secret=' . $secret . '& domain=' . $results->vhostdomain . ' & ip=' . $results->vhostip
                                        . ' & port=' . $results->vhostport . ' & ssl=' . $ssl .'" https://app/scan/vhostscan > /dev/null 2>/dev/null &');

                            } else exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\'  --data "queueid=' . $results->id . '&taskid=' . $results->taskid . ' & secret=' . $secret . '" https://app/scan/vhostscan > /dev/null 2>/dev/null &');

                            $results->save();

                            $tools_amount_vhost++;                 
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

                if (count($queues) >= $max_nuclei_in_task) {
                    foreach ($queues as $results) {

                        if ($results != NULL) {

                            if ($tools_amount_nuclei < $max_nuclei ) {

                                $queues_array_nuclei[] = $results->id;

                                $results->working = 1;

                                $results->save();

                                if ( VerifyController::dontscan($results->dirscanUrl) === 1 ) {
                                    $results->todelete = 1;
                                    $results->save();
                                    continue;
                                }

                                $nucleiurls[] = $results->dirscanUrl;
                                $nuclei_in_task++;
                            }
                        }
                    }
                }

                $tools_amount_nuclei++;
            }

            if( $tools_amount_forbiddenbypass < $max_forbiddenbypass ){
                //403 bypass
                $queues = Queue::find()
                    ->andWhere(['working' => "0"])
                    ->andWhere(['todelete' => "0"])
                    ->andWhere(['instrument' => "10"])
                    ->andWhere(['passivescan' => "0"])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit($max_forbiddenbypass_in_task)
                    ->all();

                foreach ($queues as $results) {

                    if ($results != NULL) {

                        if ($tools_amount_forbiddenbypass < $max_forbiddenbypass && $forbiddenbypass_in_task <= $max_forbiddenbypass_in_task ) {

                            $results->working = 1;

                            $forbiddenbypassurls[] = $results->dirscanUrl;

                            $queues_array_forbiddenbypass[] = $results->id;

                            $results->save();

                            $forbiddenbypass_in_task++;
                        }
                    }
                }
            }

            if( $tools_amount_authbypass < $tools_amount_authbypass ){
                //401 http basic bypass
                $queues = Queue::find()
                    ->andWhere(['working' => "0"])
                    ->andWhere(['todelete' => "0"])
                    ->andWhere(['instrument' => "11"])
                    ->andWhere(['passivescan' => "0"])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit(50)
                    ->all();

                foreach ($queues as $results) {

                    if ($results != NULL) {

                        $results->working = 1;

                        $authbypassurls[] = $results->dirscanUrl;

                        $queues_array_authbypass[] = $results->id;

                        $results->save();

                        $authbypass_in_task++;
                    }
                }
            }

            if( $tools_amount_amass < $max_passive_amass ){
                //Amasses
                $queues = Queue::find()
                    ->andWhere(['working' => "0"])
                    ->andWhere(['todelete' => "0"])
                    ->andWhere(['instrument' => "2"])
                    ->andWhere(['passivescan' => "1"])
                    ->orderBy(['id' => SORT_ASC])
                    ->limit($max_passive_amass)
                    ->all();

                foreach ($queues as $results) {

                    if ($results != NULL) {

                        if ($tools_amount_amass < $max_passive_amass ) {

                            $results->working  = 1;

                            $url = $results->amassdomain;

                            exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\' --data "url=' . $url . ' &queueid=' . $results->id . '&scanid=' . $results->taskid . '&secret=' . $secret . '" https://app/passive/amass > /dev/null 2>/dev/null &');

                            $results->save();

                            $tools_amount_amass++;
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

                                    exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\'  --data "active=1&queueid=' . $results->id . '&taskid=' . $results->taskid . ' & secret=' . $secret . '" https://app/scan/gitscan > /dev/null 2>/dev/null &');

                                    $results->save();

                                    $tools_amount->gitscan = $tools_amount->gitscan+1;
                                }
                            }
                        }
                    
                }
            } */



                    /*
                    if ($results->passivescan == 1){

                            if (strpos($results->instrument, "1") !== false) {

                                if ($tools_amount->nmap < 0) { //turned off

                                    $results->working = 1;

                                    exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\' --data "url=' . $results->nmap . ' & scanid=' . $results->taskid . ' & secret=' . $secret . '" https://app/passive/nmap > /dev/null 2>/dev/null &');

                                    $results->save();

                                    $tools_amount->nmap = $tools_amount->nmap+1;
                                }
                            }

                            if (strpos($results->instrument, "2") !== false) {

                                if ($tools_amount_amass < 0) { //turned off

                                    $results->working  = 1;

                                    exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\' --data "url=' . $results->amassdomain . ' & scanid=' . $results->taskid . ' & secret=' . $secret . '" https://app/passive/amass > /dev/null 2>/dev/null &');

                                    $results->save();

                                    $tools_amount_amass++;
                                }
                            }

                            if (strpos($results->instrument, "3") !== false) {

                                if ($tools_amount_ffuf < 0) {

                                    $results->working = 1;

                                    if ($dirscanip != "") {

                                        exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\' --data "ip=' . $results->dirscanIP . ' & url= ' . $results->dirscanUrl . ' & scanid=' . $results->taskid . ' & secret=' . $secret . '" https://app/passive/dirscan > /dev/null 2>/dev/null &');

                                    } else {
                                        
                                        exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\' --data "url=' . $results->dirscanUrl . ' & scanid=' . $results->taskid . ' & secret=' . $secret . '" https://app/passive/dirscan > /dev/null 2>/dev/null &');

                                    }
                                    $results->save();

                                    $tools_amount_ffuf++;
                                }
                            }
                    }*/

            $tools_amount->amass = $tools_amount_amass;

            $tools_amount->dirscan = $tools_amount_ffuf;

            
            //first we get a lot of results into one big url list and then create only 1 docker container to scan all these links. it really saves a lot of memory.
            if ( !empty($nucleiurls) ) {
                exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\'  --data "url=' . implode( PHP_EOL, $nucleiurls ) . '&queueid=' . implode( PHP_EOL, $queues_array_nuclei )
                    . '&secret=' . $secret  . '" https://app/scan/nuclei >/dev/null 2>/dev/null &');
            }

            if ( !empty($ffufurls) ) {
                exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\'  --data \'url=' . json_encode( $ffufurls )
                    . '&secret=' . $secret  . '\' https://app/scan/dirscan >/dev/null 2>/dev/null &');
            }

            if ( !empty($whatweburls) ) {

                $randomid = rand(100000, 900000000000);
                $scanIPS = "/dockerresults/" . $randomid . "aquatoneinput.txt";
                file_put_contents($scanIPS, implode( PHP_EOL, $whatweburls ) );

                exec("sudo chmod 777 ".$scanIPS);

                exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\'  --data "randomid=' . $randomid
                    . '&secret=' . $secret  . '" https://app/scan/aquatone >/dev/null 2>/dev/null &'); 

                exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\'  --data "queueid=' . implode( PHP_EOL, $queues_array_whatweb ) . '&randomid=' . $randomid
                    . '&secret=' . $secret  . '" https://app/scan/jsa >/dev/null 2>/dev/null &'); 

                exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\'  --data "url=' . implode( PHP_EOL, $whatweburls ) . '&queueid=' . implode( PHP_EOL, $queues_array_whatweb )
                    . '&secret=' . $secret  . '" https://app/scan/whatweb >/dev/null 2>/dev/null &');

                $tools_amount_whatweb++;
            }

            if ( !empty($forbiddenbypassurls) ) {
                exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\'  --data "url=' . implode( PHP_EOL, $forbiddenbypassurls ) . '&queueid=' . implode( PHP_EOL, $queues_array_forbiddenbypass )
                    . '&secret=' . $secret  . '" https://app/scan/forbiddenbypass >/dev/null 2>/dev/null &');

                $tools_amount_forbiddenbypass++;
            }

            if ( !empty($nmapips) ) {
                //we put ips to file because curl cant send 10000+ ips.
                $randomid = rand(100000, 900000000000);
                $scanIPS = "/dockerresults/" . $randomid . "nmapinputips.txt";
                file_put_contents($scanIPS, implode( PHP_EOL, $nmapips ) );

                exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\'  --data "randomid=' . $randomid . '&queueid=' . implode( PHP_EOL, $queues_array_nmap )
                    . '&secret=' . $secret  . '" https://app/scan/nmap >/dev/null 2>/dev/null &'); 
            }

            if ( !empty($authbypassurls) ) {
                exec('curl --insecure -H \'Connection: close\' --max-time 15 -H \'Authorization: ' . $auth . '\'  --data "url=' . implode( PHP_EOL, $authbypassurls ) . '&queueid=' . implode( PHP_EOL, $queues_array_authbypass )
                    . '&secret=' . $secret  . '" https://app/scan/authbypass >/dev/null 2>/dev/null &');

                $tools_amount_forbiddenbypass++;
            }

            $tools_amount->save(); 
        } else return Yii::$app->response->statusCode = 403;
    }

       /**
     * {@inheritdoc}
     */
    public $enableCsrfValidation = false;


    /**
     *
     * checks active scan result and sends emails
     *
     */

    public static function actionActive()
    {

        $secret = getenv('api_secret') ?: 'secretkeyzzzzcbv55';
        $auth = getenv('Authorization') ?: 'Basic bmdpbng6QWRtaW4=';

        $secretIN = Yii::$app->request->post('secret');

        if ($secret === $secretIN) {

            $results = Tasks::find()
                ->select(['tasks.taskid','tasks.notify_instrument', 'tasks.nmap_status','tasks.amass_status', 'tasks.dirscan_status','tasks.gitscan_status', 'tasks.reverseip_status','tasks.ips_status', 'tasks.vhost_status'])
                ->where(['!=', 'status', 'Done.'])
                ->limit(5000)
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
    public static function actionPassive()
    {
        $secret = getenv('api_secret') ?: 'secretkeyzzzzcbv55';

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

                            if ( !empty($result->nmap_previous) && !empty($result->nmap_new) ){
                                $diff = array_unique(array_diff($result->nmap_new,$result->nmap_previous));
                            }
                        }

                        if ($pos = strpos($result->notify_instrument, "2") !== false) {

                            if ( !empty($result->amass_previous) && !empty($result->amass_new) ) {

                                $diff = array_unique(array_diff( json_decode($result->amass_new) , json_decode($result->amass_previous)) );
                            }
                        }

                        if ($pos = strpos($result->notify_instrument, "3") !== false) {

                            if ( !empty($result->dirscan_previous) && !empty($result->dirscan_new) ){
                                $diff = array_unique(array_diff( json_decode($result->dirscan_new) , json_decode($result->dirscan_previous)) );
                            }
                        }

                        $result->needs_to_notify = 0;

                        $result->notify_instrument = 0;

                        $result->user_notified = 1;

                        $result->save(false);

                        /*if ($diff != ""){
                            $this->sendPassiveSlack($result->scanid, $diff);
                        }*/
                        
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

    private static function dontscan($url)
    {
        $dontscan = 0;

        if (preg_match("/.*filed.*.my.mail.ru/i", $url) === 1) {
            $dontscan=1; //scanning cdn is pointless
        }

        if (preg_match("/.*cs.*.vk.me/i", $url) === 1) {
            $dontscan=1; //scanning cdn is pointless
        }

        if (preg_match("/.*wg\d*.ok.ru/i", $url) === 1) {
           $dontscan=1; //scanning cdn is pointless
        }

        if (preg_match("/.*.storage.yandex.net/i", $url) === 1) {
           $dontscan=1; //scanning cdn is pointless
        }

        if (preg_match("/.*cdn.*strm.yandex.net/i", $url) === 1) {
           $dontscan=1; //scanning cdn is pointless
        }

        return $dontscan;
    }




}
