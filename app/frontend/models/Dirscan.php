<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use frontend\models\Nuclei;
use frontend\models\Queue;

ini_set('max_execution_time', 0);

class Dirscan extends ActiveRecord
{
    public static function dirscan($input)
    {
        sleep( rand(2,50) ); //when we create 100+ ffufs in 30 seconds they all execute autocalibrate request, get response timeout and die.

        global $headers; global $usewordlist; global $randomid;

        $headers = " -H 'Accept-Language: en-US;q=0.8,en;q=0.5' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Forwarded-For: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Forwarded: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded-By: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_IMFORWARDS: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'l5d-dtab: /$/inet/169.254.169.254/80' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'HTTP_X_REAL_IP: 127.0.0.1' -H 'HTTP_X_FORWARDED_FOR: 127.0.0.1' -H 'Connection: close' -H 'X-BugBounty-Hackerone: wellenc_lex' "; 

        if( $input["url"] != "") $urls = explode(PHP_EOL, $input["url"]); else return 0; //no need to scan without supplied url

        foreach ($urls as $currenturl){

            //need to rewrite dirscan with bash scripts as vhosts to scan in parallel. right now it will scan another url only after 1st scan dies. (2-4 days)

            if (preg_match("/skill|skgb/i", $currenturl) === 1) {
                $headers = $headers." -H 'Authorization: Basic dGVzdDpza2lsbGJveHRlc3Rpbmc=' ";
            }

            if (preg_match("/.*filed.*.my.mail.ru/i", $currenturl) === 1) {
                dirscan::queuedone($input["queueid"]);
                return 2; //scanning filed cdn is pointless
            }

            if (preg_match("/.*cs.*.vk.me/i", $currenturl) === 1) {
                dirscan::queuedone($input["queueid"]);
                return 2; //scanning filed cdn is pointless
            }

            $currenturl = preg_replace("/[\n\r]/", "", $currenturl);

            $hostname = dirscan::ParseHostname($currenturl);

            $port = dirscan::ParsePort($currenturl);

            $taskid = (int) $input["taskid"]; if($taskid=="") $taskid = 10;

            $usewordlist = $input["wordlist"];

            $randomid = rand(60000,100000000);

            $outputdir = "/ffuf/" . $randomid . "/";

            if (strpos($currenturl, 'https://') !== false) {
                $scheme = "https://";
            } else $scheme = "http://";

            $hostname = rtrim($hostname, '/');

            $scanurl = $scheme.$hostname.$port;

            if( ($scheme==="http://" && $port===":443") || ($scheme==="https://" && $port===":80")){
                dirscan::queuedone($input["queueid"]);
                return 2; //scanning https port with http scheme is pointless
            }

            if( $scheme==="http://" && ($port===":8443" || $port===":4443") ){
                $scheme="https://"; //httpx found wrong scheme. cant be both http and SSL
            }

            if( dirscan::bannedsubdomains($scanurl) !== 0 ){
                //dirscan::addtonuclei($scanurl);
                dirscan::queuedone($input["queueid"]);

                Yii::$app->db->close();
                return 2; //scanning banned subdomains is pointless
            }

            $domainfull = substr($hostname, 0, strrpos($hostname, ".")); //hostname without www. and .com at the end

            $hostonly = preg_replace("/(\w\d\-\_)*\./", "", $domainfull); //hostname without subdomain and .com at the end

            $firstpartofsubdomain = preg_match('/^[\w\d\-\_]+/', $hostname, $matches); 

            $firstpartofsubdomain = $matches[0];

            if ($domainfull == $hostonly) $hostonly = ""; //remove duplicate extension from scan

            if ( ($firstpartofsubdomain == $hostonly) || ($firstpartofsubdomain == $domainfull) ) $firstpartofsubdomain = ""; //remove duplicate extension from scan

            $extensions = "_,0,~1,1,2,3,bac,cache,cs,csproj,err,inc,ini,log,php,asp,aspx,jsp,py,txt,tmp,conf,config,bak,backup,swp,old,db,sql,com,bz2,zip,tar,rar,tgz,js,json,tar.gz,~,".$hostname.",".$domainfull.",".$hostonly.",".$firstpartofsubdomain;

            if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $hostname, $matches) == 1) $input["ip"] = $matches[0]; //set IP if wasnt specified by user but is in the url

            exec("sudo mkdir " . $outputdir . " "); //create dir for ffuf scan results
            exec("sudo chmod -R 777 " . $outputdir . " ");

            $output_ffuf = array();

