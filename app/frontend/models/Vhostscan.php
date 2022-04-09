<?php
namespace frontend\models;

use yii\db\ActiveRecord;
use Yii;
use frontend\models\Queue;
use frontend\models\Dirscan;
require_once 'Dirscan.php';

ini_set('max_execution_time', 0);

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

    //www.test.google-stage.com -> www.test.google-stage -> www.test -> www 
    public function sliceHost($host){
        global $vhostlist; global $domains;
        STATIC $stop = 1;
        while ($stop != 0){
            $outputValue = preg_replace('/\.(\w[\-\_\d]?)*$/', '', $host, 1, $stop);
            
            array_push($vhostlist, $outputValue);
            array_push($domains, $outputValue); //http://localhost/GOOGLE-STAGE.COM/ -> source code listings?

            vhostscan::sliceHost($outputValue);
        }
    }

    /*public function dosplit($input){
        //www.test.google.com -> www test google
        global $vhostlist;
        preg_match_all("/(\w[\-\_\d]?)*\./", $input, $out);

        if($out[0][0]!=""){
            $word = implode("", $out[0]);
            $word = rtrim($word, ".");
            $vhostlist[] = $word;
            vhostscan::dosplit($word);
        }
    }

    public function split2($input){
        //www.test.google.com -> www.test -> www
        global $vhostlist;

        preg_match_all("/(\w[\-\_\d]?)*\./", $input, $matches);

        foreach($matches[0] as $match){
            $vhostlist[] = rtrim($match, "."); 
        }
    }*/

    public function saveToDB($taskid, $output)
    {
        $output = json_encode( array_unique( array_filter($output) ) );
        
        if($output != "[]" && $output != "[[[]]]" && $output != '[["No file."]]'){

            try{

                Yii::$app->db->open();

                $task = new Tasks();
                
                $task->vhost_status = "Done.";
                $task->notify_instrument = $task->notify_instrument."7";
                $task->vhost = $output;
                $task->host = "Vhost";
                $task->date = date("Y-m-d H-i-s");

                $task->save();
                
                return 1;

            } catch (\yii\db\Exception $exception) {
                sleep(360);

                $task = new Tasks();
                        
                $task->vhost_status = "Done.";
                $task->notify_instrument = $task->notify_instrument."7";
                $task->vhost = $output;
                $task->host = "Vhost";
                $task->date = date("Y-m-d H-i-s");

                Yii::$app->db->close();
                
                return file_put_contents("/dockerresults/".$taskid."vhosterror", $output);
            }
        }
    }

    public function ReadFFUFResult($filename)
    {
        global $randomid;

        if (file_exists($filename)) {
            $output = json_decode(file_get_contents($filename), true);

            $output_vhost_array = array();
            $id=0;
            $result_length = array();

            //&& count($results) > 1

            if( isset($output["results"]) ) {
                foreach ($output["results"] as $results) {
                    if ($results["length"] > 0 && !in_array($results["length"], $result_length) && $results["length"]!="612" ){
                        $id++;
                        $result_length[] = $results["length"];//so no duplicates gonna be added
                        $output_vhost_array[$id]["url"] = $results["url"];
                        $output_vhost_array[$id]["length"] = $results["length"];
                        $output_vhost_array[$id]["status"] = $results["status"];
                        $output_vhost_array[$id]["redirect"] = $results["redirectlocation"];
                        $output_vhost_array[$id]["host"] = $results["host"];

                        if ($results["length"] < 350000 ){
                            
                            $resultfilename = "/ffuf/vhost" . $randomid . "/" . $results["resultfile"] . "";

                            if (file_exists($resultfilename)) {
                                $output_vhost_array[$id]["resultfile"] = base64_encode(file_get_contents($resultfilename));
                            }
                        }
                    }
                }
            } else $output_vhost_array = "";
        } else $output_vhost_array = "";
        
        return $output_vhost_array;
    }

    public function FindVhostsWithDomain($host)
    {
        global $headers;
        global $randomid;

        $host = trim($host);

        $outputfile = "/ffuf/vhost" . $randomid . "/" . $randomid . "domain.json";

        //$ffuf_general_string = "sudo docker run --cpu-shares 256 --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ sneakerhax/ffuf -o " . $outputfile . " -od /ffuf/vhost" . $randomid . "/ -of json -mc all -fc 404 -s -t 1 " . $headers . " -maxtime 100000 -timeout 60 -ignore-body -r -u "; 
        
        $ffuf_general_string = "/tmp/ffuf.binary -o " . $outputfile . " -od /ffuf/vhost" . $randomid . "/ -of json -mc all -fc 429,503,400 -fs 612,613,548 -s -timeout 60 -fr 'Vercel|Too Many Requests|stand by|blocked by|Blocked by|Please wait while|incapsula' -t 3 " . $headers . " -maxtime 150000 -ignore-body -r -ac -acc 'randomtest' -noninteractive -u ";

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

        $ffuf_general_string = "/tmp/ffuf.binary -o " . $outputfile . " -od /ffuf/vhost" . $randomid . "/ -of json -mc all -fc 429,503,400 -fs 612,613,548 -s -timeout 60 -fr 'Vercel|Too Many Requests|stand by|blocked by|Blocked by|Please wait while|incapsula' -t 3 " . $headers . " -w /ffuf/vhost" . $randomid . "/wordlist.txt:FUZZ -maxtime 150000 -ignore-body -r -ac -acc 'randomtest' -noninteractive -u ";

        //$ffuf_general_string = "sudo docker run --cpu-shares 256 --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ sneakerhax/ffuf -o " . $outputfile . " -od /ffuf/vhost" . $randomid . "/ -of json -mc all -fc 404 -s -t 3 " . $headers . " -w /ffuf/vhost" . $randomid . "/wordlist.txt:FUZZ -maxtime 150000 -timeout 60 -ignore-body -r -u ";

        $vhost_file_location = "/ffuf/vhost" . $randomid . "/" . $randomid . "NOdomain.json";
            
        //Asks Host:localhost, dev, etc
        exec($ffuf_general_string . escapeshellarg($host ."/") . " -H 'Host: FUZZ' ");

        $output_vhost[] = vhostscan::ReadFFUFResult($vhost_file_location);

        //Asks Host:admin.dev, asdf.dev
        exec($ffuf_general_string . escapeshellarg($host ."/") . " -H 'Host: FUZZ.dev' ");

        $output_vhost[] = vhostscan::ReadFFUFResult($vhost_file_location);

        //Asks Host:admin.local, asdf.local
        exec($ffuf_general_string . escapeshellarg($host ."/") . " -H 'Host: FUZZ.local' ");

        //Asks Host:admin.internal, asdf.internal
        exec($ffuf_general_string . escapeshellarg($host ."/") . " -H 'Host: FUZZ.internal' ");

        $output_vhost[] = vhostscan::ReadFFUFResult($vhost_file_location);

        $output_vhost = array_unique($output_vhost);

        return $output_vhost;
    }

    public function httpxhosts($amassoutput, $ipstoscan)
    {
        global $randomid;

        $iparray = array();

        //Cloudflare ip ranges + private networks - no need to ffuf
        $masks = array("103.21.244.0/22", "103.22.200.0/22", "103.31.4.0/22", "104.16.0.0/12", "104.24.0.0/14", "108.162.192.0/18", "131.0.72.0/22",
            "141.101.64.0/18", "162.158.0.0/15", "172.64.0.0/13", "188.114.96.0/20", "190.93.240.0/20", "197.234.240.0/22", "199.60.103.0/24",
            "173.245.48.0/20", "198.41.128.0/17", "172.16.0.0/12", "172.67.0.0/12", "192.168.0.0/16", "10.0.0.0/8","185.71.64.0/22","185.121.240.0/22", "104.101.221.0/24",
            "184.51.125.0/24", "184.51.154.0/24", "184.51.33.0/24", "23.15.11.0/24", "23.15.12.0/24","23.15.13.0/24","23.200.22.0/24","23.56.209.0/24","23.62.225.0/24",
            "23.74.0.0/23" );

            /*"120.52.22.96/27", "205.251.249.0/24", "180.163.57.128/26", "204.246.168.0/22", "205.251.252.0/23", "54.192.0.0/16", "204.246.173.0/24", "54.230.200.0/21", 
            "120.253.240.192/26", "116.129.226.128/26", "130.176.0.0/17", "108.156.0.0/14", "99.86.0.0/16", "205.251.200.0/21", "223.71.71.128/25", "13.32.0.0/15", "120.253.245.128/26", 
            "13.224.0.0/14", "70.132.0.0/18", "15.158.0.0/16", "13.249.0.0/16", "205.251.208.0/20", "65.9.128.0/18", "130.176.128.0/18", "58.254.138.0/25", "54.230.208.0/20", "116.129.226.0/25", 
            "52.222.128.0/17", "64.252.128.0/18", "205.251.254.0/24", "54.230.224.0/19", "71.152.0.0/17", "216.137.32.0/19", "204.246.172.0/24", "120.52.39.128/27", 
            "118.193.97.64/26", "223.71.71.96/27", "54.240.128.0/18", "205.251.250.0/23", "180.163.57.0/25", "52.46.0.0/18", "223.71.11.0/27", "52.82.128.0/19", 
            "54.230.0.0/17", "54.230.128.0/18", "54.239.128.0/18", "130.176.224.0/20", "36.103.232.128/26", "52.84.0.0/15", "143.204.0.0/16", "144.220.0.0/16", 
            "120.52.153.192/26", "119.147.182.0/25", "120.232.236.0/25", "54.182.0.0/16", "58.254.138.128/26", "120.253.245.192/27", "54.239.192.0/19", "18.64.0.0/14", 
            "120.52.12.64/26", "99.84.0.0/16", "130.176.192.0/19", "52.124.128.0/17", "204.246.164.0/22", "13.35.0.0/16", "204.246.174.0/23", "36.103.232.0/25", 
            "119.147.182.128/26", "118.193.97.128/25", "120.232.236.128/26", "204.246.176.0/20", "65.8.0.0/16", "65.9.0.0/17", "108.138.0.0/15", "120.253.241.160/27", 
            "64.252.64.0/18", "13.113.196.64/26", "13.113.203.0/24", "52.199.127.192/26", "13.124.199.0/24", "3.35.130.128/25", "52.78.247.128/26", "13.233.177.192/26", 
            "15.207.13.128/25", "15.207.213.128/25", "52.66.194.128/26", "13.228.69.0/24", "52.220.191.0/26", "13.210.67.128/26", "13.54.63.128/26", "99.79.169.0/24", 
            "18.192.142.0/23", "35.158.136.0/24", "52.57.254.0/24", "13.48.32.0/24", "18.200.212.0/23", "52.212.248.0/26", "3.10.17.128/25", "3.11.53.0/24", "52.56.127.0/25", 
            "15.188.184.0/24", "52.47.139.0/24", "18.229.220.192/26", "54.233.255.128/26", "3.231.2.0/25", "3.234.232.224/27", "3.236.169.192/26", "3.236.48.0/23", 
            "34.195.252.0/24", "34.226.14.0/24", "13.59.250.0/26", "18.216.170.128/25", "3.128.93.0/24", "3.134.215.0/24", "52.15.127.128/26", "3.101.158.0/23", 
            "52.52.191.128/26", "34.216.51.0/25", "34.223.12.224/27", "34.223.80.192/26", "35.162.63.192/26", "35.167.191.128/26", "44.227.178.0/24", "44.234.108.128/25", "44.234.90.252/30"); cloudfront?*/ 

        if($amassoutput != 0){
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
        }  

        if ($ipstoscan != 0){
            foreach ($ipstoscan as $ip) {
                if (strpos($ip, '::') === false) { //TODO: add ipv6 support

                    if (strpos($ip, '127.0.0.1') === false) { //no need to scan local ip

                        $stop = 0;

                        for ($n = 0; $n < count($masks); $n++) { 

                            if (((vhostscan::ipCheck($ip, $masks[$n])) == 1)) { // if IP isnt in blocked mask - cloudflare ranges,etc
                                $stop = 1;
                                break;
                            } else $stop = 0;

                        }

                        if ($stop == 0) { //if ip is allowed
                            $iparray[] = $ip;
                        }
                    }
                }
            }
        }

        if (!empty($iparray)) {
            $wordlist = "/ffuf/vhost" . $randomid . "/hosts.txt";
            $output = "/ffuf/vhost" . $randomid . "/httpx.txt";
            
            file_put_contents($wordlist, implode( PHP_EOL, array_filter( array_unique($iparray) ) ) );

            $httpx = "sudo docker run --cpu-shares 256 --rm -v ffuf:/ffuf projectdiscovery/httpx -ports 80,443,8080,8443,8000,3000,8083,8088,8888,8880,9999,10000,4443,6443,10250 -rate-limit 50 -timeout 65 -retries 2 -o ". $output ." -l ". $wordlist ."";

            exec($httpx);

            if (file_exists($output)) {
                $alive = file_get_contents($output);
                $alive = explode(PHP_EOL,$alive);
                $alive = array_unique($alive);
            } else $alive = [];
            
            return $alive;

        } else return 0;
    }

    public function getVHosts($domains, $amassoutput, $vhostwordlist)
    {
        global $randomid; global $domains; global $vhostlist; global $vhostwordlistmanual;

        $vhostlist = explode("\n", file_get_contents("/configs/vhostwordlist.txt"));

        $domains = array();

        if($amassoutput != 0){

            foreach ($amassoutput as $json) {
                if (!in_array($json["name"], $vhostlist)) {
                    //push full admin.something.com to the vhost domains list
                    array_push($domains, $json["name"]);
                }
            }

            if ( !empty($vhostwordlistmanual) ){
                foreach ($vhostwordlistmanual as $name) {
                    if ( !in_array( $name, $domains ) ) {
                        //push full admin.something.com to the vhost domains list
                        array_push( $domains, $name );
                    }
                }
            }
        }

        if($domains!=""){
            foreach($domains as $domainarray){

                $host = preg_replace("~https?://~", "", $domainarray);
                $host = rtrim($host, '/');

                $domains[] = $host;

                vhostscan::sliceHost($host);

                $domainfull = substr($host, 0, strrpos($host, ".")); ///www.something.com -> something.com

                $hostonly = preg_replace("/(\w[\-\_\d]?)*\./", "", $domainfull); //something.com -> something

                if ($domainfull == $hostonly) $hostonly = "";

                if ($domainfull != "") {
                    array_push($vhostlist, $domainfull); //admin.something.com -> admin.something

                    if ($hostonly != "") {
                        array_push($vhostlist, $hostonly); //admin.something.com -> admin
                    }
                }
                /*
                if (strpos($domainfull, 'https://xn--') === false) {

                    $hostwordlist[] = $domainfull; // full hostname for Host: header

                    vhostscan::dosplit($domainfull);

                    vhostscan::split2($domainfull);
                }*/ //generate too much alterations which arent needed for VHost scan?
            }
        }
        
        if( is_array($vhostwordlist) ) $domains = array_merge($domains,$vhostwordlist);

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

        $headers = " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Forwarded-For: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'Forwarded: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded-By: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Forwarded: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_IMFORWARDS: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'l5d-dtab: /$/inet/169.254.169.254/80' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'ZCACHE_CONTROL: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'HTTP_X_REAL_IP: 127.0.0.1' -H 'HTTP_X_FORWARDED_FOR: 127.0.0.1' -H 'CF-Connecting-IP: 127.0.0.1, 0.0.0.0, 192.168.0.1, 10.0.0.1, 172.16.0.1' "; 
        
        $randomid = rand(3000,100000000);

        exec("sudo mkdir /ffuf/vhost" . $randomid . " & && sudo chmod -R 777 /ffuf/vhost" . $randomid . " ");

        sleep( rand(10,150) );

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
            dirscan::queuedone($input["queueid"]);

            exec("sudo rm -R /ffuf/vhost" . $randomid . " &");
            return 1;
        }

        if ((isset($input["taskid"]) && $input["taskid"] != "") && (!isset($input["ip"]))) {

            $taskid = (int) $input["taskid"]; if($taskid=="") $taskid = 1020;

            $task = Tasks::find()
                ->where(['taskid' => $taskid])
                ->one();

            Yii::$app->db->close();  

            if($task!=""){

                $output = array();

                if ( $task->ips != "" ){
                    $iparray = explode(" ", $task->ips);
                }

                $vhostwordlist = json_decode($task->vhostwordlist, true);

                if ( $task->vhostwordlistmanual != "" ){
                    global $vhostwordlistmanual;
                    $vhostwordlistmanual = explode(" ", $task->vhostwordlistmanual);

                    $vhostwordlist = array_unique( array_merge( $vhostwordlist, $vhostwordlistmanual ) );
                }

                $amassoutput = json_decode($task->amass, true);

                $maindomain = $amassoutput[0]["domain"];

                vhostscan::getVHosts(0, $amassoutput, $vhostwordlist);

                if( isset($amassoutput) && !empty($amassoutput) ) {

                    if ( !empty( $iparray ) ){
                        $alive = vhostscan::httpxhosts($amassoutput, $iparray);
                    } else $alive = vhostscan::httpxhosts($amassoutput, 0);
                    

                    foreach($alive as $host) {

                        if($host!=""){
                            $output[] = vhostscan::findVhostsWithDomain($host);
                            $output[] = vhostscan::findVhostsNoDomain($host);
                        }
                    }

                } else if ( !empty($iparray) ) {
                    $alive = vhostscan::httpxhosts(0, $iparray);

                    foreach($alive as $ip) {

                        if($ip!=""){
                            $output[] = vhostscan::findVhostsNoDomain($ip);
                        }
                    }
                }

                vhostscan::saveToDB( $taskid, $output );
                //filter empty elemts from output, use alive IPS for later nmap purposes
            }

            dirscan::queuedone($input["queueid"]); //no amass results - maybe task already been deleted

            exec("sudo rm -R /ffuf/vhost" . $randomid . " &");

            return 1;
        }
    }
}


