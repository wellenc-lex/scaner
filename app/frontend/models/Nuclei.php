<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;
use frontend\models\Queue;

ini_set('max_execution_time', 0);
error_reporting (E_ALL ^ E_NOTICE);

class Nuclei extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public static function bannedsubdomains($in)
    {
        if (preg_match("/dev|stage|test|proxy|stg|int|adm|uat/i", $in) === 1) {
            return 0; //if its used for internal or develop purposes - scan anyway
        } else { 
            return preg_match("/link|cdn|sentry|status|socket|help|autodiscover|cdn|url(\d)*/i", $in);
        }
    }

    public static function savetodb($taskid, $nuclei)
    {
        do{
            try{
                $tryAgain = false;
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
                    $task->host = "Nuclei.";

                    $task->save();
                } else {

                    $task = new Tasks();

                    $task->host = $url;
                    $task->dirscan_status = "Done.";
                    $task->notify_instrument = $task->notify_instrument."8";
                    $task->nuclei = json_encode($nuclei);
                    $task->date = date("Y-m-d H-i-s");
                    $task->host = "Nuclei.";

                    $task->save();
                }
            } catch (\yii\db\Exception $exception) {
                $tryAgain = true;
                sleep(6000);

                nuclei::savetodb($taskid, $nuclei);
            }
        } while($tryAgain);

        return 1;
    }

    public static function Nucleiscan($list, $randomid, $headers)
    {
        $exclude = " -exclude-templates /root/nuclei-templates/fuzzing,/root/nuclei-templates/dns,/root/nuclei-templates/miscellaneous,/root/nuclei-templates/technologies/tech-detect.yaml,/root/nuclei-templates/technologies/aws,/root/nuclei-templates/technologies/waf-detect.yaml,/root/nuclei-templates/misconfiguration/http-missing-security-headers.yaml,/root/nuclei-templates/misconfiguration/cloudflare-image-ssrf.yaml,/root/nuclei-templates/cves/2018/CVE-2018-15473.yaml,/root/nuclei-templates/vulnerabilities/generic/cors-misconfig.yaml,/root/nuclei-templates/cves/2018/CVE-2017-5487.yaml,/root/nuclei-templates/cves/2018/CVE-2021-38314.yaml,/root/nuclei-templates/cves/2016/CVE-2016-10940.yaml,/root/nuclei-templates/cves/2016/CVE-2017-5487.yaml,/root/nuclei-templates/vulnerabilities/generic/open-redirect.yaml,/root/nuclei-templates/ssl/deprecated-tls.yaml,/root/nuclei-templates/ssl/,/root/nuclei/missing-csp,/root/nuclei/Custom-Nuclei-Templates/shells.yaml,/root/nuclei/Custom-Nuclei-Templates/header_sqli.yaml,/root/nuclei/Custom-Nuclei-Templates/templates/sqli_header.yaml,/root/nuclei/header_reflection_body.yaml,/root/nuclei/header-reflection.yaml,/root/nuclei-templates/technologies/nginx/nginx-version.yaml,/root/nuclei-templates/cves/2022/CVE-2022-45362.yaml,/root/nuclei/header_reflection.yaml,/root/nuclei/header_reflection_body.yaml,/root/nuclei/header_reflection.yaml,/root/nuclei/header_reflection_body.yaml,/root/nuclei/graphql_get.yaml,/root/nuclei/missing-csp.yaml,/root/nuclei-templates/miscellaneous/apple-app-site-association.yaml,/root/nuclei-templates/misconfiguration/xss-deprecated-header.yaml,/root/nuclei/Custom-Nuclei-Templates/header_reflection.yaml,/root/nuclei/Custom-Nuclei-Templates/header_sqli.yaml,/root/nuclei/Custom-Nuclei-Templates/display-via-header.yaml,/root/nuclei/header_reflection.yaml,/root/nuclei/display-via-header.yaml,/root/nuclei/Custom-Nuclei-Templates/header_reflection_body.yaml,/root/nuclei/Custom-Nuclei-Templates/missing-csp.yaml,/root/nuclei/missing-csp.yaml "; //,/root/nuclei-templates/helpers,/root/nuclei-templates/token-spray/,/root/nuclei-templates/exposures/tokens/generic/ -etags xss 

        //-exclude-severity info

        //-resolvers /root/resolv.conf

        $output = "/nuclei/" . $randomid . "/" . $randomid . "out.json";
//--net=container:vpn2 -hang-monitor
        $nuclei_start = "sudo docker run --rm --cpu-shares 512 -v nuclei:/nuclei -v configs:/root/ projectdiscovery/nuclei -t /root/nuclei/ -t /root/nuclei-templates/ -w /root/nuclei-templates/workflows -t /root/nuclei-custom/5.log4j.yaml -list " . escapeshellarg($list) . " -o " . $output . " -j -irr -max-host-error 200 -timeout 80 -rl 10 -bs 100 -c 2 -hbs 3 -stats -retries 3 -error-log /nuclei/error.log -page-timeout 80 -ztls -disable-update-check -system-resolvers  " . $exclude . $headers;

//-ept network -silent -stats
        /*$nuclei_start = "sudo /root/bin/bin/nuclei -t /root/nuclei-templates/ -list " . escapeshellarg($list) . " -o " . $output . " -json -irr -retries 2 -max-host-error 50 -timeout 180 -headless -silent -rl 25 -bs 2000 -c 25 -hbs 55 " . $exclude . $headers;  //-stats*/

        file_put_contents("/dockerresults/outnuclei.txt", $nuclei_start);

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
                    $output_json[$id]["response"] = base64_encode(
                        $results["request"].PHP_EOL.$results["response"]
                    );
                }
            }

            json_encode($output_json);   
        } else $output_json = NULL;
    
        return $output_json;
    }

    public static function nuclei($input)
    {
        $headers = " -H 'Accept-Language: en-US;q=0.8,en;q=0.5' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.5304.107 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Forwarded-For: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Forwarded: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded-By: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_IMFORWARDS: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'l5d-dtab: /$/inet/169.254.169.254/80' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'ZCACHE_CONTROL: 127.0.0.1' -H 'x-balancer-ip: 127.0.0.1' -H 'x-forwarded-for-y: 127.0.0.1' -H 'x-yandex-internal-request: 1' -H 'X-Bug-Bounty: 5d192f443f79484ce37f8a2f850308fe661f9ea17a56bd44cc9ce2e3b6002d8e' "; 

        if( $input["url"] != "") $unparsedurls = explode(PHP_EOL, $input["url"]); else return 0; //no need to scan without supplied url

        if ( isset( $input["taskid"]) && $taskid!="" ) $taskid = (int) $input["taskid"]; else $taskid = 1010;

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

        exec( "sudo mkdir /nuclei/" . $randomid . "/ && sudo chmod -R 777 /nuclei/" . $randomid . "/ ");

        $urllist = "/nuclei/" . $randomid . "/" . $randomid . "urllist.txt";;

        file_put_contents($urllist, implode( PHP_EOL, $urls) );

        $nuclei = nuclei::Nucleiscan($urllist, $randomid, $headers); //starts nuclei scan and stores result json into $nuclei
            
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

