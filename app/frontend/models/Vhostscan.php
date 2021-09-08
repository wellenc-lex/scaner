<?php
namespace frontend\models;

use yii\db\ActiveRecord;
use Yii;
use frontend\models\Queue;
set_time_limit(0);

class Vhostscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function ipCheck($IP, $CIDR){
            
        list ($net, $mask) = explode("/", $CIDR);

        $ip_net = ip2long($net);
        $ip_mask = ~((1 << (32 - $mask)) - 1);

        $ip_ip = ip2long($IP);

        $ip_ip_net = $ip_ip & $ip_mask;

        return ($ip_ip_net == $ip_net);
    }

    public function saveToDB($taskid, $output, $nmapips)
    {
        $output = json_encode(array_unique($output));

        if($output != "[]" && $output != "[[[]]]" && $output != '[["No file."]]'){

            try{

                Yii::$app->db->open();

                $decrement = ToolsAmount::find()
                        ->where(['id' => 1])
                        ->one();

                $value = $decrement->vhosts;
                        
                if ($value <= 1) {
                    $value=0;
                } else $value = $value-1;

                $decrement->vhosts=$value;
                $decrement->save();

                $task = new Tasks();
                
                $task->vhost_status = "Done.";
                $task->notify_instrument = $task->notify_instrument."7";
                $task->vhost = $output;
                $task->host = "Vhost";
                $task->date = date("Y-m-d H-i-s");

                $task->save();

                $nmapips = preg_replace('/(https?:\/\/)/i', '', $nmapips);
                $nmapips = preg_replace('/\:\d*/', '', $nmapips);

                //add ips for nmap scan to queue
                $queue = new Queue();
                $queue->nmap = $nmapips;
                $queue->instrument = 1;
                $queue->save();

                exec("sudo rm -R /ffuf/vhost" . $randomid . "/ &");
                
                return 1;

            } catch (\yii\db\Exception $exception) {
                var_dump($exception);
                sleep(360);

                $task = new Tasks();
                        
                $task->vhost_status = "Done.";
                $task->notify_instrument = $task->notify_instrument."7";
                $task->vhost = $output;
                $task->host = "Vhost";
                $task->date = date("Y-m-d H-i-s");

                $task->save();

                return $exception.json_encode(array_unique($output));
            }
        }
    }

    public function ParseHostname($url)
    {
        $url = strtolower($url);

        preg_match_all("/(https?:\/\/)?([a-zA-Z\-\d\.][^\/\:]+)/i", $url, $domains); 

        foreach ($domains[2][0] as $domain) {
            if ($domain != "") $hostname = $hostname." ".$domain; //??????????????????? null" "domain?????? P.S. it works but i really have no idea why
        }
        
        return $hostname;
    }

    public function ReadFFUFResult($filename)
    {
        global $randomid;

        if (file_exists($filename)) {
            $output = json_decode(file_get_contents($filename), true);

            $output_vhost_array = array();
            $id=0;
            $result_length = array();

            if( isset($output["results"]) ) {
                foreach ($output["results"] as $results) {
                    if ($results["length"] >= 0 && !in_array($results["length"], $result_length) && $results["length"]!="612" ){
                        $id++;
                        $result_length[] = $results["length"];//so no duplicates gonna be added
                        $output_vhost_array[$id]["url"] = $results["url"];
                        $output_vhost_array[$id]["length"] = $results["length"];
                        $output_vhost_array[$id]["status"] = $results["status"];
                        $output_vhost_array[$id]["redirect"] = $results["redirectlocation"];
                        $output_vhost_array[$id]["host"] = $results["host"];

                        if ($results["length"] < 350000 ){
                            exec("sudo chmod -R 777 /ffuf/vhost" . $randomid . "/");

                            $output_vhost_array[$id]["resultfile"] = base64_encode(file_get_contents("/ffuf/vhost" . $randomid . "/" . $results["resultfile"] . ""));
                        }
                    }
                }
            } else $output_vhost_array = "No file.";
        } else $output_vhost_array = "No file.";
        
        return $output_vhost_array;
    }

    public function FindVhostsWithDomain($host)
    {
        global $headers;
        global $randomid;

        $host = trim($host, ' ');

        $outputfile = "/ffuf/vhost" . $randomid . "/" . $randomid . "domain.json";

        $ffuf_general_string = "sudo docker run --rm --cpu-shares 256 --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -r -o " . $outputfile . " -od /ffuf/vhost" . $randomid . "/ -of json -mc all -t 1 " . $headers . " -u ";

        $vhost_file_location = "/ffuf/vhost" . $randomid . "/" . $randomid . "domain.json";

        //Asks Host:localhost.domain.com, dev.domain.com, etc
        exec($ffuf_general_string . escapeshellarg($host ."/") . " -H 'Host: FUZZ.HOSTS' -w /ffuf/vhost" . $randomid . "/wordlist.txt:FUZZ -w /ffuf/vhost" . $randomid . "/domains.txt:HOSTS ");

        $output_vhost[] = vhostscan::ReadFFUFResult($vhost_file_location);

        //Asks localhost/domain.com/
        exec($ffuf_general_string . escapeshellarg($host ."/HOSTS/") . " -H 'Host: localhost' -w /ffuf/vhost" . $randomid . "/domains.txt:HOSTS ");

        $output_vhost[] = vhostscan::ReadFFUFResult($vhost_file_location);

        $output_vhost = array_unique($output_vhost);

        return $output_vhost;
    }

    public function findVhostsNoDomain($host)
    {
        global $headers;
        global $randomid;

        $host = trim($host, ' ');

        $outputfile = "/ffuf/vhost" . $randomid . "/" . $randomid . "NOdomain.json";

        $ffuf_general_string = "sudo docker run --cpu-shares 256 --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -r -o " . $outputfile . " -od /ffuf/vhost" . $randomid . "/ -of json -mc all -t 1 " . $headers . " -w /ffuf/vhost" . $randomid . "/wordlist.txt:FUZZ -u ";

        $vhost_file_location = "/ffuf/vhost" . $randomid . "/" . $randomid . "NOdomain.json";
            
        //Asks Host:localhost, dev, etc
        exec($ffuf_general_string . escapeshellarg($host ."/") . " -H 'Host: FUZZ' ");

        $output_vhosts[] = vhostscan::ReadFFUFResult($vhost_file_location);

        //Asks Host:admin.dev, asdf.dev
        exec($ffuf_general_string . escapeshellarg($host ."/") . " -H 'Host: FUZZ.dev' ");

        $output_vhost[] = vhostscan::ReadFFUFResult($vhost_file_location);

        //Asks Host:admin.local, asdf.local
        exec($ffuf_general_string . escapeshellarg($host ."/") . " -H 'Host: FUZZ.local' ");

        $output_vhost[] = vhostscan::ReadFFUFResult($vhost_file_location);

        $output_vhost = array_unique($output_vhost);

        return $output_vhosts;
    }

    public function httpxhosts($amassoutput)
    {
        global $iparray; $iparray = array();
        global $randomid;

        //Cloudflare ip ranges + private networks - no need to ffuf
        $masks = array("103.21.244.0/22", "103.22.200.0/22", "103.31.4.0/22", "104.16.0.0/12", "104.24.0.0/14", "108.162.192.0/18", "131.0.72.0/22",
            "141.101.64.0/18", "162.158.0.0/15", "172.64.0.0/13", "188.114.96.0/20", "190.93.240.0/20", "197.234.240.0/22", "199.60.103.0/24",
            "173.245.48.0/20", "198.41.128.0/17", "172.16.0.0/12", "172.67.0.0/12", "192.168.0.0/16", "10.0.0.0/8","185.71.64.0/22","185.121.240.0/22", "104.101.221.0/24",
            "184.51.125.0/24", "184.51.154.0/24", "184.51.33.0/24", "23.15.11.0/24", "23.15.12.0/24","23.15.13.0/24","23.200.22.0/24","23.56.209.0/24","23.62.225.0/24","23.74.0.0/23");

        foreach($amassoutput as $json){
            foreach ($json["addresses"] as $ip) {

                if (strpos($ip["ip"], '::') === false) { //TODO: add ipv6 support

                    if (strpos($ip["ip"], '127.0.0.1') === false) { //no need to scan local ip

                        $stop = 0;

                        for ($n = 0; $n < count($masks); $n++) { 

                            if (((vhostscan::ipCheck($ip["ip"], $masks[$n])) == 1)) { // if IP isnt in blocked mask - cloudflare ranges,etc
                                $stop = 1;
                                break;
                            } else $stop = 0;

                        }

                        if ($stop == 0) { //if ip is allowed

                            $iparray[] = $ip["ip"];
                        }
                    }
                }
            }
        }

        $wordlist = "/ffuf/vhost" . $randomid . "/hosts.txt";
        $output = "/ffuf/vhost" . $randomid . "/httpx.txt";
        
        file_put_contents($wordlist, implode( PHP_EOL, $iparray) );

        $httpx = "sudo docker run --cpu-shares 256 --rm -v ffuf:/ffuf projectdiscovery/httpx -exclude-cdn -threads 20 -silent -o ". $output ." -l ". $wordlist ."";
        exec($httpx);

        if (file_exists($output)) {
            $alive = file_get_contents($output);
            $alive = explode(PHP_EOL,$alive);
        } else $alive = [];
        
        return $alive;
    }

    public function getVHosts($domains, $amassoutput, $vhostwordlist)
    {
        global $randomid; global $domains; global $vhostlist;

        $vhostlist = explode("\n", file_get_contents("/configs/vhostwordlist.txt"));

        if($amassoutput != 0){

            $domains = array();

            foreach ($amassoutput as $json) {
                if (!in_array($json["name"], $vhostlist)) {
                    //push full admin.something.com to the vhost domains list
                    array_push($domains, $json["name"]);
                }
            }
        }

        foreach($domains as $domainarray){

            $host = preg_replace("~https?://~", "", $domainarray);
            $host = rtrim($host, '/');

            $domainfull = substr($host, 0, strrpos($host, ".")); ///www.something.com -> something.com

            $hostonly = preg_replace("/(\w)*\./", "", $domainfull); //something.com -> something

            if ($domainfull == $hostonly) $hostonly = "";

            if ($domainfull != "") {
                array_push($vhostlist, $domainfull); //admin.something.com -> admin.something

                if ($hostonly != "") {
                    array_push($vhostlist, $hostonly); //admin.something.com -> admin
                }
            }

            $domains[] = $host;
        }

        if($vhostwordlist!=0) $domains = array_merge($domains,$vhostwordlist);

        $vhostlist = array_unique($vhostlist);
        $domains = array_unique($domains);
        
        file_put_contents("/ffuf/vhost" . $randomid . "/wordlist.txt", implode( PHP_EOL, $vhostlist) ); //push wordlist on the disk so ffuf could use it
        file_put_contents("/ffuf/vhost" . $randomid . "/domains.txt", implode( PHP_EOL, $domains) ); //to use domains supplied by user as FFUF wordlist

        return 1;
    }

    public static function vhostscan($input)
    {

        global $headers;
        global $randomid;

        $headers = " -p 0.5 -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CF_CONNECTING_IP: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1' -H 'X_IMFORWARDS: 127.0.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'ZCACHE_CONTROL: 127.0.0.1' -H 'CF-Connecting-IP: 127.0.0.1' ";

        if( isset( $input["taskid"]) ) {
            $randomid = (int) $input["taskid"]; 
        } else $randomid = rand(1,100000);

        exec("sudo mkdir /ffuf/vhost" . $randomid . " &");
        exec("sudo chmod -R 777 /ffuf/vhost" . $randomid . "/ &");

        if ((isset($input["port"]) && $input["port"] != "") && (isset($input["ip"]) && $input["ip"] != "")) {

            $ips = explode(PHP_EOL, $input["ip"]);
            $domains = explode(PHP_EOL, $input["domain"]);
            $ports = explode(PHP_EOL, $input["port"]);

            $output = array();

            //add subdomain names from the input to the ffuf wordlist
            if( isset( $domains ) ){
                vhostscan::getVHosts($domains, 0, 0);
            }

            //asks each ip each domain on each port in cycle
            foreach($ips as $currentIP){
                //echo $currentIP;

                foreach($ports as $currentport){
                    //echo $currentport;

                    if ($currentport == 443 || $currentport == 8443 || (isset($input["ssl"]) && $input["ssl"] == "1")) {
                        $scheme = "https://";
                    } else $scheme = "http://";

                    $output[] = vhostscan::findVhostsNoDomain($scheme . $currentIP . ":" . $currentport);

                    if( isset( $domains ) ){
                        $output[] = vhostscan::findVhostsWithDomain($scheme . $currentIP . ":" . $currentport);
                    }
                }
            }

            vhostscan::saveToDB($taskid, $output);
            return 1;
        }

        if ((isset($input["taskid"]) && $input["taskid"] != "") && (!isset($input["ip"]))) {


            $taskid = (int) $input["taskid"];

            $task = Tasks::find()
                ->where(['taskid' => $taskid])
                ->limit(1)
                ->one();

            Yii::$app->db->close();  

            $vhostwordlist = json_decode($task->vhostwordlist, true);

            $amassoutput = json_decode($task->amass, true);

            $maindomain = $amassoutput[0]["domain"];

            $output = array();

            vhostscan::getVHosts(0, $amassoutput, $vhostwordlist);

            if(isset($amassoutput)){

                $alive = vhostscan::httpxhosts($amassoutput);

                foreach($alive as $host) {

                    if($host!=""){
                        $output[] = vhostscan::findVhostsWithDomain($host);
                        $output[] = vhostscan::findVhostsNoDomain($host);
                    }
                }
            } else return "NO AMASS RESULTS or NO PORTS";

            vhostscan::saveToDB($taskid, $output, implode( " ", $alive) );
            
            return 1;
        }
    }
}


