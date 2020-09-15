<?php

namespace frontend\models;

use yii\db\ActiveRecord;

class Gitscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public static function gitscan($input)
    {

        global $a;
        $a = "";
        while ($a != "Done") {
            $countdocker = "pgrep -c docker";

            exec($countdocker, $count);

            if ($count[0] < 5) {

                system("systemctl start docker");

                $url = $input["url"];
                $taskid = $input["taskid"];

                $randomid = rand(1, 100000);

                $gitscan = Tasks::find()
                    ->where(['taskid' => $taskid])
                    ->limit(1)
                    ->one();

                $token ="b54732426e4a8ad9ee3aa10baf51f2d8abb6a8cb"; //change later to pick up from DB

                system('sudo /usr/bin/docker run --name=' . $randomid . ' abhartiya/tools_gitallsecrets -mergeOutput -token=' .
                    $token . ' -repoURL=' . $url . ' >/dev/null && sudo /usr/bin/docker cp ' .
                    $randomid . ':/root/results.txt /root/gitscan' . $randomid . ' && sudo /usr/bin/docker rm ' .
                    $randomid . '&& sudo mv /root/gitscan' . $randomid . ' /var/www/output/docker/' . $randomid . ' ');

                $output = file_get_contents("/var/www/output/docker/" . $randomid);

                $date_end = date("Y-m-d H-i-s");

                $gitscan->gitscan_status = "Done.";
                $gitscan->gitscan = $output;
                $gitscan->date = $date_end;

                system("find /var/www/output/docker/ -name " . $randomid . " -delete &");
                $a = "Done";
                return $gitscan->save();

            } else sleep(25);
        }
    }


}



