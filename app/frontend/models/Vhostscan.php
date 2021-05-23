<?php



// несколько портов задать через запятую или один порт одна линия - к примеру опросить 80,8080,443

// список из много айпи и много кастомных хостов

//ffuf -w hosts.txt:HOSTS -w content.txt:FUZZ -u https://HOSTS/FUZZ


//ffuf -w /path/to/vhost/wordlist -u https://target -H "Host: FUZZ" -mc all



//$headers = "-H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CF_CONNECTING_IP: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1' -H 'X_IMFORWARDS: 127.0.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'ZCACHE_CONTROL: 127.0.0.1' -H 'CF-Connecting-IP: 127.0.0.1' ";

//$start_dirscan_localhost = "sudo mkdir /ffuf/" . $randomid . " && sudo docker run --cpu-shares 256 --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -t 1 -p 3 " . $headers . " -H 'Host: localhost' -mc all -w /configs/dict.txt -r -ac -o /ffuf/" . $randomid . "/" . $randomid . "localhost.json -od /ffuf/" . $randomid . "/ -of json ";

            //exec($start_dirscan_localhost); 




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
            if ($domain != "") $hostname = $hostname." ".$domain;
        }
        
        return $hostname;
    }

    public static function vhostscan($input)
    {
        function ipCheck($IP, $CIDR){
            
            list ($net, $mask) = explode("/", $CIDR);

            $ip_net = ip2long($net);
            $ip_mask = ~((1 << (32 - $mask)) - 1);

            $ip_ip = ip2long($IP);

            $ip_ip_net = $ip_ip & $ip_mask;

            return ($ip_ip_net == $ip_net);
        }

        $headers = " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -H 'X-debug: 1' -H 'debug: 1' -H 'CACHE_INFO: 127.0.0.1' -H 'CF_CONNECTING_IP: 127.0.0.1' -H 'CLIENT_IP: 127.0.0.1' -H 'COMING_FROM: 127.0.0.1' -H 'CONNECT_VIA_IP: 127.0.0.1' -H 'FORWARDED: 127.0.0.1' -H 'HTTP-CLIENT-IP: 127.0.0.1' -H 'HTTP-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-PC-REMOTE-ADDR: 127.0.0.1' -H 'HTTP-PROXY-CONNECTION: 127.0.0.1' -H 'HTTP-VIA: 127.0.0.1' -H 'HTTP-X-FORWARDED-FOR-IP: 127.0.0.1' -H 'HTTP-X-IMFORWARDS: 127.0.0.1' -H 'HTTP-XROXY-CONNECTION: 127.0.0.1' -H 'PC_REMOTE_ADDR: 127.0.0.1' -H 'PRAGMA: 127.0.0.1' -H 'PROXY: 127.0.0.1' -H 'PROXY_AUTHORIZATION: 127.0.0.1' -H 'PROXY_CONNECTION: 127.0.0.1' -H 'REMOTE_ADDR: 127.0.0.1' -H 'VIA: 127.0.0.1' -H 'X_COMING_FROM: 127.0.0.1' -H 'X_DELEGATE_REMOTE_HOST: 127.0.0.1' -H 'X_FORWARDED: 127.0.0.1' -H 'X_FORWARDED_FOR_IP: 127.0.0.1' -H 'X_IMFORWARDS: 127.0.0.1' -H 'X_LOOKING: 127.0.0.1' -H 'XONNECTION: 127.0.0.1' -H 'XPROXY: 127.0.0.1' -H 'XROXY_CONNECTION: 127.0.0.1' -H 'ZCACHE_CONTROL: 127.0.0.1'";

        if ((isset($input["taskid"]) && $input["taskid"] != "") && (isset($input["domain"]) && $input["domain"] != "")
            && (isset($input["port"]) && $input["port"] != "") && (isset($input["ip"]) && $input["ip"] != "")) {

            $taskid = (int) $input["taskid"];

            $outputdomain = array();
            $length = array();

            $vhostlist = explode("\n", file_get_contents("/configs/vhostwordlist.txt"));

            if ($port == 443 || $port == 8443 || (isset($input["ssl"]) && $input["ssl"] == "1")) {
                $scheme = "https";
            } else $scheme = "http";

            //foreach ip

            $ips = explode(PHP_EOL, $input["ip"]);
            $domains = explode(PHP_EOL, $input["domain"]);
            $ports = explode(PHP_EOL, $input["port"]);
            

            //asks each ip each domain on each port in cycle

            foreach($ips as $iparray){
                //echo $iparray;

                foreach($ports as $portarray){
                    //echo $portarray;

                    foreach($domains as $domainarray){
                        //echo $domainarray;

                        $port = escapeshellarg($portarray);
                        $maindomain = vhost::ParseHostname($domainarray);
                        $ip = escapeshellarg($iparray);

                        //Asks Host:localhost/domain.com/ directory
                        //$curl_result = exec("curl --insecure --path-as-is " . $headers . " -s ". $scheme ."://localhost/" . $maindomain . "/ -L --resolve \"localhost:" . $port . ":0" . $ip . "\"");

                        $curl_result = exec("curl --insecure --path-as-is " . $headers . " -s ". $scheme ."://localhost/" . $maindomain . "/ -L --resolve \"localhost:" . $port . ":0" . $ip . "\"");
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

                            $curl_result = exec("curl --insecure --path-as-is " . $headers . " -s " . $scheme . "://" . $domaintoask . ":0" . $port . " -L --resolve \"" . $domaintoask . ":" . $port . ":0" . $ip . "\"");
                            sleep(1);

                            $curl_length = strlen(trim($curl_result));

                            if ($curl_length > 0 && !in_array($curl_length,$length)) {
                                $newdata = array(
                                    'ip' => $ip,
                                    'port' => $port,
                                    'length' => $curl_length,
                                    'domain' => $domaintoask,
                                    'body' => base64_encode($curl_result),
                                );
                                $outputdomain[] = $newdata;
                            } if (!in_array($curl_length,$length)) $length[] = $curl_length;

                            $curl_result = exec("curl --insecure --path-as-is " . $headers . " -s " . $scheme . "://" . $domaintoask . "." . $maindomain . ":" . $port . " -L --resolve \"" . $domaintoask . "." . $maindomain . ":" . $port . ":0" . $ip . "\"");
                            sleep(1);

                            if ($curl_length > 0 && !in_array($curl_length,$length)) {
                                $newdata = array(
                                    'ip' => $ip,
                                    'port' => $port,
                                    'length' => trim($curl),
                                    'domain' => $domaintoask . "." . $maindomain,
                                );
                                $outputdomain[] = $newdata;
                            } if (!in_array($curl_length,$length)) $length[] = $curl_length;



return 1;
                        }
                    
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
            $task->vhost = json_encode($outputdomain);
            $task->date = date("Y-m-d H-i-s");

            $task->save();
            
            return 1;
        }

        if ((isset($input["taskid"]) && $input["taskid"] != "") && (!isset($input["domain"]))) {

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

