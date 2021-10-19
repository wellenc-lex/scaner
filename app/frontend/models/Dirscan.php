<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use frontend\models\Nuclei;
use frontend\models\Queue;
require_once 'Nuclei.php';

ini_set('max_execution_time', 0);

class Dirscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function queuedone($queueid)
    {
        $queue = Queue::find()
            ->where(['id' => $queueid])
            ->limit(1)
            ->one();

        if($queue!=""){
            $queue->todelete = 1;
            $queue->save();
        }

        return 1;

    }

    public function savetodb($taskid, $hostname, $outputarray, $gau_result, $scanurl)
    {
        global $randomid;

        if( empty($outputarray) || $outputarray == 'null' || $outputarray == '[null]' || ( count($outputarray) == 1 && $outputarray[0]["status"] == "400" ) ){
            return 1; //no need to save empty results
        }

        $outputarray = json_encode($outputarray);

        try{
            Yii::$app->db->open();
            global $usewordlist;

            if($usewordlist==0){

                $dirscan = Tasks::find()
                    ->where(['taskid' => $taskid])
                    ->limit(1)
                    ->one();

                if(!empty($dirscan) && ($dirscan->dirscan == "")) { //if task exists in db

                    $dirscan->dirscan_status = "Done.";
                    $dirscan->dirscan = $outputarray;
                    $dirscan->notify_instrument = $dirscan->notify_instrument."3";
                    $dirscan->wayback = $gau_result;
                    $dirscan->date = date("Y-m-d H-i-s");

                    $dirscan->save();

                } else {
                    $dirscan = new Tasks();
                    $dirscan->host = $hostname;
                    $dirscan->dirscan_status = "Done.";
                    $dirscan->dirscan = $outputarray;
                    $dirscan->notify_instrument = $dirscan->notify_instrument."3";
                    $dirscan->wayback = $gau_result;
                    $dirscan->date = date("Y-m-d H-i-s");

                    $dirscan->save();
                }
            } else {

                $dirscan = new Tasks();
                $dirscan->host = $hostname;
                $dirscan->dirscan_status = "Done.";
                $dirscan->dirscan = $outputarray;
                $dirscan->notify_instrument = $dirscan->notify_instrument."3";
                $dirscan->wayback = $gau_result;
                $dirscan->date = date("Y-m-d H-i-s");

                $dirscan->save();
            }
           
        } catch (\yii\db\Exception $exception) {

            sleep(2000);
            $dirscan = new Tasks();
            $dirscan->host = $hostname;
            $dirscan->dirscan_status = "Done.";
            $dirscan->notify_instrument = $dirscan->notify_instrument."3";
            $dirscan->dirscan = $outputarray;
            $dirscan->wayback = $gau_result;
            $dirscan->date = date("Y-m-d H-i-s");

            $dirscan->save();
            
            return file_put_contents("/ffuf/error".$randomid, $exception.$outputarray.$gau_result);
        }

        if( $scanurl != "" ){
            //add jsa+nuclei scans to queue
            $queue = new Queue();
            $queue->dirscanUrl = $scanurl;
            $queue->instrument = 9; //jsa
            $queue->save();

            //add jsa scan to queue
            $queue = new Queue();
            $queue->dirscanUrl = $scanurl;
            $queue->instrument = 8; //nuclei
            $queue->save();
        }
  
    }

    public function ParseScheme($url)
    {
        $url = strtolower($url);

        preg_match_all("/(https?:\/\/)?([\w\-\_\d\.][^\/\:\&]+)/i", $url, $domain); //get hostname only
        
        return $domain[1][0]; //group 1 == scheme
    }

    public function ParseHostname($url)
    {
        $url = strtolower($url);

        preg_match_all("/(https?:\/\/)?([\w\-\_\d\.][^\/\:\&]+)/i", $url, $domain); //get hostname only
        
        return $domain[2][0]; //group 2 == domain name

    }

    public function ParseIP($ip)
    {
        $ip = strtolower($ip);

        preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $ip, $ip); //get ip only //weird regex because IP parses throguht regexp earlier - when form submits
      
        return $ip[0]; //everything thats inside regex
    }

    public function ParsePort($url)
    {
        $url = strtolower($url);

        preg_match_all("/(https?:\/\/)?([\w\-\_\d\.][^\/\:]+)/i", $url, $port); //get hostname only

        if(isset($port[2][1]) && $port[2][1]!="") {
            $port = ":".$port[2][1]; 
        } else $port = "";
        
        return $port; // :8443
    }

    public function ReadFFUFResult($filename, $localhost)
    {
        global $randomid;

        if (file_exists($filename)) {
            $output = json_decode(file_get_contents($filename), true);

            $outputarray = array();
            $id=0;
            $result_length = array();

            if( isset($output["results"]) ) {
                exec("sudo chmod -R 777 /ffuf/" . $randomid . "/");
                foreach ($output["results"] as $results) {
                    if ( $results["length"] >= 0 && !in_array($results["length"], $result_length) ){
                        $id++;
                        $result_length[] = $results["length"];//so no duplicates gonna be added
                        $outputarray[$id]["url"] = $results["url"];
                        $outputarray[$id]["length"] = $results["length"];
                        $outputarray[$id]["status"] = $results["status"];
                        $outputarray[$id]["redirect"] = $results["redirectlocation"];
                        if($localhost==1) $outputarray[$id]["localhost"] = 1;

                        if ($results["length"] < 350000 ){

                            $resultfilename = "/ffuf/" . $randomid . "/" . $results["resultfile"] . "";

                            if (file_exists($resultfilename)) {
                                $outputarray[$id]["resultfile"] = base64_encode(file_get_contents($resultfilename));
                            }
                        }
                    }
                }
            } 
        } 

        return $outputarray;
    }

    public function Gau($url, $randomid)
    {
        //Get subdomains from gau
        $name="/ffuf/" . $randomid . "/" . $randomid . "gau.txt";

        $blacklist = "'js,eot,jpg,jpeg,gif,css,tif,tiff,png,ttf,otf,woff,woff2,ico,pdf,svg,txt,ico,icons,images,img,images,fonts,font-icons'";

        $gau = "sudo docker run --cpu-shares 512 --rm -v ffuf:/ffuf 5631/gau gau -b ". $blacklist ." -t 1 -retries 25 -o ". $name ." " . escapeshellarg($url) . " ";

        exec($gau);

        exec("sudo chmod -R 777 /ffuf/" . $randomid . "/ &");

        if( file_exists($name) ){
            $gau_result = explode("\n", file_get_contents($name));

            foreach ($gau_result as $id => $result) {
                //wayback saves too much (js,images,xss payloads)
                if(preg_match("/(%22|\"|\">|<|<\/|\<\/|%20| |%0d%0a)/i", $result) === 1 ){
                    unset($gau_result[$id]);
                }
            }

            $gau_result = array_map('htmlentities', $gau_result);
            $gau_result = json_encode($gau_result, JSON_UNESCAPED_UNICODE);
        }
        return $gau_result;
    }

    public static function dirscan($input)
    {
        global $headers; global $usewordlist; global $randomid;

        $headers = " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1' -H 'X_IMFORWARDS: 127.0.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'ZCACHE_CONTROL: 127.0.0.1' ";

        if( $input["url"] != "") $urls = explode(PHP_EOL, $input["url"]); else return 0; //no need to scan without supplied url

        foreach ($urls as $currenturl){

            $hostname = dirscan::ParseHostname($currenturl);

            $port = dirscan::ParsePort($currenturl);

            $taskid = (int) $input["taskid"]; if($taskid=="") $taskid = 10;

            $usewordlist = $input["wordlist"];

            $randomid = rand(60000,100000000);

            if (strpos($currenturl, 'https://') !== false) {
                $scheme = "https://";
            } else $scheme = "http://";

            $hostname = trim($hostname, ' ');
            $hostname = rtrim($hostname, '/');

            $hostname = trim($hostname, ' ');
            $port = trim($port, ' ');

            if( $scheme=="http://" && ($port==":443") ){
                dirscan::queuedone($input["queueid"]);
                return 1; //pointless scan https port with http scheme
                //$scheme="https://"; //httpx found wrong scheme. cant be both http and SSL
            }

            if( $scheme=="http://" && ($port==":8443" || $port==":4443") ){
                $scheme="https://"; //httpx found wrong scheme. cant be both http and SSL
            }

            $domainfull = substr($hostname, 0, strrpos($hostname, ".")); //hostname without www. and .com at the end

            $hostonly = preg_replace("/(\w)*\./", "", $domainfull); //hostname without subdomain and .com at the end

            if ($domainfull == $hostonly) $hostonly = ""; //remove duplicate extension from scan

            $extensions = "log,php,asp,aspx,jsp,py,txt,conf,config,bak,backup,swp,old,db,sql,com,zip,tar,rar,tgz,tar.gz,".$hostname.",".$domainfull.",".$hostonly;

            if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $hostname, $matches) == 1) $input["ip"] = $matches[0]; //set IP if wasnt specified by user but is in the url


            exec("sudo mkdir /ffuf/" . $randomid . "/ "); //create dir for ffuf scan results
            exec("sudo chmod -R 777 /ffuf/" . $randomid . "/ ");

            $output_ffuf = array();

            $ffuf_output = "/ffuf/" . $randomid . "/" . $randomid . ".json";
            $ffuf_output_localhost = "/ffuf/" . $randomid . "/" . $randomid . "localhost.json";

            $ffuf_string = "sudo docker run --cpu-shares 512 --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -maxtime 180000 -s -fc 429 -fs 612 -maxtime-job 40000 -recursion -recursion-depth 1 -t 1 -p 2 -r";
            
            $general_ffuf_string = $ffuf_string.$headers." -mc all -timeout 10 -w /configs/dict.txt:FUZZ -ac -D -e " . escapeshellarg($extensions) . " -od /ffuf/" . $randomid . "/ -of json ";

            if (!isset($input["ip"])) {
                $start_dirscan = $general_ffuf_string . " -u " . escapeshellarg($scheme.$hostname.$port."/FUZZ") . " -o " . $ffuf_output . " ";
                
                if ( $port == "" ) $gau_result = dirscan::gau($hostname, $randomid); //no need to gau service on specific port. there will be no valid results
            }

            if (isset($input["ip"])) {

                $ip = dirscan::ParseIP($input["ip"]);

                $start_dirscan = $general_ffuf_string ." -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -H " . escapeshellarg('Host: ' . $hostname) . " -H 'CF-Connecting-IP: 127.0.0.1' -o " . $ffuf_output . "";

                $start_dirscan_localhost = $general_ffuf_string . " -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -p 1  -H 'Host: localhost' -H 'CF-Connecting-IP: 127.0.0.1' -o " . $ffuf_output_localhost . "";

                exec($start_dirscan_localhost);
                $output_ffuf[] = dirscan::ReadFFUFResult($ffuf_output_localhost, 1);
            }

            exec($start_dirscan);

            $output_ffuf[] = dirscan::ReadFFUFResult($ffuf_output, 0);

            if($usewordlist=="1"){

                //ffuf /site.com/subdomain.site.com/ from amass/gau wordlist
                $task = Tasks::find()
                        ->where(['taskid' => $taskid])
                        ->limit(1)
                        ->one();

                Yii::$app->db->close();  
                if($task){
                    $vhostwordlist = json_decode($task->vhostwordlist, true);

                    if (!empty($vhostwordlist)) {
                        $hostsfile = "/ffuf/" . $randomid . "/" . $randomid . "domains.txt";
                        file_put_contents($hostsfile, implode( PHP_EOL, $vhostwordlist) ); //to use domains supplied by user as FFUF wordlist

                        $start_dirscan = $ffuf_string . " -mc all -timeout 20 -ac -D -e " . escapeshellarg($extensions) . $headers . " -u " . escapeshellarg($scheme.$hostname.$port."/HOSTS/") . " -w " . $hostsfile . ":HOSTS -o " . $ffuf_output . " ";
                        exec($start_dirscan);

                        $output_ffuf[] = dirscan::ReadFFUFResult($ffuf_output, 0);
                    }
                }
            }

            $output_ffuf = array_unique($output_ffuf);

            $scanurl = $scheme.$hostname.$port;

            exec("sudo rm -r /ffuf/" . $randomid . "/");

            dirscan::savetodb($taskid, $hostname, $output_ffuf, $gau_result, $scanurl);

            dirscan::queuedone($input["queueid"]);

            Yii::$app->db->close();  

        }
        
        return 1;
    }

}


































