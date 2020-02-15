<?php

namespace frontend\models;

use yii\db\ActiveRecord;

class Reverseip extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public static function reverseipscan($input)
    {

        $url = $input["url"];

        $url = ltrim($url, ' ');
        $url = rtrim($url, ' ');
        $url = escapeshellarg($url);

        $taskid = $input["taskid"];

        $command = "curl --insecure  https://api.hackertarget.com/reverseiplookup/?q=" . $url . "";

        exec($command, $output);

        $output = json_encode($output);

        $date_end = date("Y-m-d H-i-s");

        $reverseipscan = Tasks::find()
            ->where(['taskid' => $taskid])
            ->limit(1)
            ->one();

        $reverseipscan->reverseip_status = "Done.";
        $reverseipscan->reverseip = $output;
        $reverseipscan->date = $date_end;

        $reverseipscan->save();
        return 1;
                
    }

}


