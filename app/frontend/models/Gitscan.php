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
        //$taskid = $input["taskid"];

        $taskid = 3384;

        $task = Tasks::find()
            ->where(['taskid' => $taskid])
            ->limit(1)
            ->one();

        $token = "b54732426e4a8ad9ee3aa10baf51f2d8abb6a8cb"; //change later to pick up from DB


        $task = json_decode($task["amass"], true);

        foreach ($task as $json) {
            $addresses[] = $json["name"];
        }
        var_dump($addresses);
        return 1;






        /*system('sudo /usr/bin/docker run --name=' . $randomid . ' abhartiya/tools_gitallsecrets -mergeOutput -token=' .
               $token . ' -repoURL=' . $url . ' >/dev/null && sudo /usr/bin/docker cp ' .
            $randomid . ':/root/results.txt /root/gitscan' . $randomid . ' && sudo /usr/bin/docker rm ' .
            $randomid . '&& sudo mv /root/gitscan' . $randomid . ' /var/www/output/docker/' . $randomid . ' ');

        $output = file_get_contents("/var/www/output/docker/" . $randomid);
*/

        $task->gitscan_status = "Done.";
        //$gitscan->gitscan = $output;
        $task->date = date("Y-m-d H-i-s");

                //system("find /var/www/output/docker/ -name " . $randomid . " -delete &");
        $task->save();

        return 1;
    }
}



