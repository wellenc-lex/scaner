<?php

namespace frontend\models;

use yii\db\ActiveRecord;

class Dirscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function ParseHostname($url)
    {
        $url = strtolower($url);

        preg_match("/(https?:\/\/)([a-zA-Z-\d\.]*)/", $url, $domain); //get hostname only
        
<<<<<<< HEAD
        function is_valid_domain_name($domain_name){
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
                && preg_match("/^.{1,253}$/", $domain_name) //overall length check
                && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); //length of each label
        }
=======
        return $domain[2]; //group 2 == domain name
    }
>>>>>>> 25872b2... Merge remote-tracking branch 'origin/master'

    public function ParseIP($ip)
    {
        $ip = strtolower($ip);

        preg_match("/([\w\.-\:])*/i", $ip, $ip); //get ip only
      
        return $ip[0]; //everything thats inside regex
    }

    public function ParsePort($url)
    {
        $url = strtolower($url);

        preg_match("/(https?:\/\/)([a-z-\d\.]*)(:\d*)/", $url, $port); //get hostname only
        
        return $port[3]; //group 2 == port
    }

    public static function dirscan($input)
    {
        
        $url = dirscan::ParseHostname($input["url"]);

        $port = dirscan::ParsePort($input["url"]);

<<<<<<< HEAD
        $randomid = rand(1, 1000000);
=======
        if ($port != "") $port = dirscan::ParsePort($input["url"]); else $port = "";

        $taskid = (int) $input["taskid"];
>>>>>>> 25872b2... Merge remote-tracking branch 'origin/master'

        $randomid = $taskid;

        if (strpos($input["url"], 'https://') !== false) {
                $scheme = "https://";
            } else {
                $scheme = "http://";
        }

<<<<<<< HEAD
        if (!isset($input["ip"])) {
            $start_dirscan = "sudo mkdir /ffuf/" . $randomid . "/ && sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$url) . "/FUZZ -t 8 -p 0.5-1.5 -mc all -w /configs/dict.txt -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -r -ac -D -e log,php,asp,aspx,jsp,py,txt,conf,config,bak,backup,swp,old,db,sql -o /ffuf/" . $randomid . "/" . $randomid . ".json -od /ffuf/" . $randomid . "/ -of json";
=======
        $url = rtrim($url, ' ');
        $url = rtrim($url, '/');

        $hostfull = substr($url, 0, strrpos($url, ".")); //hostname without www. and .com at the end

        $hostonly = preg_replace("/(\w)*\./", "", $hostfull); //hostname without subdomain and .com at the end

        if (!isset($input["ip"])) {
            $start_dirscan = "sudo mkdir /ffuf/" . $randomid . "/ && sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$url.$port."/FUZZ") . " -t 1 -p 2 -mc all -w /configs/dict.txt -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'CF-Connecting-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -r -ac -D -e " . escapeshellarg("log,php,asp,aspx,jsp,py,txt,conf,config,bak,backup,swp,old,db,sql,com,zip,tar,rar,tgz,tar.gz,".$url.",".$hostfull.",".$hostonly) . " -o /ffuf/" . $randomid . "/" . $randomid . ".json -od /ffuf/" . $randomid . "/ -of json";
>>>>>>> 25872b2... Merge remote-tracking branch 'origin/master'
        }

        if (isset($input["ip"])) {

<<<<<<< HEAD
            $ip = $input["ip"];
            $ip = ltrim($ip, ' ');
            $ip = rtrim($ip, ' ');

            $start_dirscan = "sudo mkdir /ffuf/" . $randomid . " && sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$ip."/FUZZ") . " -t 2 -s -p 0.5-1.5 -H " . escapeshellarg('Host: ' . $url) . " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -mc all -w /configs/dict.txt -r -ac -D -e log,php,asp,aspx,jsp,py,txt,conf,config,bak,backup,swp,old,db,sql -o /ffuf/" . $randomid . "/" . $randomid . ".json -od /ffuf/" . $randomid . "/ -of json ";
        }

        system($start_dirscan);
=======
            $ip = dirscan::ParseIP($input["ip"]);

            $start_dirscan = "sudo mkdir /ffuf/" . $randomid . " && sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -t 1 -s -p 2 -H " . escapeshellarg('Host: ' . $url) . " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'CF-Connecting-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -mc all -w /configs/dict.txt -r -ac -D -e " . escapeshellarg("log,php,asp,aspx,jsp,py,txt,conf,config,bak,backup,swp,old,db,sql,com,zip,tar,rar,tgz,tar.gz,".$url.",".$hostfull.",".$hostonly) . " -o /ffuf/" . $randomid . "/" . $randomid . ".json -od /ffuf/" . $randomid . "/ -of json ";

            $start_dirscan_localhost = "sudo mkdir /ffuf/" . $randomid . " && sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -t 1 -s -p 2 -H 'Host: localhost' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'CF-Connecting-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -mc all -w /configs/dict.txt -r -ac -D -e " . escapeshellarg("log,php,asp,aspx,jsp,py,txt,conf,config,bak,backup,swp,old,db,sql,com,zip,tar,rar,tgz,tar.gz,".$url.",".$hostfull.",".$hostonly) . " -o /ffuf/" . $randomid . "/" . $randomid . "localhost.json -od /ffuf/" . $randomid . "/ -of json ";

            exec($start_dirscan_localhost); 
>>>>>>> 25872b2... Merge remote-tracking branch 'origin/master'

        }
        
        $wayback_result = array();
        $string = array();
            
<<<<<<< HEAD
            exec("curl \"http://web.archive.org/cdx/search/cdx?url=". "*.".$url."/*" . "&output=list&fl=original&collapse=urlkey\"", $wayback_result);
=======
        exec("curl \"http://web.archive.org/cdx/search/cdx?url=". "*." . $url . "/*" . "&output=list&fl=original&collapse=urlkey\"", $wayback_result);
>>>>>>> 25872b2... Merge remote-tracking branch 'origin/master'

        foreach ($wayback_result as $id => $result) {
            //wayback saves too much (js,images,xss payloads)
            if(preg_match("/(icons|image|img|images|css|fonts|font-icons|robots.txt|.png|.jpeg|.jpg|.js|.svg|%22|\"|\">|<|<\/|\<\/|%20| |%0d%0a)/i", $result) === 1 ){
                unset($wayback_result[$id]);
                continue;
            }
        }

<<<<<<< HEAD
            $wayback_result = array_map('htmlentities',$wayback_result);
            $wayback_result = json_encode($wayback_result, JSON_UNESCAPED_UNICODE);

        } else $wayback_result = "Wrong domain.";
