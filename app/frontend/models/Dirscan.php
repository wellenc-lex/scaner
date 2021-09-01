<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
require_once 'Nuclei.php';

class Dirscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function ParseHostname($url)
    {
        $url = strtolower($url);

        preg_match("/(https?:\/\/)?([a-zA-Z-\d\.\/]*)/", $url, $domain); //get hostname only
        
        return $domain[2]; //group 2 == domain name
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

        preg_match("/(https?:\/\/)([a-z-\d\.]*)(:\d*)/", $url, $port); //get hostname only
        
        return $port[3]; //group  == port
    }

    public function FindScheme($url)
    {
        //We need to find out which ports are alive to find virtual hosts on them
        $schemes = ["http://", "https://"];
        $returnscheme = array();

        preg_match("/(https?:\/\/)?([a-zA-Z-\d\.\/]*)/", $url, $domain); //get hostname only

        foreach ($schemes as $scheme){

            $schemestatus = exec('curl --insecure --connection-timeout 30 -sL -w "%{http_code}\n" '. escapeshellarg($scheme . $domain[2]) .' -o /dev/null');

            if($schemestatus != "000" && $schemestatus>1 && $schemestatus != "400"){
                $returnscheme = $scheme;
            }                 
        }

        return $returnscheme;
    }

    public function jsa($scheme,$url,$port, $randomid)
    {

        $command = "sudo docker run --cpu-shares 512 --rm -v ffuf:/ffuf 5631/jsa " . escapeshellarg($scheme.$url.$port) . " " . $randomid . " ";

        exec($command);

        if (file_exists("/ffuf/" . $randomid . "/linkfinder.html")) {
            $linkfinder = file_get_contents("/ffuf/" . $randomid . "/linkfinder.html");
        } else $linkfinder="linkfinder error no file";

        if (file_exists("/ffuf/" . $randomid . "/secretfinder.html")) {
            $secretfinder = file_get_contents("/ffuf/" . $randomid . "/secretfinder.html");
        } else $secretfinder="secretfinder error no file";

        $return = base64_encode($secretfinder.$linkfinder); //both htmls in one variable encoded so there will be no error with inserting into db
        
        return $return; 
    }

    public function savetodb($taskid, $hostname, $outputarray, $nuclei, $jsanalysis, $wayback_result)
    {
        if($jsanalysis=="c2VjcmV0ZmluZGVyIGVycm9yIG5vIGZpbGVsaW5rZmluZGVyIGVycm9yIG5vIGZpbGU=" && $outputarray=="" && $nuclei==""){
            return 1; //no need to save empty results
        }

        try{
            Yii::$app->db->open();

            $decrement = ToolsAmount::find()
                ->where(['id' => 1])
                ->one();

            $value = $decrement->dirscan;
            
            if ($value <= 1) {
                $value=0;
            } else $value = $value-1;

            $decrement->dirscan=$value;
            $decrement->save();

            $dirscan = Tasks::find()
                ->where(['taskid' => $taskid])
                ->limit(1)
                ->one();

            if(!empty($dirscan)){ //if task exists in db

                $dirscan->dirscan_status = "Done.";
                $dirscan->dirscan = $outputarray;
                $dirscan->nuclei = json_encode($nuclei);
                $dirscan->js = $jsanalysis;
                $dirscan->wayback = $wayback_result;
                $dirscan->date = date("Y-m-d H-i-s");

                $dirscan->save();
                
            } else {
                $dirscan = new Tasks();
                $dirscan->taskid = $taskid;
                $dirscan->host = $hostname;
                $dirscan->dirscan_status = "Done.";
                $dirscan->dirscan = $outputarray;
                $dirscan->nuclei = json_encode($nuclei);
                $dirscan->js = $jsanalysis;
                $dirscan->wayback = $wayback_result;
                $dirscan->date = date("Y-m-d H-i-s");

                $dirscan->save();
            }

            exec("sudo rm -r /ffuf/" . $randomid . "/");
        } catch (\yii\db\Exception $exception) {

                sleep(1000);
                $dirscan = new Tasks();
                $dirscan->taskid = $taskid;
                $dirscan->host = $hostname;
                $dirscan->dirscan_status = "Done.";
                $dirscan->dirscan = $outputarray;
                $dirscan->nuclei = json_encode($nuclei);
                $dirscan->js = $jsanalysis;
                $dirscan->wayback = $wayback_result;
                $dirscan->date = date("Y-m-d H-i-s");

                $dirscan->save();
                return $exception.$outputarray.$nuclei.$jsanalysis.$wayback_result;
            }
    }


    public function Wayback($url)
    {
        $wayback_result = array();
        $string = array();
            
        exec("curl \"http://web.archive.org/cdx/search/cdx?url=". "*." . $url . "/*" . "&output=list&fl=original&collapse=urlkey\"", $wayback_result);

        foreach ($wayback_result as $id => $result) {
            //wayback saves too much (js,images,xss payloads)
            if(preg_match("/(icons|image|img|images|css|fonts|font-icons|robots.txt|.png|.jpeg|.jpg|.svg|%22|\"|\">|<|<\/|\<\/|%20| |%0d%0a)/i", $result) === 1 ){
                unset($wayback_result[$id]);
                continue;
            }
        }

        //Alienvault
        $id=1;
        $alienvault_pages[$id] = shell_exec("curl 'https://otx.alienvault.com/api/v1/indicators/hostname/" . $url . "/url_list?limit=50&page=" . $id . "'");

        while ((strpos($alienvault_pages[$id], '"has_next": true') !== false) && $id<50) {
            $id++;
            $alienvault_pages[$id] = shell_exec("curl 'https://otx.alienvault.com/api/v1/indicators/hostname/" . $url . "/url_list?limit=50&page=" . $id . "'");
        }
        
        for ($i=1;$i<$id;$i++) {

            $alienvault_json = json_decode($alienvault_pages[$i], true);
        
            foreach ($alienvault_json["url_list"] as $alienvault_urls) {

            if(preg_match("/(icons|image|img|images|css|gif|tiff|woff|woff2|fonts|font-icons|robots.txt|.png|.jpeg|.jpg|%22|\"|\">|<|<\/|\<\/|%20| |%0d%0a|mp4|webm|.svg)/i", $alienvault_urls["url"]) === 1 ){ continue; } else $wayback_result[] = $alienvault_urls["url"];
            }  
        }

        //commoncrawl 
        $crawlurl = json_decode(shell_exec("curl http://index.commoncrawl.org/collinfo.json"), true);
            
        $commoncrawl = shell_exec("curl '" . $crawlurl[0]["cdx-api"] . "?output=json&url=*." . $url . "/*'");

        $commoncrawl = str_replace("}
{", "},{", $commoncrawl);

        $commoncrawl = '[' . $commoncrawl . ']';

        $commoncrawl = json_decode($commoncrawl, true);

        if(is_array($commoncrawl)){
            foreach ($commoncrawl as $id) {

                   if ($id["status"] == 200 || $id["status"] == 204 || $id["status"] == 302 || $id["status"] == 500){
                       if(preg_match("/(icons|image|img|images|css|gif|tiff|woff|woff2|fonts|font-icons|robots.txt|.png|.jpeg|.jpg|%22|\"|\">|<|<\/|\<\/|%20| |%0d%0a|mp4|webm|.svg)/i", $id["url"]) === 1 ){
                           continue;
                       } else $wayback_result[] = $id["url"];
                   } 

            }
        } //else $wayback_result[] = "Not an array.";

        $wayback_result = array_unique($wayback_result);

        foreach ($wayback_result as $key => $url) {
            if(preg_match("/(.js)/i", $url) === 1 ){
                unset($wayback_result[$key]);
            }
        }

        $wayback_result = array_map('htmlentities', $wayback_result);
        $wayback_result = json_encode($wayback_result, JSON_UNESCAPED_UNICODE);

        return $wayback_result;
    }

    public static function dirscan($input)
    {
        
        $hostname = dirscan::ParseHostname($input["url"]);

        $port = dirscan::ParsePort($input["url"]);

        if ($port != "") $port = dirscan::ParsePort($input["url"]); else $port = "";

        $taskid = (int) $input["taskid"];

        $randomid = $taskid;

        if (strpos($input["url"], 'https://') !== false) {
            $scheme = "https://";
            
            if (strpos($input["url"], 'http://') !== false) {
                $scheme = "http://";
            } else {
                $scheme = dirscan::FindScheme($input["url"]);
            }
        }

        $hostname = trim($hostname, ' ');
        $hostname = rtrim($hostname, '/');

        $hostname = trim($hostname, ' ');
        $port = trim($port, ' ');

        $domainfull = substr($hostname, 0, strrpos($hostname, ".")); //hostname without www. and .com at the end

        $hostonly = preg_replace("/(\w)*\./", "", $domainfull); //hostname without subdomain and .com at the end

        if ($domainfull == $hostonly) $hostonly = ""; //remove duplicate extension from scan

        $extensions = "log,php,asp,aspx,jsp,py,txt,conf,config,bak,backup,swp,old,db,sql,com,zip,tar,rar,tgz,tar.gz,".$hostname.",".$domainfull.",".$hostonly;

        if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $hostname, $matches) == 1) $input["ip"] = $matches[0]; //set ip if wasnt specified by user but is in the url

        global $headers;

        $headers = " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1' -H 'X_IMFORWARDS: 127.0.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'ZCACHE_CONTROL: 127.0.0.1' -H 'Connection: close, X-Real-IP' ";

        if (!isset($input["ip"])) {
            $start_dirscan = "sudo mkdir /ffuf/" . $randomid . "/ && sudo docker run --rm --cpu-shares 512 --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$hostname.$port."/FUZZ") . " -t 1 -p 1 -timeout 30 -mc all -w /configs/dict.txt  " . $headers . "  -r -D -e " . escapeshellarg($extensions) . " -o /ffuf/" . $randomid . "/" . $randomid . ".json -od /ffuf/" . $randomid . "/ -of json";

            $wayback_result = dirscan::Wayback($hostname);
        }

        if (isset($input["ip"])) {

            $ip = dirscan::ParseIP($input["ip"]);

            $start_dirscan = "sudo docker run --cpu-shares 512 --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -t 1 -p 3 -H " . escapeshellarg('Host: ' . $hostname) . " " . $headers . " -H 'CF-Connecting-IP: 127.0.0.1' -mc all -w /configs/dict.txt -r -ac -D -e " . escapeshellarg($extensions) . " -o /ffuf/" . $randomid . "/" . $randomid . ".json -od /ffuf/" . $randomid . "/ -of json ";

            $start_dirscan_localhost = "sudo mkdir /ffuf/" . $randomid . " && sudo docker run --cpu-shares 512 --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -t 1 -p 1 " . $headers . " -H 'Host: localhost' -H 'CF-Connecting-IP: 127.0.0.1' -mc all -w /configs/dict.txt -r -ac -D -e " . escapeshellarg($extensions) . " -o /ffuf/" . $randomid . "/" . $randomid . "localhost.json -od /ffuf/" . $randomid . "/ -of json ";

            exec($start_dirscan_localhost); 
        }

        exec($start_dirscan);
        
        //Get dirscan results file from volume
        if (file_exists("/ffuf/" . $randomid . "/" . $randomid . ".json")) {
            $output = json_decode(file_get_contents("/ffuf/" . $randomid . "/" . $randomid . ".json"), true);

            $outputarray = array();
            $id=0;
            $result_length = array();

            foreach ($output["results"] as $results) {
                if ($results["length"] >= 0 && !in_array($results["length"], $result_length)){
                    $id++;
                    $result_length[] = $results["length"];//so no duplicates gonna be added
                    $outputarray[$id]["url"] = $results["url"];
                    $outputarray[$id]["length"] = $results["length"];
                    $outputarray[$id]["status"] = $results["status"];
                    $outputarray[$id]["redirect"] = $results["redirectlocation"];

                    if ($results["length"] < 350000 ){
                        exec("sudo chmod -R 777 /ffuf/" . $randomid . "/");

                        $outputarray[$id]["resultfile"] = base64_encode(file_get_contents("/ffuf/" . $randomid . "/" . $results["resultfile"] . ""));
                    }
                }
            }
            $outputdirscan = $outputarray;
        } else {


            /*sleep(1800);
            exec($start_dirscan);

                if (file_exists("/ffuf/" . $randomid . "/" . $randomid . ".json")) {
                    $output = json_decode(file_get_contents("/ffuf/" . $randomid . "/" . $randomid . ".json"), true);

                    $outputarray = array();
                    $id=0;
                    $result_length = array();

                    if( isset($output["results"]) ) {
                            foreach ($output["results"] as $results) {
                            if ($results["length"] >= 0 && !in_array($results["length"], $result_length)){
                                $id++;
                                $result_length[] = $results["length"];//so no duplicates gonna be added
                                $outputarray[$id]["url"] = $results["url"];
                                $outputarray[$id]["length"] = $results["length"];
                                $outputarray[$id]["status"] = $results["status"];
                                $outputarray[$id]["redirect"] = $results["redirectlocation"];

                                if ($results["length"] < 350000 ){
                                    exec("sudo chmod -R 777 /ffuf/" . $randomid . "/");

                                    $outputarray[$id]["resultfile"] = base64_encode(file_get_contents("/ffuf/" . $randomid . "/" . $results["resultfile"] . ""));
                                }
                            }
                        } $outputdirscan = $outputarray;

                    } else $outputarray = "No file.";

                } else */$outputarray = "No file.";
        }
        

        //Get localhost dirscan results file from volume
        if (file_exists("/ffuf/" . $randomid . "/" . $randomid . "localhost.json")) {
            $output = json_decode(file_get_contents("/ffuf/" . $randomid . "/" . $randomid . "localhost.json", true));

            $output_localhost_array = array();
            $id=0;
            $result_length = array();

            if( isset($output["results"]) ) {
                foreach ($output["results"] as $results) {
                    if ($results["length"] >= 0 && !in_array($results["length"], $result_length)){
                        $id++;
                        $result_length[] = $results["length"];//so no duplicates gonna be added
                        $output_localhost_array[$id]["url"] = $results["url"];
                        $output_localhost_array[$id]["length"] = $results["length"];
                        $output_localhost_array[$id]["status"] = $results["status"];
                        $output_localhost_array[$id]["redirect"] = $results["redirectlocation"];
                        $output_localhost_array[$id]["localhost"] = 1;

                        if ($results["length"] < 350000 ){
                            exec("sudo chmod -R 777 /ffuf/" . $randomid . "/");

                            $output_localhost_array[$id]["resultfile"] = base64_encode(file_get_contents("/ffuf/" . $randomid . "/" . $results["resultfile"] . ""));
                        }
                    }
                }
            } else $output_localhost_array = "No file.";
        } else $output_localhost_array = "No file.";

        if ($output_localhost_array != "No file." && !is_null($output_localhost_array) ) {
            $outputarray = json_encode(array_merge($outputdirscan,$output_localhost_array));
        } else $outputarray = json_encode($outputdirscan);

        $nuclei = Nuclei::Nucleiscan($scheme,$hostname,$port,$randomid); //starts nuclei scan and stores result json into $nuclei

        $jsanalysis = dirscan::jsa($scheme,$hostname,$port, $randomid);

        dirscan::savetodb($taskid, $hostname, $outputarray, $nuclei, $jsanalysis, $wayback_result);

        return 1;
    }

}

