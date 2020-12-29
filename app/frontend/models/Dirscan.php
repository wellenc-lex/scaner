<?php

namespace frontend\models;

use yii\db\ActiveRecord;

class Dirscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function Nuclei($scheme,$url,$port,$randomid)
    {
        /*if($ip!="0.0.0.0"){
            $nuclei_start = "sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ projectdiscovery/nuclei -target " . escapeshellarg($scheme.$ip.$port."/") . " -H " . escapeshellarg('Host: ' . $url) . " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H ' X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'CF-Connecting-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -o /ffuf/" . $randomid . "/" . $randomid . "nuclei.json -json -timeout 10 -json-requests -t /configs/nuclei-templates -nC -debug -v -c 100 "; } else {*/ // Nuclei can't work with Host: header+ ip :(

        exec("sudo chmod 777 /ffuf/" . $randomid . "/ -R && sudo chmod 777 /ffuf/" . $randomid . " -R");

        $nuclei_start = "sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ projectdiscovery/nuclei -target " . escapeshellarg($scheme.$url.$port."/") . "  -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H ' X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -exclude technologies -severity critical,medium -o /ffuf/" . $randomid . "/" . $randomid . "nuclei.json -json -timeout 10 -json-requests -t /configs/nuclei-templates -nC -c 1 ";

        exec($nuclei_start); 

        if (file_exists("/ffuf/" . $randomid . "/" . $randomid . "nuclei.json")) {
            $output = file_get_contents("/ffuf/" . $randomid . "/" . $randomid . "nuclei.json");
                    
            $output = str_replace("}
{", "},{", $output);

            $output = '[' . $output . ']';
            $output = json_decode($output, true);

            $id=0;
            foreach ($output as $results) {
                $id++;
                $output_json[$id]["template"] = $results["template"];
                $output_json[$id]["matched"] = $results["matched"];
                $output_json[$id]["severity"] = $results["severity"];

                if ($results["response"] < 350000 ){
                    $output_json[$id]["response"] = $results["response"];
                }
            }

            json_encode($output_json);   
        } else $output_json = NULL;
    
        return $output_json;
    }

    public function ParseHostname($url)
    {
        $url = strtolower($url);

        preg_match("/(https?:\/\/)?([a-zA-Z-\d\.]*)/", $url, $domain); //get hostname only
        
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
        
        $url = dirscan::ParseHostname($input["url"]);

        $port = dirscan::ParsePort($input["url"]);

        if ($port != "") $port = dirscan::ParsePort($input["url"]); else $port = "";

        $taskid = (int) $input["taskid"];

        $randomid = $taskid;

        if (strpos($input["url"], 'https://') !== false) {
                $scheme = "https://";
            } else {
                $scheme = "http://";
        }

        $url = ltrim($url, ' ');
        $url = rtrim($url, ' ');
        $url = rtrim($url, '/');

        $domainfull = substr($url, 0, strrpos($url, ".")); //hostname without www. and .com at the end

        $hostonly = preg_replace("/(\w)*\./", "", $domainfull); //hostname without subdomain and .com at the end

        if ($domainfull == $hostonly) $hostonly = ""; //remove duplicate extension from scan

        $extensions = "log,php,asp,aspx,jsp,py,txt,conf,config,bak,backup,swp,old,db,sql,com,zip,tar,rar,tgz,tar.gz,".$url.",".$domainfull.",".$hostonly;

        if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $url, $matches) == 1) $input["ip"] = $matches[0];

        if (!isset($input["ip"])) {
            $start_dirscan = "sudo mkdir /ffuf/" . $randomid . "/ && sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$url.$port."/FUZZ") . " -t 1 -p 2 -mc all -w /configs/dict.txt -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H ' X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -r -ac -D -e " . escapeshellarg($extensions) . " -o /ffuf/" . $randomid . "/" . $randomid . ".json -od /ffuf/" . $randomid . "/ -of json";

            $wayback_result = dirscan::Wayback($url);
        }

        if (isset($input["ip"])) {

            $ip = dirscan::ParseIP($input["ip"]);

            $start_dirscan = "sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -t 1 -p 2 -H " . escapeshellarg('Host: ' . $url) . " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H ' X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'CF-Connecting-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -mc all -w /configs/dict.txt -r -ac -D -e " . escapeshellarg($extensions) . " -o /ffuf/" . $randomid . "/" . $randomid . ".json -od /ffuf/" . $randomid . "/ -of json ";

            $start_dirscan_localhost = "sudo mkdir /ffuf/" . $randomid . " && sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -t 1 -p 2 -H 'Host: localhost' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H ' X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'CF-Connecting-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -mc all -w /configs/dict.txt -r -ac -D -e " . escapeshellarg($extensions) . " -o /ffuf/" . $randomid . "/" . $randomid . "localhost.json -od /ffuf/" . $randomid . "/ -of json ";

            exec($start_dirscan_localhost); 
        }

        exec($start_dirscan);

        $nuclei = dirscan::Nuclei($scheme,$url,$port,$randomid); //starts nuclei scan and stores result json into $nuclei
        
        //Get dirscan results file from volume
        if (file_exists("/ffuf/" . $randomid . "/" . $randomid . ".json")) {
            $output = file_get_contents("/ffuf/" . $randomid . "/" . $randomid . ".json");
            $output = json_decode($output, true);

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
            sleep(1800);
            exec($start_dirscan);

                if (file_exists("/ffuf/" . $randomid . "/" . $randomid . ".json")) {
                    $output = file_get_contents("/ffuf/" . $randomid . "/" . $randomid . ".json");
                    $output = json_decode($output, true);

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
                } else $outputarray = "No file.";
        }

        //Get localhost dirscan results file from volume
        if (file_exists("/ffuf/" . $randomid . "/" . $randomid . "localhost.json")) {
            $output = file_get_contents("/ffuf/" . $randomid . "/" . $randomid . "localhost.json");
            $output = json_decode($output, true);

            $output_localhost_array = array();
            $id=0;
            $result_length = array();

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

        if ($output_localhost_array != "No file." && !is_null($output_localhost_array) ) {
            $outputarray = json_encode(array_merge($outputdirscan,$output_localhost_array));
        } else $outputarray = json_encode($outputdirscan);

        //Scaner's work is done -> decrement scaner's dirscan amount in DB
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

        if(!empty($dirscan)){ //if querry exists in db

            $dirscan->dirscan_status = "Done.";
            $dirscan->dirscan = $outputarray;
            $dirscan->nuclei = json_encode($nuclei);
            $dirscan->wayback = $wayback_result;
            $dirscan->date = date("Y-m-d H-i-s");

            $dirscan->save();
        } else {
            $dirscan = new Tasks();
            $dirscan->taskid = $taskid;
            $dirscan->userid = 10;
            $dirscan->dirscan_status = "Done.";
            $dirscan->dirscan = $outputarray;
            $dirscan->nuclei = json_encode($nuclei);
            $dirscan->wayback = $wayback_result;
            $dirscan->date = date("Y-m-d H-i-s");

            $dirscan->save();
        }

        exec("sudo rm -r /ffuf/" . $randomid . "/");
        return 1;
    }

}

