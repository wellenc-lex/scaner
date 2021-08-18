<?php
namespace frontend\models;

use yii\db\ActiveRecord;
use Yii;
set_time_limit(0);

class Vhostscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function ParseHostname($url)
    {
        $url = strtolower($url);

        preg_match_all("/(https?:\/\/)*([\w\:\.]*)/i", $url, $domains); 

        foreach ($domains[2] as $domain) {
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
                    if ($results["length"] >= 0 && !in_array($results["length"], $result_length)){
                        $id++;
                        $result_length[] = $results["length"];//so no duplicates gonna be added
                        $output_vhost_array[$id]["url"] = $results["url"];
                        $output_vhost_array[$id]["length"] = $results["length"];
                        $output_vhost_array[$id]["status"] = $results["status"];
                        $output_vhost_array[$id]["redirect"] = $results["redirectlocation"];
                        $output_vhost_array[$id]["host"] = $results["host"];

                        if ($results["length"] < 350000 ){
                            exec("sudo chmod -R 777 /ffuf/" . $randomid . "/");

                            $output_vhost_array[$id]["resultfile"] = base64_encode(file_get_contents("/ffuf/vhost" . $randomid . "/" . $results["resultfile"] . ""));
                        }
                    }
                }
            } else $output_vhost_array = "No file.";
        } else $output_vhost_array = "No file.";
        
        return $output_vhost_array;
    }

    public function FindVhostsWithDomain($scheme, $ip, $port)
    {
        global $headers;
        global $randomid;

        $ip = trim($ip, ' ');
        $port = trim($port, ' ');

        $ffuf_general_string = "sudo docker run --cpu-shares 256 --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -ac -r -o /ffuf/vhost" . $randomid . "/" . $randomid . "domain.json -od /ffuf/vhost" . $randomid . "/ -of json -mc all -t 1 " . $headers . " -w /ffuf/vhost" . $randomid . "/wordlist.txt:FUZZ -u ";

        $vhost_file_location = "/ffuf/vhost" . $randomid . "/" . $randomid . "domain.json";

        //Asks Host:localhost.domain.com, dev.domain.com, etc
        exec($ffuf_general_string . escapeshellarg($scheme . $ip .":". $port ."/" ) . " -H 'Host: FUZZ.HOSTS' -w /ffuf/vhost" . $randomid . "/domains.txt:HOSTS ");

        $output_vhost[] = vhostscan::ReadFFUFResult($vhost_file_location);

        //Asks Host:admin.dev, asdf.dev
        exec($ffuf_general_string . escapeshellarg($scheme . $ip .":". $port ."/") . " -H 'Host: FUZZ.dev' ");

        $output_vhost[] = vhostscan::ReadFFUFResult($vhost_file_location);

        //Asks localhost/domain.com/
        exec($ffuf_general_string . escapeshellarg($scheme . $ip .":". $port ."/HOSTS/") . " -H 'Host: FUZZ' -w /ffuf/vhost" . $randomid . "/domains.txt:HOSTS ");

        $output_vhost[] = vhostscan::ReadFFUFResult($vhost_file_location);

        //Asks Host:admin.local, asdf.local
        exec($ffuf_general_string . escapeshellarg($scheme . $ip .":". $port ."/") . " -H 'Host: FUZZ.local' ");

        $output_vhost[] = vhostscan::ReadFFUFResult($vhost_file_location);

        $output_vhost = array_unique($output_vhost);

        return $output_vhost;
    }

    public function findVhostsNoDomain($scheme, $ip, $port)
    {
        global $headers;
        global $randomid;

        $ip = trim($ip, ' ');
        $port = trim($port, ' ');
            
        //Asks Host:localhost, dev, etc
        exec("sudo docker run --cpu-shares 256 --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$ip.":".$port."/") . " -t 1 -H 'Host: FUZZ'" . $headers . " -mc all -w /configs/vhostwordlist.txt -ac -r -o /ffuf/vhost" . $randomid . "/" . $randomid . "nodomain.json -od /ffuf/vhost" . $randomid . "/ -of json ");

        $output_vhosts = vhostscan::ReadFFUFResult("/ffuf/vhost" . $randomid . "/" . $randomid . "nodomain.json");

        return $output_vhosts;
    }


    public static function vhostscan($input)
    {

        //add regexp - ip regexp, port - onlynumbers - проверка прямо для текущего порта в самом начале выполнения, domain - onlyletters,numbers,/onedot , taskid - only numbers!!

        //regexp - not banning, only searching for needed symbols!
        //dirscan too!!

        // символ переноса строки должен быть в вайтлисте - проверить запросом

        global $headers;
        global $randomid;

        $headers = " -p 1 -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CF_CONNECTING_IP: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1' -H 'X_IMFORWARDS: 127.0.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'ZCACHE_CONTROL: 127.0.0.1' -H 'CF-Connecting-IP: 127.0.0.1' ";

        function ipCheck($IP, $CIDR){
            
            list ($net, $mask) = explode("/", $CIDR);

            $ip_net = ip2long($net);
            $ip_mask = ~((1 << (32 - $mask)) - 1);

            $ip_ip = ip2long($IP);

            $ip_ip_net = $ip_ip & $ip_mask;

            return ($ip_ip_net == $ip_net);
        }

        if ((isset($input["port"]) && $input["port"] != "") && (isset($input["ip"]) && $input["ip"] != "")) {

            if( isset( $input["taskid"]) ) {
                $randomid = (int) $input["taskid"]; 
            } else $randomid = rand(1,100000);

            $ips = explode(PHP_EOL, $input["ip"]);
            $domains = explode(PHP_EOL, $input["domain"]);
            $ports = explode(PHP_EOL, $input["port"]);

            $outputdomain = array();
            $length = array();
            $vhostlist = array();

            $hosts = array();

            exec("sudo mkdir /ffuf/vhost" . $randomid . " &");
            exec("sudo chmod -R 777 /ffuf/vhost" . $randomid . "/ &");
            
            //add domains from the input to the wordlist
            if( isset( $domains ) ){  
                $vhostlist = explode("\n", file_get_contents("/configs/vhostwordlist.txt"));

                foreach($domains as $domainarray){

                    $host = preg_replace("~https?://~", "", $domainarray);
                    $host = rtrim($host, '/');

                    $domainfull = substr($host, 0, strrpos($host, ".")); ///www.something.com -> something.com

                    $hostonly = preg_replace("/(\w)*\./", "", $domainfull); //something.com -> something

                    if ($domainfull == $hostonly) $hostonly = "";

                    if ($domainfull != "") {
                        array_push($vhostlist, $domainfull); //att.com.loc -> att.com

                        if ($hostonly != "") {
                            array_push($vhostlist, $hostonly); //att.com.loc -> att
                        }
                    }

                    $hosts[] = $host;
                }
                
                file_put_contents("/ffuf/vhost" . $randomid . "/wordlist.txt", implode( PHP_EOL, $vhostlist )); //push wordlist on the disk so ffuf could use it
                file_put_contents("/ffuf/vhost" . $randomid . "/domains.txt", implode( PHP_EOL, $hosts)); //to use domains supplied by user as FFUF wordlist
            }

            //asks each ip each domain on each port in cycle
            foreach($ips as $currentIP){
                //echo $currentIP;

                foreach($ports as $currentport){
                    //echo $currentport;

                    if ($currentport == 443 || $currentport == 8443 || (isset($input["ssl"]) && $input["ssl"] == "1")) {
                        $scheme = "https://";
                    } else $scheme = "http://";

                    $output[] = vhostscan::findVhostsNoDomain($scheme, $currentIP, $currentport);

                    if( isset( $domains ) ){
                        $output[] = vhostscan::findVhostsWithDomain($scheme, $currentIP, $currentport);
                    }

                }

            }

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

            $task->vhost = json_encode(array_unique($output));
            $task->date = date("Y-m-d H-i-s");

            $task->save();
            
            return 1;
        }

        if ((isset($input["taskid"]) && $input["taskid"] != "") && (!isset($input["ip"]))) {

            //Cloudflare ip ranges + private networks - no need to curl
            $masks = array("103.21.244.0/22", "103.22.200.0/22", "103.31.4.0/22", "104.16.0.0/12", "108.162.192.0/18", "131.0.72.0/22",
                "141.101.64.0/18", "162.158.0.0/15", "172.64.0.0/13", "188.114.96.0/20", "190.93.240.0/20", "197.234.240.0/22",
                "173.245.48.0/20", "198.41.128.0/17", "172.16.0.0/12", "172.67.0.0/12", "192.168.0.0/16", "10.0.0.0/8");

            $taskid = (int) $input["taskid"];

            $task = Tasks::find()
                ->where(['taskid' => $taskid])
                ->limit(1)
                ->one();

            Yii::$app->db->close();  

            $amassoutput = json_decode($task->amass, true);

            $maindomain = $amassoutput[0]["domain"];

            $checkedips = array();
            $outputdomain = array();
            $length = array();

            $vhostlist = explode("\n", file_get_contents("/configs/vhostwordlist.txt"));

            $host = rtrim($task->host, '/');

            $domainfull = substr($host, 0, strrpos($host, ".")); //hostname without www. and .com at the end

            $hostonly = preg_replace("/(\w)*\./", "", $domainfull); //hostname without subdomain and .com at the end

            if ($domainfull == $hostonly) $hostonly = "";

            foreach ($vhostlist as $list) {
                if ($domainfull != "") {
                    array_push($vhostlist, $domainfull.$list ); //att.com.loc

                    if ($hostonly != "") {
                        array_push($vhostlist, $hostonly.$list ); //att.loc
                    }
                }
            }

            if(isset($amassoutput)){

                foreach ($amassoutput as $json) {
                    if (!in_array($json["name"], $vhostlist)) {
                        array_push($vhostlist, $json["name"]); //if domain found by amass isnt already in vhost list
                    }
                }

                array_unique($vhostlist);

                //Get vhost names from amass scan & wordlist file + use only unique ones

                foreach ($amassoutput as $json) {

                    foreach ($json["addresses"] as $ip) {

                        if (strpos($ip["ip"], '::') === false) { //TODO: add ipv6 support

                            if (strpos($ip["ip"], '127.0.0.1') === false) {

                                if (!in_array($ip["ip"], $checkedips)) { //if ip wasnt called earlier - then call it

                                    $stop = 0;

                                    for ($n = 0; $n < count($masks); $n++) { 

                                        if (((ipCheck($ip["ip"], $masks[$n])) == 1)) { // if IP isnt in blocked mask
                                            $stop = 1;
                                            break;
                                        } else $stop = 0;
                                    }

                                    if ($stop == 0) {

                                        //Asks localhost/domain.com/
                                        $curl_result = exec("curl --insecure --path-as-is " . $headers . " -s http://localhost/" . $maindomain . "/ -L --resolve \"localhost:80:" . $ip["ip"] . "\"");
                                        sleep(1);

                                        $curl_length = strlen(trim($curl_result));

                                        if ($curl_length > 0 && !in_array($curl_length,$length)) {
                                            $newdata = array(
                                                'ip' => $ip["ip"],
                                                'length' => $curl_length,
                                                'domain' => $maindomain,
                                                'body' => base64_encode($curl_result),
                                            );
                                            $outputdomain[] = $newdata;
                                        } if (!in_array($curl_length,$length)) $length[] = $curl_length;

                                        foreach ($vhostlist as $domaintoask) {

                                            //Asks Host:localhost, dev, etc
                                            $curl_result = exec("curl --insecure --path-as-is " . $headers . " -s http://" . $domaintoask . " -L --resolve \"" . $domaintoask . ":80:" . $ip["ip"] . "\"");
                                            sleep(1);

                                            $curl_length = strlen(trim($curl_result));

                                            if ($curl_length > 0 && !in_array($curl_length,$length)) {
                                                $newdata = array(
                                                    'ip' => $ip["ip"],
                                                    'length' => $curl_length,
                                                    'domain' => $domaintoask,
                                                    'body' => base64_encode($curl_result),
                                                );
                                                $outputdomain[] = $newdata;
                                            } if (!in_array($curl_length,$length)) $length[] = $curl_length;

                                            //Asks Host:localhost.domain.com, dev.domain.com, etc
                                            $curl_result = exec("curl --insecure --path-as-is " . $headers . " -s http://" . $domaintoask . "." . $maindomain . " -L --resolve \"" . $domaintoask . "." . $maindomain . ":80:" . $ip["ip"] . "\"");
                                            sleep(1);

                                            $curl_length = strlen(trim($curl_result));

                                            if ($curl_length > 0 && !in_array($curl_length,$length)) {
                                                $newdata = array(
                                                    'ip' => $ip["ip"],
                                                    'length' => $curl_length,
                                                    'domain' => $domaintoask . "." . $maindomain,
                                                    'body' => base64_encode($curl_result),
                                                );
                                                $outputdomain[] = $newdata;
                                            } if (!in_array($curl_length,$length)) $length[] = $curl_length;

                                        } $checkedips[] = $ip["ip"]; //Mark IP as checked out
                                    }
                                }
                            }    
                        }
                    }
                }
            }

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
            $task->vhost = json_encode($outputdomain);
            $task->date = date("Y-m-d H-i-s");

            $task->save();
            
            return 1;
        }
    }
}