            $ffuf_output = "" . $outputdir . "" . $randomid . ".json";
            $ffuf_output_localhost = "" . $outputdir . "" . $randomid . "localhost.json";

            //$ffuf_string = "sudo docker run --dns 8.8.4.4 --cpu-shares 256 --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ sneakerhax/ffuf -maxtime 350000 -fc 429,503,400 -fs 612,613,548 -s -timeout 40 -recursion -recursion-depth 1 -t 1 -p 2 -r -fr 'Vercel|Too Many Requests|stand by|blocked by|Blocked by|Please wait while|incapsula' -ac -acc 'randomtest' -noninteractive ";

            $ffuf_string = "/tmp/ffuf.binary -maxtime 750000 -fc 504,404,429,503,400,502,406,520,522 -fs 612,613,548,26,25,0,696956 -s -timeout 280 -recursion -recursion-depth 1 -t 1 -p 1.5  -fr 'Selligent Marketing Cloud|Incapusla Incident|shopify|okta|medium|Vercel|Too Many Requests|stand by|blocked by|Blocked by|Please wait while|incapsula|Thank you for using nginx|Welcome to nginx|Scan your infrastructure with us' -r -ac -noninteractive ";
            
            $general_ffuf_string = $ffuf_string.$headers." -mc all -w /configs/dict.txt:FUZZ -D -e " . escapeshellarg($extensions) . " -od " . $outputdir . " -of json ";

            if (!isset($input["ip"])) {
                $start_dirscan = $general_ffuf_string . " -u " . escapeshellarg($currenturl."/FUZZ") . " -o " . $ffuf_output . " ";

                //escapeshellarg($scheme.$hostname.$port."/FUZZ")
                /*
                if (isset($input["directurl"])) {
                    $start_dirscan = $start_dirscan . " -u " . escapeshellarg($currenturl."/FUZZ") . " ";
                }*/
            }

            if (isset($input["ip"])) {

                $ip = dirscan::ParseIP($input["ip"]);

                $start_dirscan = $general_ffuf_string ." -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -H " . escapeshellarg('Host: ' . $hostname) . " -p 0.2 -H 'CF-Connecting-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -o " . $ffuf_output . "  > /dev/null ";

                $start_dirscan_localhost = $general_ffuf_string . " -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -p 0.2  -H 'Host: localhost' -H 'CF-Connecting-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -o " . $ffuf_output_localhost . " -ac=0  > /dev/null ";

                exec($start_dirscan_localhost);
                $output_ffuf[] = dirscan::ReadFFUFResult($ffuf_output_localhost, 1, $outputdir);
            }

            exec($start_dirscan, $exec_output, $retval);

            if ( array_search("exiting", $exec_output) !== false ) {
                sleep( rand(20,160) );
                exec($start_dirscan);
            }

            $output_ffuf[] = dirscan::ReadFFUFResult($ffuf_output, 0, $outputdir);



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
                        $hostsfile = "" . $outputdir . "" . $randomid . "domains.txt";
                        file_put_contents($hostsfile, implode( PHP_EOL, $vhostwordlist) ); //to use domains supplied by user as FFUF wordlist

                        $start_dirscan = $ffuf_string . " -mc all -ac -D -e " . escapeshellarg($extensions) . $headers . " -u " . escapeshellarg($scheme.$hostname.$port."/HOSTS/") . " -w " . $hostsfile . ":HOSTS -o " . $ffuf_output . " ";
                        
                        exec($start_dirscan, $exec_output);

                        if ( array_search("exiting", $exec_output) !== false ) {
                            sleep( rand(10,30) );
                            exec($start_dirscan);
                        }

