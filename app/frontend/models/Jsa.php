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
        if( $jsa_output == "ZXJyb3Igbm8gZmlsZQ==" && !empty($jsa_output) && $jsa_output!=[] ) {
            return 2;
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

            sleep(3000);
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

        $randomid = (int) $input["randomid"];

        exec("sudo docker run --cpu-shares 128 -v dockerresults:/dockerresults -v jsa:/jsa 5631/jsa /dockerresults/" . $randomid . "aquatoneinput.txt /jsa/" . $randomid . " ");

        if (file_exists("/jsa/" . $randomid . "/out.txt")) {
            $trufflehog = file_get_contents("/jsa/" . $randomid . "/out.txt");
        } else $trufflehog="error no file";

        $jsa_output = base64_encode($trufflehog); //htmls encoded so there will be no error with inserting into db

        //exec("sudo rm -r /jsa/" . $randomid . "/");;

        jsa::savetodb($taskid, $hostname, $jsa_output);

        dirscan::queuedone($input["queueid"]);

        
        return 1;
    }

}


































