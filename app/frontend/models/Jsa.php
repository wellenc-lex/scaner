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

    public static function savetodb($taskid, $output)
    {
        if( $output == "ZXJyb3Igbm8gZmlsZQ==" && !empty($output) && $output!=[] ) {
            return 2;
        }

        try{
            $jsa = new Tasks();
            $jsa->dirscan_status = "Done.";
            $jsa->notify_instrument = $task->notify_instrument."9";
            $jsa->js = $output;
            $jsa->date = date("Y-m-d H-i-s");

            $jsa->save();
           
        } catch (\yii\db\Exception $exception) {

            sleep(5000);
            $jsa = new Tasks();
            $jsa->dirscan_status = "Done.";
            $jsa->notify_instrument = $task->notify_instrument."9";
            $jsa->js = $output;
            $jsa->date = date("Y-m-d H-i-s");

            $jsa->save();
            
            return $exception.$output;
        }

        return 1;
    }

    public static function jsa($input)
    {
        $randomid = (int) $input["randomid"];

        exec("sudo docker run --dns=8.8.8.8 --rm --privileged=true --ulimit nofile=1048576:1048576 --cpu-shares 256 -v /dockerresults/:/dockerresults -v /jsa/:/jsa 5631/jsa /dockerresults/" . $randomid . "aquatoneinput.txt /jsa/" . $randomid . " >> /dockerresults/jsa.output 2>&1");

        if (file_exists("/jsa/" . $randomid . "/out.txt")) {
            $trufflehog = file_get_contents("/jsa/" . $randomid . "/out.txt");
        } else $trufflehog="error no file";

        $trufflehog = str_replace('-', '/', $trufflehog);
        $trufflehog = str_replace('~', '/', $trufflehog);

        $output = base64_encode($trufflehog); //htmls encoded so there will be no error with inserting into db

        //exec("sudo rm /jsa/" . $randomid . "/out.txt");;

        jsa::savetodb($taskid, $output);

        dirscan::queuedone($input["queueid"]);

        return 1;
    }

}


































