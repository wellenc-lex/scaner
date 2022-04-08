<?php
namespace frontend\models\passive;

use frontend\models\PassiveScan;
use yii\db\ActiveRecord;

class Dirscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'passive_scan';
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

        preg_match("/([\w\.-\:])*/i", $ip, $ip); //get ip only //weird regex because IP parses throguht regexp earlier - when form submits
      
        return $ip[0]; //everything thats inside regex
    }

    public function ParsePort($url)
    {
        $url = strtolower($url);

        preg_match("/(https?:\/\/)([a-z-\d\.]*)(:\d)*/", $url, $port); //get hostname only
        
        return $port[3]; //group 2 == port
    }

    public static function dirscan($input)
    {
        $changes = 0;
        
        $url = dirscan::ParseHostname($input["url"]);

        $port = dirscan::ParsePort($input["url"]);

        if ($port != "") $port = dirscan::ParsePort($input["url"]); else $port = "";

        $scanid = (int) $input["scanid"];

        $randomid = $scanid;

        if (strpos($input["url"], 'https://') !== false) {
                $scheme = "https://";
            } else {
                $scheme = "http://";
        }

        $url = rtrim($url, ' ');
        $url = rtrim($url, '/');

        $domainfull = substr($url, 0, strrpos($url, ".")); //hostname without www. 

        $hostonly = preg_replace("/(\w)*\./", "", $domainfull); //hostname without subdomain and .com at the end

        if ($domainfull == $hostonly) $hostonly = ""; //remove duplicate extension from scan

        $extensions = "log,php,asp,aspx,jsp,py,txt,conf,config,bak,backup,swp,old,db,sql,com,zip,tar,rar,tgz,tar.gz,".$url.",".$domainfull.",".$hostonly;

        if (!isset($input["ip"])) {
            $start_dirscan = "sudo mkdir /ffuf/" . $randomid . "/ && sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$url.$port."/FUZZ") . " -t 1 -p 1 -mc all -w /configs/dict.txt -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H ' X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -r -ac -D -e " . escapeshellarg($extensions) . " -o /ffuf/" . $randomid . "/" . $randomid . ".json -od /ffuf/" . $randomid . "/ -of json";
        }

        if (isset($input["ip"])) {

            $ip = dirscan::ParseIP($input["ip"]);

            $start_dirscan = " sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -t 1 -p 1 -H " . escapeshellarg('Host: ' . $url) . " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H ' X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'CF-Connecting-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -mc all -w /configs/dict.txt -r -ac -D -e " . escapeshellarg($extensions) . " -o /ffuf/" . $randomid . "/" . $randomid . ".json -od /ffuf/" . $randomid . "/ -of json ";

            $start_dirscan_localhost = "sudo mkdir /ffuf/" . $randomid . " && sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$ip.$port."/FUZZ") . " -t 1 -p 1 -H 'Host: localhost' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'X-Originating-IP: 127.0.0.1' -H 'X-Forwarded-For: 127.0.0.1' -H 'X-Remote-IP: 127.0.0.1' -H ' X-Forwarded-Host: 127.0.0.1' -H 'Client-IP: 127.0.0.1' -H 'Forwarded-For-Ip: 127.0.0.1' -H 'Forwarded-For: 127.0.0.1' -H 'Forwarded: 127.0.0.1' -H 'X-Forwarded-For-Original: 127.0.0.1' -H 'X-Forwarded-By: 127.0.0.1' -H 'X-Forwarded: 127.0.0.1' -H 'X-Custom-IP-Authorization: 127.0.0.1' -H 'X-Remote-Addr: 127.0.0.1' -H 'X-Real-IP: 127.0.0.1' -H 'X-Client-IP: 127.0.0.1' -H 'X-Host: 127.0.0.1' -H 'X-Forwared-Host: 127.0.0.1' -H 'True-Client-IP: 127.0.0.1' -H 'CF-Connecting-IP: 127.0.0.1' -H 'X-Cluster-Client-IP: 127.0.0.1' -H 'Fastly-Client-IP: 127.0.0.1' -mc all -w /configs/dict.txt -r -ac -D -e " . escapeshellarg($extensions) . " -o /ffuf/" . $randomid . "/" . $randomid . "localhost.json -od /ffuf/" . $randomid . "/ -of json ";

            exec($start_dirscan_localhost); 
        }

        exec($start_dirscan); 
        
        //Get dirscan results file from volume
        if (file_exists("/ffuf/" . $randomid . "/" . $randomid . ".json")) {

            exec("sudo chmod -R 777 /ffuf/" . $randomid . "/*");
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
                        $outputarray[$id]["resultfile"] = base64_encode(file_get_contents("/ffuf/" . $randomid . "/" . $results["resultfile"] . ""));
                    }
                }
            }
            $outputdirscan = $outputarray;
        } else $outputarray = "No file.";

        //Get localhost dirscan results file from volume
        if (file_exists("/ffuf/" . $randomid . "/" . $randomid . "localhost.json")) {

            exec("sudo chmod -R 777 /ffuf/" . $randomid . "/*");
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
                        $output_localhost_array[$id]["resultfile"] = base64_encode(file_get_contents("/ffuf/" . $randomid . "/" . $results["resultfile"] . ""));
                    }
                }
            }
        } else $output_localhost_array = "No file.";

        if ($output_localhost_array != "No file." && !is_null($output_localhost_array) ) {
            $outputarray = json_encode(array_merge($outputdirscan,$output_localhost_array));
        } else $outputarray = json_encode($outputdirscan);

        $dirscan = PassiveScan::find()
            ->where(['PassiveScanid' => $scanid])
            ->limit(1)
            ->one();

        if ($dirscan->dirscan_new == "" || $dirscan->dirscan_new == "null") {
            $dirscan->dirscan_new = $outputarray;

            $dirscan->save();

            exec("sudo rm -r /ffuf/" . $randomid . "/");

            return 0; // no diffs
        } elseif ($dirscan->dirscan_new != "") {

            if ($outputarray == $dirscan->dirscan_new) {
                $changes = 0; // no diffs
            } else {
                $changes = 1; // changes detected
            }

            $dirscan->dirscan_previous = $dirscan->dirscan_new;
            $dirscan->dirscan_new = $outputarray;

            $dirscan->save();

            exec("sudo rm -r /ffuf/" . $randomid . "/");
        }
        return $changes;
    }
}
