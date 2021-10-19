<?php

namespace frontend\models;

use Yii;
use frontend\models\Queue;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;
require_once 'Dirscan.php';

class Ipscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function savetodb($hostname, $output)
    {
        global $randomid;

        if( empty($outputarray) ){
            return 1; //no need to save empty results
        }

        try{
            
            $task = new Tasks();
            $task->host = $hostname;
            $task->ips_status = "Done.";
            $task->notify_instrument = $task->notify_instrument."5";
            $task->ips = $output;
            $task->date = date("Y-m-d H-i-s");

            $task->save();
           
        } catch (\yii\db\Exception $exception) {

            sleep(2000);
            Yii::$app->db->open();
            $task = new Tasks();
            $task->host = $hostname;
            $task->ips_status = "Done.";
            $task->notify_instrument = $task->notify_instrument."5";
            $task->ips = $output;
            $task->date = date("Y-m-d H-i-s");

            $task->save();
            
            return file_put_contents("/ffuf/error".$randomid, $exception.$output);
        }

        if( $output != "" ){
            

            /* nmap scan for found ips
            $queue = new Queue();
            $queue->dirscanUrl = $scanurl;
            $queue->instrument = 1; //jsa
            $queue->save();
            */

            //nmap save as OG + parse + httpx + dirscan automatically
        }
  
    }

    public static function ipscan($input)
    {
        global $randomid;
        
        $parsed_queries = array();

        if( $input["query"] != "") $queries = explode(PHP_EOL, $input["query"]); else return 0; //no need to scan without supplied url

        $randomid = rand(100000, 1000000000);

        $queriesfile = "/ffuf/" . $randomid . "/" . $randomid . "queries.txt";

        $outputfile = "/ffuf/" . $randomid . "/" . $randomid . "ipscanoutput.txt";

        $apikeysfile = "/configs/ipscanapikeys.json";

        foreach ($queries as $query){

            //we assume that we get clear amass root domain (without any subdomains) in format google.com so queries will be google.com and google
            $hostname = dirscan::ParseHostname($query);
            preg_match("/^[\w\-\_\d=]+/i", $query, $hostonly);

            if($hostname != $hostonly[0]){
                
                $parsed_queries[] = $hostname;
                $parsed_queries[] = $hostonly[0];

            }

        }

        exec("sudo mkdir /ffuf/" . $randomid . "/ "); //create dir for ffuf scan results
        exec("sudo chmod -R 777 /ffuf/" . $randomid . "/ ");

        file_put_contents($queriesfile, implode( PHP_EOL, $parsed_queries) );


        exec("sudo docker run --cpu-shares 512 --rm --network=docker_default -v ffuf:/ffuf -v configs:/configs/ 5631/passivequeries python3 passivequery.py -i " . $queriesfile  
            . " -a " . $apikeysfile . " -o " . $outputfile);
            

        $output = implode( ' ', array_unique( file_get_contents( $outputfile ) ) );

        ipscan::savetodb($hostname, $output);

        dirscan::queuedone($input["queueid"]);

        return exec("sudo rm -r /ffuf/" . $randomid . "/");
    }

}


