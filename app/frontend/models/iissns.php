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

            sleep(3000);
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

        exec("/tmp/sns.binary --threads 1 --timeout 100 --nocolor --check --file /dockerresults/" . $randomid . "aquatoneinput.txt > /jsa/" . $randomid . "iis.txt ");

        if (file_exists("/jsa/" . $randomid . "iis.txt")) {
            $file = file_get_contents("/jsa/" . $randomid . "iis.txt");
        } else $file="error no file";

        $iis_output = base64_encode($file); //htmls encoded so there will be no error with inserting into db

        //exec("sudo rm -r /jsa/" . $randomid . "/");;

        jsa::savetodb($taskid, $iis_output);

        dirscan::queuedone($input["queueid"]);

        
        return 1;
    }

}


































