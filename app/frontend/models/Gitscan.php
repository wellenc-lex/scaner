<?php

namespace frontend\models;

set_time_limit(0);
use Yii;
use yii\db\ActiveRecord;
use frontend\models\PassiveScan;
use frontend\models\ToolsAmount;

class Gitscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function PassiveGitscan($taskid)
    {
        sleep(5); //so the amass passive results are 100% updated in db

        $task = PassiveScan::find()
            ->where(['PassiveScanid' => $taskid])
            ->limit(1)
            ->one();

        Yii::$app->db->close(); //closes DB connection so no timeout occur

        $amass = json_decode($task["amass_new"], true); 

        $done = array();

        foreach ($amass as $json) {
            $addresses[] = $json["name"];
        }

        array_unique($addresses);

        foreach ($addresses as $address) {
            if(preg_match("/(www.|static|sctp)/i", $address) === 1 ){
                continue;
            } else $done[] = "\"".$address."\"";
        }

        array_unique($done);

        file_put_contents("/dockerresults/" . $taskid . "amassGitscanPassive.txt", implode(PHP_EOL, $done));

        exec("sudo docker run --rm -v configs:/configs/ -v dockerresults:/dockerresults 5631/githound --dig-commits --dig-files --subdomain-file /dockerresults/" . $taskid . "amassGitscanPassive.txt --config-file /configs/githoundconfig1.yml > /dockerresults/" . $taskid . "amassGitOut.txt");

        $gitout = file_get_contents("/dockerresults/" . $taskid . "amassGitOut.txt");

        Yii::$app->db->open();

        $PassiveScan = PassiveScan::find()
            ->where(['PassiveScanid' => $taskid])
            ->limit(1)
            ->one();

        $PassiveScan->gitscan = base64_encode($gitout);

        return $PassiveScan->save();
    }

    public static function gitscan($input)
    {

        //gitscan rm /dockerresults/" . $randomid . "amass*

        $taskid = (int) $input["taskid"];

        exec("sudo chmod -R 777 /dockerresults/ && sudo chmod -R 777 /dockerresults");

        if ($input["passive"] == 1){

            $wayback_result = gitscan::PassiveGitscan($taskid);

            return 2;

        } elseif ($input["active"] == 1){

            $task = Tasks::find()
                ->where(['taskid' => $taskid])
                ->limit(1)
                ->one();

            Yii::$app->db->close(); //closes DB connection so no timeout occur when $task->save()

            $task = json_decode($task["amass"], true); 

            $done = array();

            foreach ($task as $json) {
                $addresses[] = $json["name"];
            }

            array_unique($addresses);

            foreach ($addresses as $address) {
                if(preg_match("/(www.|static|sctp)/i", $address) === 1 ){
                    continue;
                } else $done[] = "\"".$address."\"";
            }

            array_unique($done);

            file_put_contents("/dockerresults/" . $taskid . "amassGitscanActive.txt", implode(PHP_EOL, $done));

            exec("sudo docker run --rm -v configs:/configs/ -v dockerresults:/dockerresults 5631/githound --dig-commits --dig-files --subdomain-file /dockerresults/" . $taskid . "amassGitscanActive.txt --config-file /configs/githoundconfig1.yml > /dockerresults/" . $taskid . "amassGitOutActive.txt");

            $gitout = file_get_contents("/dockerresults/" . $taskid . "amassGitOutActive.txt");

            Yii::$app->db->open();

            $decrement = ToolsAmount::find()
                ->where(['id' => 1])
                ->one();

            $value = $decrement->gitscan;
            
            if ($value <= 1) {
                $value=0;
            } else $value = $value-1;

            $decrement->gitscan=$value;
            $decrement->save();
            

            $task = Tasks::find()
                ->where(['taskid' => $taskid])
                ->limit(1)
                ->one();

            $task->gitscan = base64_encode($gitout);
            $task->date = date("Y-m-d H-i-s");
            $task->gitscan_status = "Done.";
            $task->save();

            exec("sudo rm /dockerresults/" . $taskid . "*");

            return 1;
        }    
    }

    public static function gittofile()
    {

        exec("sudo chmod 777 /dockerresults/ -R && sudo chmod 777 /dockerresults -R");

            $tasks = Tasks::find()
                ->where(['>','taskid','1']) //gitscan results not null check in Db
                ->andWhere(['!=','amass','[]'])
                ->andWhere(['IS NOT', 'amass', null])
                ->all();

            $done = array();

            foreach ($tasks as $task) {
                $task = json_decode($task["amass"], true); 

                if (!empty($task)){
                    foreach ($task as $json) {
                        $addresses[] = $json["name"];
                    }

                    $addresses = array_unique($addresses);

                    foreach ($addresses as $address) {
                        if(preg_match("/(^api|autodiscover|autoconfig|contact|img|cdn|static|sctp|www|^ns[\d\-\_\.]*|\_dc\-mx|^url[\d\-\_\.]*|^docs\.|academy|links|blog|help|status|(.*mail.*\.[\w\d\-\_]*\.[\w\d\-\_]*)|developers|smtp)/i", $address) === 1 ){

                            continue;
                        } else $done[] = "\"".$address."\"";
                    }
                }
            }
            
            $done = array_unique($done);

            file_put_contents("/dockerresults/gitdomains.txt", implode(PHP_EOL, $done));
            return 1;
         
    }

}



