<?php

namespace frontend\models;

use yii\db\ActiveRecord;

class Dirscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public static function dirscan($input)
    {
        
        function is_valid_domain_name($domain_name){
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
                && preg_match("/^.{1,253}$/", $domain_name) //overall length check
                && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); //length of each label
        }

        $url = $input["url"];

        $url = rtrim($url, '/');
        $url = rtrim($url, '/');

        $url = ltrim($url, ' ');
        $url = rtrim($url, ' ');

        $url = str_replace(",", " ", $url);
        $url = str_replace("\r", " ", $url);
        $url = str_replace("\n", " ", $url);
        $url = str_replace("|", " ", $url);
        $url = str_replace("&", " ", $url);
        $url = str_replace("&&", " ", $url);
        $url = str_replace(">", " ", $url);
        $url = str_replace("<", " ", $url);
        $url = str_replace("'", " ", $url);
        $url = str_replace("\"", " ", $url);
        $url = str_replace("\\", " ", $url);

        $url = strtolower($url);

        $taskid = $input["taskid"];

        $randomid = rand(1, 1000000);

        if (strpos($url, 'https://') !== false) {
                $scheme = "https://";
                $url = str_replace("https://", "", $url);
            } else {
                $scheme = "http://";
                $url = str_replace("http://", "", $url);
        }

        if (!isset($input["ip"])) {
            $start_dirscan = "sudo mkdir /ffuf/" . $randomid . "/ && sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$url) . "/FUZZ -t 8 -p 0.5-1.5 -mc all -w /configs/dict.txt -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -r -ac -D -e log,php,asp,aspx,jsp,py,txt,conf,config,bak,backup,swp,old,db,sql -o /ffuf/" . $randomid . "/" . $randomid . ".json -od /ffuf/" . $randomid . "/ -of json";
        }

        if (isset($input["ip"])) {

            $ip = $input["ip"];
            $ip = ltrim($ip, ' ');
            $ip = rtrim($ip, ' ');

            $start_dirscan = "sudo mkdir /ffuf/" . $randomid . " && sudo docker run --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/ffuf -u " . escapeshellarg($scheme.$ip."/FUZZ") . " -t 2 -s -p 0.5-1.5 -H " . escapeshellarg('Host: ' . $url) . " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -mc all -w /configs/dict.txt -r -ac -D -e log,php,asp,aspx,jsp,py,txt,conf,config,bak,backup,swp,old,db,sql -o /ffuf/" . $randomid . "/" . $randomid . ".json -od /ffuf/" . $randomid . "/ -of json ";
        }

        system($start_dirscan);

        if (is_valid_domain_name($url)){
            $wayback_result = array();
            $string = array();
            
            exec("curl \"http://web.archive.org/cdx/search/cdx?url=". "*.".$url."/*" . "&output=list&fl=original&collapse=urlkey\"", $wayback_result);

            foreach ($wayback_result as $id => $result) {
                //wayback saves too much (js,images,xss payloads)
                if(preg_match("/(icons|image|img|images|css|fonts|font-icons|.png|.jpeg|.jpg|.js|%22|\"|\">|<|<\/|\<\/)/i", $result) === 1 ){
                    unset($wayback_result[$id]);
                    continue;
                }
            }

            $wayback_result = array_map('htmlentities',$wayback_result);
            $wayback_result = json_encode($wayback_result, JSON_UNESCAPED_UNICODE);

        } else $wayback_result = "Wrong domain.";
        
        //Get dirscan results file
        if (file_exists("/ffuf/" . $randomid . "/" . $randomid . ".json")) {
            $output = file_get_contents("/ffuf/" . $randomid . "/" . $randomid . ".json");
            $output = json_decode($output, true);

            $outputarray = array();
            $id=0;

            foreach ($output["results"] as $results) {
            
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