=======
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

            if(preg_match("/(icons|image|img|images|css|gif|tiff|woff|woff2|fonts|font-icons|robots.txt|.png|.jpeg|.jpg|.js|%22|\"|\">|<|<\/|\<\/|%20| |%0d%0a|mp4|webm|.svg)/i", $alienvault_urls["url"]) === 1 ){ continue; } else $wayback_result[] = $alienvault_urls["url"];
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
                       if(preg_match("/(icons|image|img|images|css|gif|tiff|woff|woff2|fonts|font-icons|robots.txt|.png|.jpeg|.jpg|.js|%22|\"|\">|<|<\/|\<\/|%20| |%0d%0a|mp4|webm|.svg)/i", $id["url"]) === 1 ){
                           continue;
                       } else $wayback_result[] = $id["url"];
                   } 

            }
        } //else $wayback_result[] = "Not an array.";

        exec($start_dirscan); 
>>>>>>> 25872b2... Merge remote-tracking branch 'origin/master'
        
        //Get dirscan results file
        if (file_exists("/ffuf/" . $randomid . "/" . $randomid . ".json")) {
            $output = file_get_contents("/ffuf/" . $randomid . "/" . $randomid . ".json");
            $output = json_decode($output, true);

            $outputarray = array();
            $id=0;

            foreach ($output["results"] as $results) {
<<<<<<< HEAD
            
            if ($results["length"] >= 0){
                $id++;
                $outputarray[$id]["url"] = $results["url"];
                $outputarray[$id]["length"] = $results["length"];
                $outputarray[$id]["status"] = $results["status"];
                $outputarray[$id]["redirect"] = $results["redirectlocation"];

                if ($results["length"] < 150000 ){
                    exec("sudo chmod -R 755 /ffuf");

                    $outputarray[$id]["resultfile"] = base64_encode(file_get_contents("/ffuf/" . $randomid . "/" 
                . $results["resultfile"] . ""));
                    }
                }
            }
            $outputarray = json_encode($outputarray);
        } else $outputarray = "No file.";

=======
                if ($results["length"] >= 0 && !in_array($results["length"], $result_length)){
                    $id++;
                    $result_length[] = $results["length"];//so no duplicates gonna be added
                    $outputarray[$id]["url"] = $results["url"];
                    $outputarray[$id]["length"] = $results["length"];
                    $outputarray[$id]["status"] = $results["status"];
                    $outputarray[$id]["redirect"] = $results["redirectlocation"];

                    if ($results["length"] < 350000 ){
                        exec("sudo chmod -R 755 /ffuf/" . $randomid . "/");

                        $outputarray[$id]["resultfile"] = base64_encode(file_get_contents("/ffuf/" . $randomid . "/" . $results["resultfile"] . ""));
                    }
                }
            }
            $outputdirscan = $outputarray;
        } else $outputarray = "No file.";

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
                        exec("sudo chmod -R 755 /ffuf/" . $randomid . "/");

                        $output_localhost_array[$id]["resultfile"] = base64_encode(file_get_contents("/ffuf/" . $randomid . "/" . $results["resultfile"] . ""));
                    }
                }
            }
            $output_localhost_array = json_encode($output_localhost_array);
        } else $output_localhost_array = "No file.";

        if ($output_localhost_array != "No file.") {
            $outputarray = json_encode(array_merge($outputdirscan,$output_localhost_array));
        } else $outputarray = json_encode($outputdirscan);

        $wayback_result = array_unique($wayback_result);
        $wayback_result = array_map('htmlentities', $wayback_result);
        $wayback_result = json_encode($wayback_result, JSON_UNESCAPED_UNICODE);

>>>>>>> 25872b2... Merge remote-tracking branch 'origin/master'
        $date_end = date("Y-m-d H-i-s");

        $dirscan = Tasks::find()
            ->where(['taskid' => $taskid])
            ->limit(1)
            ->one();

        $dirscan->dirscan_status = "Done.";
        $dirscan->dirscan = $outputarray;
        $dirscan->wayback = $wayback_result;
        $dirscan->date = $date_end;

        $dirscan->save();

        //Scaner's work is done -> decrement scaner's amount in DB
        $decrement = ToolsAmount::find()
            ->where(['id' => 1])
            ->one();

        $value = $decrement->dirscan;
        
        if ($value <= 1) {
            $value=0;
        } else $value = $value-1;

        $decrement->dirscan=$value;
        $decrement->save();

        exec("sudo rm -r /ffuf/" . $randomid . "/");
        return 1;

    }

}

