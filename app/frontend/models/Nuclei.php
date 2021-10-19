<?php

namespace frontend\models;

use yii\db\ActiveRecord;
use frontend\models\Dirscan;
use frontend\models\Queue;
require_once 'Dirscan.php';

ini_set('max_execution_time', 0);

class Nuclei extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function Nucleiscan($list, $randomid)
    {

        $headers = " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CF_CONNECTING_IP: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1' -H 'X_IMFORWARDS: 127.0.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'ZCACHE_CONTROL: 127.0.0.1' -H 'Connection: close, X-Real-IP' ";

        $exclude = " -exclude-templates /root/nuclei-templates/helpers -exclude-templates /root/nuclei-templates/dns -exclude-templates /root/nuclei-templates/takeovers -exclude-templates /root/nuclei-templates/miscellaneous -exclude-templates /root/nuclei-templates/technologies/tech-detect.yaml -exclude-templates /root/nuclei-templates/technologies/waf-detect.yaml -exclude-templates /root/nuclei-templates/misconfiguration/http-missing-security-headers.yaml -exclude-templates /root/nuclei-templates/misconfiguration/cloudflare-image-ssrf.yaml -exclude-templates /root/nuclei-templates/cves/2018/CVE-2018-15473.yaml -exclude-templates /root/nuclei-templates/vulnerabilities/generic/cors-misconfig.yaml -exclude-templates /root/nuclei-templates/exposures/tokens/generic/ -etags xss -exclude-templates /root/nuclei-templates/token-spray/ ";//-exclude-severity info

        $output = "/nuclei/" . $randomid . "/" . $randomid . "out.json";

        $nuclei_start = "sudo docker run --cpu-shares 256 --rm --network=docker_default -v nuclei:/nuclei -v configs:/root/ projectdiscovery/nuclei -t /root/nuclei-templates/ -list " . escapeshellarg($list) . " -stats -o " . $output . " -json -irr -timeout 15 -rl 3 " . $exclude . $headers;

        //-nut

        exec($nuclei_start); 

        if (file_exists($output)) {
            $output = file_get_contents($output);
                    
            $output = str_replace("}
{", "},{", $output);

            $output = '[' . $output . ']';
            $output = json_decode($output, true);

            $id=0;
            foreach ($output as $results) {
                $id++;
                $output_json[$id]["template"] = $results["templateID"];
                $output_json[$id]["matched"] = $results["matched"];
                $output_json[$id]["severity"] = $results["info"]["severity"];

                if ( isset( $results["extracted_results"] ) ){
                    $output_json[$id]["regexp"] = $results["extracted_results"];
                }

                if ( isset( $results["response"] ) ){
                    $output_json[$id]["response"] = base64_encode($results["response"]);
                }
            }

            json_encode($output_json);   
        } else $output_json = NULL;
    
        return $output_json;
    }

    public static function nuclei($input)
    {
        if( $input["url"] != "") $unparsedurls = explode(PHP_EOL, $input["url"]); else return 0; //no need to scan without supplied url

        $taskid = (int) $input["taskid"]; if($taskid=="") $taskid = 1010;

        $randomid = rand(1,100000000);

        foreach ($unparsedurls as $currenturl){
            $currenturl = trim($currenturl, ' ');
            $currenturl = rtrim($currenturl, '/');

            if (preg_match("/https?:\/\//i", $currenturl) === 0) $currenturl = "http://".$currenturl;

            $urls[] = $currenturl;
        }

        $urls = array_unique($urls);

        exec("sudo mkdir /nuclei/" . $randomid . "/ && sudo chmod 777 /nuclei/" . $randomid . "/ -R && sudo chmod 777 -R /nuclei/" . $randomid . " ");

        $urllist = "/nuclei/" . $randomid . "/" . $randomid . "urllist.txt";;

        file_put_contents($urllist, implode( PHP_EOL, $urls) );

        $nuclei = nuclei::Nucleiscan($urllist, $randomid); //starts nuclei scan and stores result json into $nuclei
            
        if($nuclei!=="null" && $nuclei!=""){

            $task = Tasks::find()
            ->where(['taskid' => $taskid])
            ->limit(1)
            ->one();

            if(!empty($task) && ($task->nuclei="") ){ //if task exists in db and nuclei result is empty

                $task->dirscan_status = "Done.";
                $task->notify_instrument = $task->notify_instrument."8";
                $task->nuclei = json_encode($nuclei);
                $task->date = date("Y-m-d H-i-s");

                $task->save();
                    
            } else {

                $task = new Tasks();
                $task->host = $url;
                $task->dirscan_status = "Done.";
                $task->notify_instrument = $task->notify_instrument."8";
                $task->nuclei = json_encode($nuclei);
                $task->date = date("Y-m-d H-i-s");

                $task->save();
            }
        }

        if( isset($input["queueid"]) ) $queues = explode(PHP_EOL, $input["queueid"]); 

        foreach($queues as $queue){
            dirscan::queuedone($queue);
        }

        exec("sudo rm -r /nuclei/" . $randomid . "*");

        return 1;
    }

}

