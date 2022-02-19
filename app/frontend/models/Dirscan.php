<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use frontend\models\Nuclei;
use frontend\models\Queue;

ini_set('max_execution_time', 0);

class Dirscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function bannedsubdomains($in)
    {
        if (preg_match("/dev|stage|test|proxy|stg|int|adm|uat/i", $in) === 1) {
            return 0; //if its used for internal or develop purposes - scan anyway
        } else { 
            return preg_match("/link|img|cdn|sentry|support|^ws|wiki|status|static|blog|socket|docs|help|jira|lync|maintenance|atlassian|autodiscover|grafana|confluence|git|cdn|sentry|url(\d)*/i", $in);
        }
    }

    public function addtonuclei($scanurl)
    {
        if( $scanurl != "" ){
            //add nuclei to queue
            $queue = new Queue();
            $queue->dirscanUrl = $scanurl;
            $queue->instrument = 8; //nuclei
            $queue->save();
        }
        return 1;
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

    public function savetodb($taskid, $hostname, $output_ffuf, $gau_result, $scanurl)
    {
        global $randomid;

        dirscan::addtonuclei($scanurl);

        //|| ( count($output_ffuf) === 1 && $output_ffuf[0]["status"] == "400" )

        array_filter($output_ffuf);

        $ffuf = $output_ffuf;

        $output_ffuf = json_encode($output_ffuf);

        $output_ffuf = preg_replace("/(null,{)|(null,.?\[)/", "", $output_ffuf);

        if( $output_ffuf === 'null' || $output_ffuf === '[null]' || $output_ffuf === '[]' || $output_ffuf === '[[]]' ||  $output_ffuf === '' ||  $output_ffuf === '{}' ||  $output_ffuf === '[{}]'){
            
            $dirscan = new Tasks();
            $dirscan->host = $hostname;
            $dirscan->dirscan_status = "Rescan";
            $dirscan->dirscan = "Rescan";
            $dirscan->notify_instrument = "3";
            $dirscan->hidden = "1";
            $dirscan->date = date("Y-m-d H-i-s");

            $dirscan->save();

            return 1; //save empty results to maaybe scan manually later
        } else {

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
                        $dirscan->dirscan = $output_ffuf;
                        $dirscan->notify_instrument = $dirscan->notify_instrument."3";
                        $dirscan->wayback = $gau_result;
                        $dirscan->date = date("Y-m-d H-i-s");

                        $dirscan->save();

                    } else {
                        $dirscan = new Tasks();
                        $dirscan->host = $hostname;
                        $dirscan->dirscan_status = "Done.";
                        $dirscan->dirscan = $output_ffuf;
                        $dirscan->notify_instrument = $dirscan->notify_instrument."3";
                        $dirscan->wayback = $gau_result;
                        $dirscan->date = date("Y-m-d H-i-s");

                        $dirscan->save();
                    }
                } else {

                    $dirscan = new Tasks();
                    $dirscan->host = $hostname;
                    $dirscan->dirscan_status = "Done.";
                    $dirscan->dirscan = $output_ffuf;
                    $dirscan->notify_instrument = $dirscan->notify_instrument."3";
                    $dirscan->wayback = $gau_result;
                    $dirscan->date = date("Y-m-d H-i-s");

                    $dirscan->save();
                }

                $forbidden = array();

                foreach ($ffuf as $oneffuf) {

                    if ( $oneffuf["status"] == "403") {
                        
                        if (preg_match("/incapsula|checking your browser|Please stand by|%5c|%2e%2e/i", base64_decode( $oneffuf["resultfile"]) ) === 0) {
                            $forbidden[] = $oneffuf["url"];
                        }
                    }
                }

                if ( count($forbidden) < 50 && count($forbidden) > 0) {
                    
                    foreach($forbidden as $forbiddenurl){

                        //add 403 urls to queue
                        $queue = new Queue();
                        $queue->dirscanUrl = $forbiddenurl;
                        $queue->instrument = 10; //403 bypass
                        $queue->save();
                    }
                }
               
            } catch (\yii\db\Exception $exception) {

                sleep(5000);

                dirscan::savetodb($taskid, $hostname, $output_ffuf, $gau_result, $scanurl);
                Yii::$app->db->close();
                
                return file_put_contents("/dockerresults/".$randomid."error", $output_ffuf.$gau_result);
            }
        }
  
    }

    public function ParseScheme($url)
    {
        $url = strtolower($url);

        preg_match_all("/(https?:\/\/)?([\w\-\_\d\.][^\/\:\&\[\r\n\?]+)/i", $url, $domain); //get hostname only
        
        return trim( $domain[1][0], ' '); //group 1 == scheme
    }

    public function ParseHostname($url)
    {
        $url = strtolower($url);

        preg_match_all("/(https?:\/\/)?([\w\-\_\d\.][^\/\:\&\[\r\n\?]+)/i", $url, $domain); //get hostname only
        
        return trim( $domain[2][0], ' '); //group 2 == domain name

    }

    public function ParseIP($ip)
    {
        $ip = strtolower($ip);

        preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $ip, $ip); //get ip only //weird regex because IP parses throguht regexp earlier - when form submits
      
        return trim( $ip[0], ' '); //everything thats inside regex
    }

    public function ParsePort($url)
    {
        $url = strtolower($url);

        preg_match_all("/(https?:\/\/)?([\w\-\_\d\.][^\/\:\&\[\r\n\?]+)/i", $url, $port); //get hostname only

        if(isset($port[2][1]) && $port[2][1]!="") {
            $port = ":".$port[2][1]; 
        } else $port = "";
        
        return trim( $port, ' '); // :8443
    }

    public function ReadFFUFResult($filename, $localhost)
    {
        global $randomid;

        if (file_exists($filename)) {
            $output = json_decode(file_get_contents($filename), true);

            $output_ffuf = array();
            $id=0;
            $result_length = array();

            if( isset($output["results"]) ) {
                exec("sudo chmod -R 777 /ffuf/" . $randomid . "/");
                foreach ($output["results"] as $results) {
                    if ( $results["length"] >= 0 && !in_array($results["length"], $result_length) ){
                        $id++;
                        $result_length[] = $results["length"];//so no duplicates gonna be added
                        $output_ffuf[$id]["url"] = $results["url"];
                        $output_ffuf[$id]["length"] = $results["length"];
                        $output_ffuf[$id]["status"] = $results["status"];
                        $output_ffuf[$id]["redirect"] = $results["redirectlocation"];
                        if($localhost==1) $output_ffuf[$id]["localhost"] = 1;

                        if ($results["length"] < 230000 ){

                            $resultfilename = "/ffuf/" . $randomid . "/" . $results["resultfile"] . "";

                            if (file_exists($resultfilename)) {
                                $output_ffuf[$id]["resultfile"] = base64_encode(file_get_contents($resultfilename));
                            }
                        }
                    }
                }
            } 
        } 

        return $output_ffuf;
    }

    public function Gau($url, $randomid)
    {
        //Get subdomains from gau
        $name="/ffuf/" . $randomid . "/" . $randomid . "gau.txt";

        $blacklist = "'js,eot,jpg,jpeg,gif,css,tif,tiff,png,ttf,otf,woff,woff2,ico,pdf,svg,txt,ico,icons,images,img,images,fonts,font-icons'";

        $gau = "timeout 5000 sudo docker run --cpu-shares 512 --rm -v ffuf:/ffuf sxcurity/gau:latest --blacklist ". $blacklist ." --threads 1 --retries 15 --fc 404,302,301 --o ". $name ." " . escapeshellarg($url) . " ";

        exec($gau);

        exec("sudo chmod -R 777 /ffuf/" . $randomid . "/ &");

        if( file_exists($name) ){
            $gau_result = array_unique( explode("\n", file_get_contents($name) ) );

            foreach ($gau_result as $id => $result) {
                //wayback saves too much (js,images,xss payloads)
                if(preg_match("/(%22|\"|\">|<|<\/|\<\/|%20| |%0d%0a||\<\!\-\-)/i", $result) === 1 ){
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

        $headers = " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Forwarded-For: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Forwarded: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded-By: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_IMFORWARDS: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'l5d-dtab: /$/inet/169.254.169.254/80' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'ZCACHE_CONTROL: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'HTTP_X_REAL_IP: 127.0.0.1' -H 'HTTP_X_FORWARDED_FOR: 127.0.0.1' "; 

        if( $input["url"] != "") $urls = explode(PHP_EOL, $input["url"]); else return 0; //no need to scan without supplied url

        foreach ($urls as $currenturl){

            $currenturl = preg_replace("/[\n\r]/", "", $currenturl);

            $hostname = dirscan::ParseHostname($currenturl);

            $port = dirscan::ParsePort($currenturl);

            $taskid = (int) $input["taskid"]; if($taskid=="") $taskid = 10;

            $usewordlist = $input["wordlist"];

            $randomid = rand(60000,100000000);

            if (strpos($currenturl, 'https://') !== false) {
                $scheme = "https://";
            } else $scheme = "http://";

            $hostname = rtrim($hostname, '/');

            $scanurl = $scheme.$hostname.$port;

            if( dirscan::bannedsubdomains($scanurl) !== 0 ){
                dirscan::addtonuclei($scanurl);
                dirscan::queuedone($input["queueid"]);

                return 2; //scanning banned subdomains is pointless
            }


            if( ($scheme==="http://" && $port===":443") || ($scheme==="https://" && $port===":80")){
                dirscan::queuedone($input["queueid"]);
                return 2; //scanning https port with http scheme is pointless
            }

            if( $scheme==="http://" && ($port===":8443" || $port===":4443") ){
                $scheme="https://"; //httpx found wrong scheme. cant be both http and SSL
            }

            $domainfull = substr($hostname, 0, strrpos($hostname, ".")); //hostname without www. and .com at the end

            $hostonly = preg_replace("/(\w\d\-\_)*\./", "", $domainfull); //hostname without subdomain and .com at the end

            if ($domainfull == $hostonly) $hostonly = ""; //remove duplicate extension from scan

            $extensions = "_,0,~1,1,2,3,bac,cache,cs,csproj,err,inc,ini,log,php,asp,aspx,jsp,py,txt,tmp,conf,config,bak,backup,swp,old,db,sql,com,bz2,zip,tar,rar,tgz,js,json,tar.gz,~,".$hostname.",".$domainfull.",".$hostonly;

            if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $hostname, $matches) == 1) $input["ip"] = $matches[0]; //set IP if wasnt specified by user but is in the url


            exec("sudo mkdir /ffuf/" . $randomid . "/ "); //create dir for ffuf scan results
            exec("sudo chmod -R 777 /ffuf/" . $randomid . "/ ");

            $output_ffuf = array();

            $ffuf_output = "/ffuf/" . $randomid . "/" . $randomid . ".json";
            $ffuf_output_localhost = "/ffuf/" . $randomid . "/" . $randomid . "localhost.json";

            $ffuf_string = "sudo docker run --cpu-shares 512 --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ sneakerhax/ffuf -maxtime 350000 -fc 429,503,400 -fs 612,613 -timeout 20 -recursion -recursion-depth 1 -t 1 -p 2 -r -fr 'Vercel|Too Many Requests|stand by|blocked by|Blocked by|Please wait while|incapsula' -ac -acc 'randomtest' -noninteractive ";
            
            $general_ffuf_string = $ffuf_string.$headers." -mc all -w /configs/dict.txt:FUZZ -D -e " . escapeshellarg($extensions) . " -od /ffuf/" . $randomid . "/ -of json ";

            if (!isset($input["ip"])) {
                $start_dirscan = $general_ffuf_string . " -u " . escapeshellarg($scheme.$hostname.$port."/FUZZ") . " -o " . $ffuf_output . " ";
            }

            if (isset($input["ip"])) {

                $ip = dirscan::ParseIP($input["ip"]);

                $start_dirscan = $general_ffuf_string ." -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -p 0.1 -H " . escapeshellarg('Host: ' . $hostname) . " -p 0.5 -H 'CF-Connecting-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -o " . $ffuf_output . "";

                $start_dirscan_localhost = $general_ffuf_string . " -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -p 0.1  -H 'Host: localhost' -H 'CF-Connecting-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -o " . $ffuf_output_localhost . " -ac=0 ";

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

                        $start_dirscan = $ffuf_string . " -mc all -ac -D -e " . escapeshellarg($extensions) . $headers . " -u " . escapeshellarg($scheme.$hostname.$port."/HOSTS/") . " -w " . $hostsfile . ":HOSTS -o " . $ffuf_output . " ";
                        exec($start_dirscan);

                        $output_ffuf[] = dirscan::ReadFFUFResult($ffuf_output, 0);
                    }
                }
            }

            $output_ffuf = array_unique($output_ffuf);

            if ( !isset($input["ip"]) ) {
                if ( $port == "" ) {
                    if( !empty($output_ffuf) && $output_ffuf !== 'null' && $output_ffuf !== '[null]' && $output_ffuf !== '[]' && $output_ffuf !== '[[]]' 
                        &&  $output_ffuf !== '' &&  $output_ffuf !== '{}' &&  $output_ffuf !== '[{}]' ){

                        //isset($output_ffuf[1]);
                        
                        $gau_result = dirscan::gau($hostname, $randomid); //no need to gau service on specific port. there will be no valid results
                    } 
                }
            }

            exec("sudo rm -r /ffuf/" . $randomid . "/");

            dirscan::savetodb($taskid, $scheme.$hostname, $output_ffuf, $gau_result, $scanurl);

            dirscan::queuedone($input["queueid"]);

            Yii::$app->db->close();  

        }
        
        return 1;
    }

}


































