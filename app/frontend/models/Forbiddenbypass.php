<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;
use frontend\models\Queue;

ini_set('max_execution_time', 0);

class Forbiddenbypass extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function savetodb($output)
    {
        try{
            Yii::$app->db->open();

            $task = new Tasks();

            $task->forbiddenbypass = $output;
            $task->notify_instrument = "10";
            $task->status = "Done.";
            $task->date = date("Y-m-d H-i-s");

            $task->save();

            Yii::$app->db->close();
            
        } catch (\yii\db\Exception $exception) {

            sleep(2000);

            Forbiddenbypass::savetodb($taskid, $output);
        }

        return 1;
    }

    public static function main($input)
    {
        $randomid = rand(1,100000000);

        $inputurlsfile = "/dockerresults/" . $randomid . "403input.txt";
        $output = "/dockerresults/" . $randomid . "403output.txt";

        if( $input["url"] != "") file_put_contents($inputurlsfile, $input["url"] ); else return 0; //no need to scan without supplied urls

        exec('sudo docker run --rm -v dockerresults:/dockerresults 5631/403bypass /bin/bash -c "cat ' . $inputurlsfile  . ' | ./403bypass.sh > ' . $output . ' "');

        if ( file_exists($output) ) {
            $results = file_get_contents($output);

            var_dump($results);

            $results = preg_replace('/.*inside substitute pattern.*/', '', $results);

            var_dump($results);
            print_r($results);

            //remove unescaped newline inside substitute pattern
        }

        if($results != ""){
            Forbiddenbypass::savetodb($results);
        }

        if( isset($input["queueid"]) ) {
            $queues = explode(PHP_EOL, $input["queueid"]); 

            foreach($queues as $queue){
                dirscan::queuedone($queue);
            }
        }

        //exec("sudo rm /dockerresults/" . $randomid . "403*");

        return 1;
    }

}

