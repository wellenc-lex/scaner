<?php

namespace frontend\models;

use Yii;
use frontend\models\Queue;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;

require_once 'Dirscan.php';

ini_set('max_execution_time', 0);

class Aquatone extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function saveToDB($taskid, $aquatoneoutput)
    {
        if($aquatoneoutput != "[]" && $aquatoneoutput != 'No screenshots'){

            try{
                Yii::$app->db->open();

                $task = Tasks::find()
                    ->where(['taskid' => $taskid])
                    ->limit(1)
                    ->one();

                if(!empty($task)){ //if querry exists in db

                    $task->aquatone = $aquatoneoutput;
                    $task->aquatone_status = 'Done.';
                    $task->nmap_status = "Done.";
                    $task->status = 'Done.';
                    $task->date = date("Y-m-d H-i-s");

                    $task->save(); 

                } else {
                    $task = new Tasks();
                    
                    $task->aquatone = $aquatoneoutput;
                    $task->aquatone_status = 'Done.';
                    $task->nmap_status = "Done.";
                    $task->notify_instrument = "1";
                    $task->status = 'Done.';
                    $task->date = date("Y-m-d H-i-s");

                    $task->save(); 

                    $taskid = $task->taskid;
                }

            } catch (\yii\db\Exception $exception) {
                var_dump($exception);
                sleep(360);

                aquatone::saveToDB($taskid, $aquatoneoutput);
            }

            return Yii::$app->db->close();
        }
    }
    
    public function readaquatone($taskid)
    {

        if (file_exists("/screenshots/" . $taskid . "/aquatone_report.html")) {
            $fileaquatone = file_get_contents("/screenshots/" . $taskid . "/aquatone_report.html");

            $fileaquatone = str_replace('<img src="screenshots', '<img src="../../screenshots/'.$taskid.'/', $fileaquatone);

            $fileaquatone = str_replace('<a href="screenshots', '<a href="../../screenshots/'.$taskid.'/', $fileaquatone);

            $fileaquatone = str_replace('<link rel="stylesheet" href="https://bootswatch.com/4/darkly/bootstrap.min.css" integrity="sha384-RVGPQcy+W2jAbpqAb6ccq2OfPpkoXhrYRMFFD3JPdu3MDyeRvKPII9C82K13lxn4" crossorigin="anonymous">', '<link rel="stylesheet" href="https://bootswatch.com/3/darkly/bootstrap.min.css">', $fileaquatone);

            $fileaquatone = str_replace('</html>>', '</html>', $fileaquatone);

            $fileaquatone = str_replace('.cluster {
                border-bottom: 1px solid rgb(68, 68, 68);
                padding: 30px 20px 20px 20px;
                overflow-x: auto;
                white-space: nowrap;
            }', '.cluster {
                border-bottom: 1px solid rgb(68, 68, 68);
                //box-shadow: none !important;
                padding: 30px 20px 20px 20px;
                overflow-x: auto;
                white-space: nowrap;
            }', $fileaquatone);

            $fileaquatone = str_replace('.cluster:nth-child(even) {
                background-color: rgba(0, 0, 0, 0.075);
                box-shadow: inset 0px 6px 8px rgb(24, 24, 24);
            }', '.cluster:nth-child(even) {
                border-bottom: 1px solid rgb(68, 68, 68);
                padding: 30px 20px 20px 20px;
                overflow-x: auto;
                white-space: nowrap;
                //box-shadow: inset 0px 6px 8px rgb(24, 24, 24);
                box-shadow: none !important;
            }', $fileaquatone);

            preg_replace("/<footer>.*<\/footer>/s", "", $fileaquatone);

            $fileaquatone = str_replace('<div class="card-footer text-muted">', '<div class="card-footer text-muted">
            <label style="text-align: right; float: right; margin-right: 4%">
                <input type="checkbox" name="dirscan"> <b>Dirscan</b>
            </label>

            <label style="text-align: right; float: right; margin-right: 2%">
                <input type="checkbox" name="nmap"> <b>Nmap</b>
            </label>', $fileaquatone);

            $fileaquatone = str_replace('<td>', '<td style="word-wrap: break-word; max-width: 100px;">', $fileaquatone);

            /** Copy the screenshots from the volume to folder in order to be accessible from nginx **/
            //$movescreenshots = "sudo chmod -R 777 /screenshots/" . $taskid . "/screenshots && cp -R --remove-destination /screenshots/" . $taskid . "/screenshots /var/www/app/frontend/web/ && sudo rm -r /screenshots/" . $taskid . "/ && sudo rm -r /dockerresults/" . $taskid . "";


            $movescreenshots = "sudo chmod -R 777 /screenshots/" . $taskid . "/screenshots && ln -s /screenshots/" . $taskid . "/screenshots /var/www/app/frontend/web/screenshots/" . $taskid . " ";

            //&& sudo chmod -R 777 /var/www/app/frontend/web/screenshots/" . $taskid . "

            $fileaquatone = preg_replace('/\<footer\>(.)*\<\/footer\>/', '', $fileaquatone, -1);

            exec($movescreenshots);

        } else $fileaquatone="No screenshots";

        return $fileaquatone;
    }

    public function aquatone($taskid, $filename, $queues)
    {
        exec("sudo mkdir /screenshots/" . $taskid . "/");
        exec("sudo chmod 777 -R  /screenshots/" . $taskid . "/");

        //for amass results we need to scan other ports
        if ( preg_match("/(\w\d\_\-)*\.json/i", $filename) !== 0 ) {
            $command = "cat ". $filename ." | sudo docker run -v screenshots:/screenshots -v dockerresults:/dockerresults --rm -i 5631/aquatone -http-timeout 20000 -threads 1 -scan-timeout 5000 -ports xlarge -http-timeout 30000 -screenshot-timeout 60000 -chrome-path /usr/bin/chromium-browser -out /screenshots/" . $taskid . " -save-body false";
        }

        //for nmap results
        if ( preg_match("/(\w\d\_\-)*\.xml/i", $filename) !== 0 ) {
            $command = "cat " . $filename . " | sudo docker run -v screenshots:/screenshots -v dockerresults:/dockerresults --rm -i 5631/aquatone -http-timeout 20000 -threads 1 -scan-timeout 5000 -http-timeout 30000 -screenshot-timeout 60000 -chrome-path /usr/bin/chromium-browser -out /screenshots/" . $taskid . " -save-body false -nmap";
        }

        exec($command);

        exec("sudo mkdir /var/www/app/frontend/web/screenshots &");

        $aquatoneoutput = aquatone::readaquatone($taskid);

        aquatone::saveToDB($taskid, $aquatoneoutput);

        if( $queues != "" ) {

            $queues = explode(PHP_EOL, $input["queues"]); 

            foreach($queues as $queue){
                dirscan::queuedone($queue);
            }
        }

        return 1;
    }

}





















