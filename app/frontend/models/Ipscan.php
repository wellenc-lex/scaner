<?php

namespace frontend\models;

use yii\db\ActiveRecord;

class Ipscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public static function ipscan($input)
    {

        $url = $input["url"];
        $taskid = $input["taskid"];

        $url = str_replace("http://", "", $url);
        $url = str_replace("https://", "", $url);
        $url = str_replace(",", " ", $url);
        $url = str_replace("\r", " ", $url);
        $url = str_replace("\n", " ", $url);
        $url = escapeshellarg($url);

        $randomid = rand(1, 100000);

        $command = "/usr/bin/python2.7 /var/www/soft/ipscan/censyssearch.py -s /var/www/output/ipscan/scan" . $randomid . ".json -q " . $url . " ";

        $command2 = "sudo /usr/bin/find /var/www/output/ipscan/ -name 'scan$randomid.*' -delete &";

        $escaped_command = ($command);

        system($escaped_command, $ips_returncode);

        $output = file_get_contents("/var/www/output/ipscan/scan" . $randomid . ".json");

        $date_end = date("Y-m-d H-i-s");

        $ips = Tasks::find()
            ->where(['taskid' => $taskid])
            ->limit(1)
            ->one();

        $ips->ips_status = "Done.";
        $ips->ips = $output;
        $ips->date = $date_end;

        system($command2);

        return $ips->save();
    }

}


