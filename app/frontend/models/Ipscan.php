<?php

namespace frontend\models;

use Yii;
use frontend\models\Queue;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;
use frontend\models\Nmap;


ini_set('max_execution_time', 0);

class Ipscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public static function savetodb($taskid, $hostname, $output)
    {
        global $randomid;

        if( empty($output) ){
            return 1; //no need to save empty results
        }

        try{
            Yii::$app->db->open();

            $task = Tasks::find()
                ->where(['taskid' => $taskid])
                ->limit(1)
                ->one();

            if(!empty($task)){ //if querry exists in db

                $task->ips_status = "Done.";
                $task->ips = $task->ips." ".$output;
                $task->hidden = 1;
                $task->date = date("Y-m-d H-i-s");

                $task->save(); 
            } else {
                $task = new Tasks();
                $task->host = $hostname;
                $task->ips_status = "Done.";
                $task->notify_instrument = "16";
                $task->ips = $output;
                $task->hidden = 1;
                $task->date = date("Y-m-d H-i-s");

                $task->save();

                $taskid = $task->taskid;
            }

        } catch (\yii\db\Exception $exception) {

            sleep(2000);
            ipscan::savetodb($taskid, $hostname, $output);
        }

        if( $output != "" ){

            //add vhost scan to queue
            $queue = new Queue();
            $queue->taskid = $taskid;
            $queue->instrument = 7;
            $queue->save();

            //add nmap scan to queue
            $queue = new Queue();
            $queue->taskid = $task->taskid;
            $queue->instrument = 1;
            $queue->nmap = $output;
            $queue->save();

            Yii::$app->db->close();
        }
    }

    public static function ipscan($input)
    {
        global $randomid;
        
        $parsed_queries = array();

        $taskid = (int) $input["taskid"]; if($taskid=="0") $taskid = "";

        if( $input["query"] != "") $queries = explode(PHP_EOL, $input["query"]); else return 0; //no need to scan without supplied queries

        $randomid = rand(100000, 1000000000);

        $queriesfile = "/dockerresults/" . $randomid . "/queries.txt";

        $outputfile = "/dockerresults/" . $randomid . "/ipscanoutput.txt";

        $apikeysfile = "/configs/ipscankeys/". date("d") . ".json";

        if ( !file_exists($apikeysfile) ) {
            $apikeysfile = "/configs/ipscankeys/1.json";
        }

        foreach ($queries as $query){

            //we assume that we get domain without any subdomains in format google.com so queries will be google.com and google
            $hostname = dirscan::ParseHostname($query);
            //preg_match("/^[\w\-\_\d=]+/i", $query, $hostonly); its cool for small sites but for big company it gives a LOT out of scope ips

            if($hostname != $hostonly[0]){
                
                $parsed_queries[] = $hostname;
                //$parsed_queries[] = $hostonly[0];

            }
        }

        exec("sudo mkdir /dockerresults/" . $randomid . "/ && sudo chmod -R 777 /dockerresults/" . $randomid . "/"); //create dir for ips+nmap+aquatone scan results

        file_put_contents($queriesfile, implode( PHP_EOL, $parsed_queries) );

        exec("sudo docker run --dns 8.8.4.4 --cpu-shares 256 --rm -v dockerresults:/dockerresults -v configs:/configs/ 5631/passivequeries python3 passivequery.py -i " . $queriesfile  
            . " -a " . $apikeysfile . " -o " . $outputfile);
            
        if ( file_exists($outputfile) ) {
            $output = file_get_contents($outputfile);
        }

        ipscan::savetodb($taskid, $hostname, $output);

        $queue = explode(PHP_EOL, $input["queueid"]);

        foreach ($queue as $id) {
            dirscan::queuedone($id);
        }

        return 1;
    }

}


