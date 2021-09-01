<?php

namespace frontend\models;

use yii\db\ActiveRecord;

class Nuclei extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function Nucleiscan($scheme,$url,$port,$randomid)
    {

        exec("sudo mkdir /ffuf/" . $randomid . "/ && sudo chmod 777 /ffuf/" . $randomid . "/ -R && sudo chmod 777 /ffuf/" . $randomid . " -R && sudo chmod 777 /configs/nuclei-templates/ -R");

        $headers = " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CF_CONNECTING_IP: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1' -H 'X_IMFORWARDS: 127.0.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'ZCACHE_CONTROL: 127.0.0.1' -H 'Connection: close, X-Real-IP' ";

        $nuclei_start = "sudo docker run --cpu-shares 256 --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ projectdiscovery/nuclei -ud /configs/nuclei-templates -target " . escapeshellarg($scheme.$url.$port."/") . " " . $headers . " -t /configs/nuclei-templates/ -exclude /configs/nuclei-templates/helpers -exclude /configs/nuclei-templates/dns -exclude /configs/nuclei-templates/takeovers -exclude /configs/nuclei-templates/miscellaneous -exclude /configs/nuclei-templates/exposed-tokens/generic -exclude /configs/nuclei-templates/technologies/tech-detect.yaml -exclude /configs/nuclei-templates/technologies/waf-detect.yaml -stats -o /ffuf/" . $randomid . "/" . $randomid . "nuclei.json -json -timeout 20 -c 1 -rate-limit 5";

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

                if ( isset( $results["extracted_results"] ) ){
                    $output_json[$id]["regexp"] = $results["extracted_results"];
                }

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

    public static function nuclei($input)
    {
        
        $url = nuclei::ParseHostname($input["url"]);

        $port = nuclei::ParsePort($input["url"]);

        if ($port != "") $port = nuclei::ParsePort($input["url"]); else $port = "";

        $taskid = (int) $input["taskid"];

        $randomid = $taskid;

        if (strpos($input["url"], 'https://') !== false) {
                $scheme = "https://";
            } else {
                $scheme = "http://";
        }

        $url = trim($url, ' ');
        $url = rtrim($url, '/');

        $nuclei = nuclei::Nucleiscan($scheme,$url,$port,$randomid); //starts nuclei scan and stores result json into $nuclei
        
        if(json_encode($nuclei) != "{}"){

            $task = Tasks::find()
            ->where(['taskid' => $taskid])
            ->limit(1)
            ->one();

            if(!empty($task)){ //if task exists in db

                $task->dirscan_status = "Done.";
                $task->nuclei = json_encode($nuclei);
                $task->date = date("Y-m-d H-i-s");

                $task->save();
                
            } else {

                $task = new Tasks();
                $task->host = $url;
                $task->dirscan_status = "Done.";
                $task->nuclei = json_encode($nuclei);
                $task->date = date("Y-m-d H-i-s");

                $task->save();
            }
        }

        exec("sudo rm -r /ffuf/" . $randomid . "/");

        return 1;
    }

}