                        $output_ffuf[] = dirscan::ReadFFUFResult($ffuf_output, 0, $outputdir);
                    }
                }
            }

            //Create subdomain/domain - based wordlists
            $file = $outputdir . "custom.txt";

            if ( dirscan::makecustomwordlist($hostname, $domainfull, $hostonly, $firstpartofsubdomain, $file) == 1 ) {
                
                $start_dirscan = $ffuf_string.$headers." -mc all -w " . $file . ":FUZZ -D -e " . escapeshellarg($extensions) . " -od " . $outputdir . " -of json "
                . " -u " . escapeshellarg($currenturl."/FUZZ") . " -o " . $ffuf_output;

                exec($start_dirscan, $exec_output, $retval);

                if ( array_search("exiting", $exec_output) !== false ) {
                    sleep( rand(20,160) );
                    exec($start_dirscan);
                }

                $output_ffuf[] = dirscan::ReadFFUFResult($ffuf_output, 0, $outputdir);
            }

            $output_ffuf = array_unique($output_ffuf);

            if ( !isset($input["ip"]) ) {
                if ( $port == "" ) {
                    $gau_result = dirscan::gau($hostname, $randomid, $outputdir); //no need to gau service on specific ip/port. there will be no valid results
                }
            }

            exec("sudo rm -r " . $outputdir . "");

            dirscan::savetodb($taskid, $scheme.$hostname, $output_ffuf, $gau_result, $scanurl);

            dirscan::queuedone($input["queueid"]);

            Yii::$app->db->close();
        }
        
        return 1;
    }

    public static function tableName()
    {
        return 'tasks';
    }

    public static function bannedsubdomains($in)
    {
        if (preg_match("/dev|stage|test|proxy|stg|int|adm|uat/i", $in) === 1) {
            return 0; //if its used for internal or develop purposes - we need to scan it anyway
        } else { 
            return preg_match("/link|support|^ws|wiki|status|docs|help|jira|lync|maintenance|atlassian|autodiscover|grafana|confluence|git|zendesk|sentry|(url(\d)*)/i", $in);
            //returns 1 if string has something from the regexp 
        }
    }

    public static function queuedone($queueid)
    {
        $queue = Queue::find()
            ->where(['id' => $queueid])
            ->limit(1)
            ->one();

        if($queue!=""){
            $queue->todelete = 1;
            $queue->date_modified = date("Y-m-d");
            $queue->save();
        }

        return 1;
    }

    public static function savetodb($taskid, $hostname, $output_ffuf, $gau_result, $scanurl)
    {
        global $randomid;

        array_filter($output_ffuf);

        $ffuf = $output_ffuf;

        $output_ffuf = json_encode($output_ffuf);

        $output_ffuf = preg_replace("/(null,{)|(null,.?\[)/", "", $output_ffuf);

        if( $output_ffuf === 'null' || $output_ffuf === '[null]' || $output_ffuf === '[]' || $output_ffuf === '[[]]' ||  $output_ffuf === '' ||  $output_ffuf === '{}' ||  $output_ffuf === '[{}]'){
            return 1; //save empty results to maaybe rescan manually later
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
                        $forbidden[] = $oneffuf["url"];
                    }
                }

                if ( count($forbidden) < 15 && count($forbidden) >= 1) {
                    
                    foreach($forbidden as $forbiddenurl){

                        //add 403 urls to queue
                        $queue = new Queue();
                        $queue->dirscanUrl = $forbiddenurl;
                        $queue->instrument = 10; //403 bypass
                        $queue->save();
                    }
                }
               
            } catch (\yii\db\Exception $exception) {

                sleep(6000);

                dirscan::savetodb($taskid, $hostname, $output_ffuf, $gau_result, $scanurl);
                Yii::$app->db->close();
                
                return file_put_contents("/dockerresults/".$randomid."error", $output_ffuf);
            }
        }
  
    }

    public static function ParseScheme($url)
    {
        $url = strtolower($url);

        preg_match_all("/(https?:\/\/)?([\w\-\_\d\.][^\/\:\&\[\r\n\?]+)/i", $url, $domain); //get hostname only
        
        return trim( $domain[1][0], ' '); //group 1 == scheme
    }

    public static function ParseHostname($url)
    {
        $url = strtolower($url);

        preg_match_all("/(https?:\/\/)?([\w\-\_\d\.][^\/\:\&\[\r\n\?]+)/i", $url, $domain); //get hostname only
        
        return trim( $domain[2][0], ' '); //group 2 == domain name

    }

    public static function ParseIP($ip)
    {
        $ip = strtolower($ip);

        preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $ip, $ip); //get ip only //weird regex because IP parses throguht regexp earlier - when form submits
      
        return trim( $ip[0], ' '); //everything thats inside regex
    }

    public static function ParsePort($url)
    {
        $url = strtolower($url);

        preg_match_all("/\:[\d[\r\n\?]+/i", $url, $port); //get hostname only

        if(isset($port[0][0]) && $port[0][0]!="") {
            $port = $port[0][0]; 
        } else $port = "";
        
        return trim( $port, ' '); // :8443
    }

    public static function ReadFFUFResult($filename, $localhost, $outputdir)
    {
        global $randomid;

        $status403 = 0; $otherstatus = 0; //we dont need results with only 403 outputs because they are useless.

        if (file_exists($filename)) {
            $output = json_decode(file_get_contents($filename), true);

            $output_ffuf = array();
            $id=0;
            $result_length = array();

            if( isset($output["results"]) ) {
                //exec("sudo chmod -R 777 " . $outputdir . "");
                foreach ($output["results"] as $results) {
                    if ( $results["length"] >= 0 && !in_array($results["length"], $result_length) ){
                        $id++;

                        if ( $results["status"] == 403 || $results["status"] == 522 ) $status403 = 1; else $otherstatus = 1;

                        $result_length[] = $results["length"];//so no duplicates gonna be added
                        $output_ffuf[$id]["url"] = $results["url"];
                        $output_ffuf[$id]["length"] = $results["length"];
                        $output_ffuf[$id]["status"] = $results["status"];
                        $output_ffuf[$id]["redirect"] = $results["redirectlocation"];
                        if($localhost==1) $output_ffuf[$id]["localhost"] = 1;

                        if ($results["length"] < 45000000 ){

                            $resultfilename = "" . $outputdir . "" . $results["resultfile"] . "";

                            if (file_exists($resultfilename)) {
                                $output_ffuf[$id]["resultfile"] = base64_encode(file_get_contents($resultfilename));
                            }
                        }
                    }
                }
            } 
        }

        if ( $status403 == 1 && $otherstatus == 0 ) $output_ffuf = array();

        return $output_ffuf;
    }

    public static function Gau($url, $randomid, $outputdir)
    {
        //Get subdomains from gau
        $name="" . $outputdir . "" . $randomid . "gau.txt";

        $blacklist = "'js,eot,jpg,jpeg,gif,css,tif,tiff,png,ttf,otf,woff,woff2,ico,pdf,svg,txt,ico,icons,images,img,images,fonts,font-icons,mp4,mp3'";

        $gau = "timeout 2000 sudo docker run --cpu-shares 256 --rm -v ffuf:/ffuf sxcurity/gau:latest --blacklist ". $blacklist ." --threads 1 --retries 15 --timeout 290 --fc 504,404,302,301 --o ". $name ." " . escapeshellarg($url) . " ";

        exec($gau);

        //exec("sudo chmod -R 777 " . $outputdir . " &");

        if( file_exists($name) ){
            $gau_result = array_unique( explode("\n", file_get_contents($name) ) );

            foreach ($gau_result as $id => $result) {
                //wayback saves too much junk info (js,images,xss payloads)
                if(preg_match("/(%22|\"|\">|<|<\/|\<\/|%20| |%0d%0a|\<\!\-\-|\<\!\-\-\/\/)/i", $result) === 1 ){
                    unset($gau_result[$id]);
                }
            }

            $gau_result = array_map('htmlentities', $gau_result);
            $gau_result = json_encode($gau_result, JSON_UNESCAPED_UNICODE);
        }
        return $gau_result;
    }

    //www.test.google-stage.com -> www.test.google-stage -> www.test -> www 
    public static function sliceHost($host){
        global $vhostlist;
        STATIC $stop = 1;
        while ($stop != 0){
            $outputValue = preg_replace('/\.(\w[\-\_\d]?)*$/', '', $host, 1, $stop);
            
            array_push($vhostlist, $outputValue);
            dirscan::sliceHost($outputValue);
        }
    }

    public static function dosplit($input){
        //www.test.google.com -> www test google
        global $vhostlist;
        preg_match_all("/(\w[\-\_\d]?)*\./", $input, $out);

        if($out[0][0]!=""){
            $word = implode("", $out[0]);
            $word = rtrim($word, ".");
            $vhostlist[] = $word;
            dirscan::dosplit($word);
        }
    }

    public static function split2($input){
        //www.test.google.com -> www.test -> www
        global $vhostlist;

        preg_match_all("/(\w[\-\_\d]?)*\./", $input, $matches);

        foreach($matches[0] as $match){
            $vhostlist[] = rtrim($match, "."); 
        }
    }

    public static function makecustomwordlist($hostname, $domainfull, $hostonly, $firstpartofsubdomain, $file)
    {
        global $vhostlist;

        $vhostlist[] = $domainfull;
        $vhostlist[] = $hostonly;
        $vhostlist[] = $firstpartofsubdomain;

        dirscan::sliceHost($hostname);

        if (strpos($hostname, 'https://xn--') === false) {
            dirscan::dosplit($hostname);
            dirscan::split2($hostname);
        }
        $vhostlist = array_unique($vhostlist);

        foreach ($vhostlist as $domain) {
            $done[] = $domain . "%EXT%"; //add dirsearch - compatiable extensions for ffuf
        }
        
        if ( !empty($done) ) {
            
            file_put_contents($file, implode( PHP_EOL, $done) );

            return 1;
        } else return 0;
    }
}


































