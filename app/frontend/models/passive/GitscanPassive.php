<?php

namespace frontend\models\passive;

use yii\db\ActiveRecord;

class GitscanPassive extends ActiveRecord
{
    public static function tableName()
    {
        return 'gitscanpassive';
    }

    public static function gitscan($input)
    {

        global $a;
        $a = "";
        while ($a != "Done") {
            $countnmap = "pgrep -c docker";

            exec($countnmap, $count);

            if ($count[0] < 3) {

                $url = $input["url"];
                $taskid = $input["taskid"];

                $randomid = rand(1, 100000);

                $gitscan = Tasks::find()
                    ->where(['taskid' => $taskid])
                    ->limit(1)
                    ->one();

                $token = $gitscan->token;


                $command = 'sudo /usr/bin/docker run --name=' . $randomid . ' abhartiya/tools_gitallsecrets -mergeOutput -token=' . $token . '  -thogEntropy -repoURL=' . $url . ' >/dev/null && sudo /usr/bin/docker cp ' . $randomid . ':/root/results.txt /root/gitscan' . $randomid . ' && sudo /usr/bin/docker rm ' . $randomid . '&& sudo mv /root/gitscan' . $randomid . ' /var/www/output/docker/' . $randomid . ' ';

                $command2 = "find /var/www/output/docker/ -name " . $randomid . " -delete &";

                $escaped_command = ($command);

                system("systemctl start docker");

                system($escaped_command, $gitscan_returncode);

                $output = base64_encode(file_get_contents("/var/www/output/docker/" . $randomid . ""));

                $date_end = date("Y-m-d H-i-s");

                $gitscan->gitscan_status = "Done.";
                $gitscan->gitscan = $output;
                $gitscan->date = $date_end;

                system($command2);
                $a = "Done";
                return $gitscan->save();

            } else sleep(25);
        }
    }

}


//$gitscan -> viewed = 0 & needs_to_notify = 1 if prev!=new

//date = today day

//ltrip http://github.com/

//docker run -it abhartiya/tools_gitallsecrets -token="b54732426e4a8ad9ee3aa10baf51f2d8abb6a8cb" -org="qiwi" -cloneForks

