<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;
use frontend\models\Queue;

ini_set('max_execution_time', 0);

class Nuclei extends ActiveRecord
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
            return preg_match("/link|cdn|sentry|status|socket|help|autodiscover|cdn|url(\d)*/i", $in);
        }
    }

    public function savetodb($taskid, $nuclei)
    {
        try{
            Yii::$app->db->open();

            $task = Tasks::find()
                ->where(['taskid' => $taskid])
                ->limit(1)
                ->one();

            if(!empty($task) && ($task->nuclei=="") ){ //if task exists in db and nuclei result is empty

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
        } catch (\yii\db\Exception $exception) {

            sleep(2000);

            nuclei::savetodb($taskid, $nuclei);
        }

        return 1;
    }


    public function Nucleiscan($list, $randomid)
    {

        $headers = " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Forwarded-For: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Forwarded: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded-By: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_IMFORWARDS: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'l5d-dtab: /$/inet/169.254.169.254/80' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'ZCACHE_CONTROL: 127.0.0.1' "; 

        $exclude = " -exclude-templates /root/nuclei-templates/helpers -exclude-templates /root/nuclei-templates/dns -exclude-templates /root/nuclei-templates/miscellaneous -exclude-templates /root/nuclei-templates/technologies/tech-detect.yaml -exclude-templates /root/nuclei-templates/technologies/aws -exclude-templates /root/nuclei-templates/technologies/waf-detect.yaml -exclude-templates /root/nuclei-templates/misconfiguration/http-missing-security-headers.yaml -exclude-templates /root/nuclei-templates/misconfiguration/cloudflare-image-ssrf.yaml -exclude-templates /root/nuclei-templates/cves/2018/CVE-2018-15473.yaml -exclude-templates /root/nuclei-templates/vulnerabilities/generic/cors-misconfig.yaml -exclude-templates /root/nuclei-templates/exposures/tokens/generic/ -etags xss -exclude-templates /root/nuclei-templates/token-spray/ -exclude-severity info -exclude-templates /root/nuclei-templates/cves/2018/CVE-2017-5487.yaml -exclude-templates /root/nuclei-templates/cves/2018/CVE-2021-38314.yaml ";


        $output = "/nuclei/" . $randomid . "/" . $randomid . "out.json";

        $nuclei_start = "sudo docker run --rm --cpu-shares 256 --network=docker_default -v nuclei:/nuclei -v configs:/root/ projectdiscovery/nuclei -t /root/nuclei-templates/ -list " . escapeshellarg($list) . " -stats -o " . $output . " -json -irr -retries 3 -max-host-error 5000 -timeout 60 -rl 15 -bs 1500 -c 1 " . $exclude . $headers; 

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
                $output_json[$id]["template"] = $results["template-id"];
                $output_json[$id]["matched"] = $results["matched"].$results["matched-at"];
                $output_json[$id]["severity"] = $results["info"]["severity"];

                if ( isset( $results["extracted-results"] ) ){
                    $output_json[$id]["regexp"] = $results["extracted-results"];
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

            if( nuclei::bannedsubdomains($currenturl) === 0 ){
                $urls[] = $currenturl;
            }
        }

        $urls = array_unique($urls);

        exec("sudo mkdir /nuclei/" . $randomid . "/ && sudo chmod 777 /nuclei/" . $randomid . "/ -R && sudo chmod 777 -R /nuclei/" . $randomid . " ");

        $urllist = "/nuclei/" . $randomid . "/" . $randomid . "urllist.txt";;

        file_put_contents($urllist, implode( PHP_EOL, $urls) );

        $nuclei = nuclei::Nucleiscan($urllist, $randomid); //starts nuclei scan and stores result json into $nuclei
            
        if($nuclei!=="null" && $nuclei!=""){
            nuclei::savetodb($taskid, $nuclei);
        }

        if( isset($input["queueid"]) ) $queues = explode(PHP_EOL, $input["queueid"]); 

        foreach($queues as $queue){
            dirscan::queuedone($queue);
        }

        exec("sudo rm -r /nuclei/" . $randomid . "*");

        return 1;
    }

}

