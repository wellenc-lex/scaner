<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;
use frontend\models\Queue;
require_once 'Dirscan.php';

class jsa extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function savetodb($taskid, $hostname, $jsa_output)
    {

        if( $jsa_output == "c2VjcmV0ZmluZGVyIGVycm9yIG5vIGZpbGU=" ){
            return 1; //no need to save empty results
        }

        try{
            
            $jsa = new Tasks();
            $jsa->host = $hostname;
            $jsa->dirscan_status = "Done.";
            $jsa->notify_instrument = $task->notify_instrument."9";
            $jsa->js = $jsa_output;
            $jsa->date = date("Y-m-d H-i-s");

            $jsa->save();
           
        } catch (\yii\db\Exception $exception) {

            sleep(1000);
            $jsa = new Tasks();
            $jsa->host = $hostname;
            $jsa->dirscan_status = "Done.";
            $jsa->notify_instrument = $task->notify_instrument."9";
            $jsa->js = $jsa_output;
            $jsa->date = date("Y-m-d H-i-s");

            $jsa->save();
            
            return $exception.$jsa_output;
        }

        return 1;
    }

    public static function jsa($input)
    {
        global $randomid;

        if( $input["url"] != "") $urls = explode(PHP_EOL, $input["url"]); else return 0; //no need to scan without supplied url

        foreach ($urls as $currenturl){

            $hostname = dirscan::ParseHostname($currenturl);

            $port = dirscan::ParsePort($currenturl);

            $taskid = (int) $input["taskid"]; if($taskid=="") $taskid = 1000;

            $randomid = rand(1000,10000000);

            if (strpos($currenturl, 'https://') !== false) {
                $scheme = "https://";
            } else $scheme = "http://";

            $hostname = trim($hostname, ' ');
            $hostname = rtrim($hostname, '/');

            $hostname = trim($hostname, ' ');
            $port = trim($port, ' ');

            exec("sudo mkdir /jsa/" . $randomid . "/ && sudo chmod -R 777 /jsa/" . $randomid . "/"); //create dir for ffuf scan results

            exec("timeout 80400 sudo docker run --cpu-shares 128 --rm -v jsa:/jsa 5631/jsa " . escapeshellarg($scheme.$hostname.$port) . " " . $randomid . " ");

            if (file_exists("/jsa/" . $randomid . "/secretfinder.html")) {
                $secretfinder = file_get_contents("/jsa/" . $randomid . "/secretfinder.html");
            } else $secretfinder="secretfinder error no file";

            $jsa_output = base64_encode($secretfinder); //htmls encoded so there will be no error with inserting into db

            exec("sudo rm -r /jsa/" . $randomid . "/");;
    
            jsa::savetodb($taskid, $hostname, $jsa_output);

            dirscan::queuedone($input["queueid"]);
        }
        
        return 1;
    }

}


































