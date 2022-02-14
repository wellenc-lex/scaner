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

            $fileaquatone = str_replace('screenshotPath":"screenshots/', 'screenshotPath":"../../screenshots/'.$taskid.'/', $fileaquatone);

            $fileaquatone = str_replace('<img src="screenshots', '<img src="../../screenshots/'.$taskid.'/', $fileaquatone);

            $fileaquatone = str_replace('<a href="screenshots', '<a href="../../screenshots/'.$taskid.'/', $fileaquatone);

            $fileaquatone = str_replace('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
    integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">', '<link rel="stylesheet" href="https://bootswatch.com/4/darkly/bootstrap.min.css" crossorigin="anonymous">', $fileaquatone);

            $fileaquatone = str_replace('</html>>', '</html>', $fileaquatone);

            $fileaquatone = str_replace('</ol>d', '</ol>', $fileaquatone);

            $fileaquatone = str_replace('.carousel-item {
      color: #383d41;
      text-align: center;
    }', '.carousel-item {
                color: white !important;
                text-align: center;
                font-size: initial;
            }

            .btn {
                color: #00b5ff !important;
            }', $fileaquatone);


            $fileaquatone = str_replace('.btn-outline-secondary {
    color: #444;
    border-color: #444;', 
    '.btn-outline-secondary {
    color: white;
    border-color: white;', $fileaquatone);

                $fileaquatone = str_replace('.carousel-indicators li {
      background-color: #6c757d;
    }', '.carousel-indicators li {
      background-color: white;
    }

    .pre {
        display: block;
        font-size: 87.5%;
        color: black;
    }', $fileaquatone);

            $fileaquatone = str_replace('<div class="card-footer">', '<div class="card-footer">
            <label style="text-align: right; float: right; margin-right: 4%">
                <input type="checkbox" name="dirscan"> <b>Dirscan</b>
            </label>

            <label style="text-align: right; float: right; margin-right: 2%">
                <input type="checkbox" name="nmap"> <b>Nmap</b>
            </label>', $fileaquatone);

            $fileaquatone = str_replace('<td>', '<td style="word-wrap: break-word; max-width: 100px;">', $fileaquatone);

            $fileaquatone = str_replace('<a href="" target="_blank" class="btn btn-primary view-raw-response-button">View Raw Response</a>', '', $fileaquatone);

            $fileaquatone = str_replace('<a href="" target="_blank" class="btn btn-primary view-raw-headers-button">View Raw Headers</a>', '', $fileaquatone);

            $fileaquatone = str_replace('tabindex="-1" role="dialog" aria-hidden="true"', '', $fileaquatone);

            /** Copy the screenshots from the volume to folder in order to be accessible from nginx **/
            //$movescreenshots = "sudo chmod -R 777 /screenshots/" . $taskid . "/screenshots && cp -R --remove-destination /screenshots/" . $taskid . "/screenshots /var/www/app/frontend/web/ && sudo rm -r /screenshots/" . $taskid . "/ && sudo rm -r /dockerresults/" . $taskid . "";

            $movescreenshots = "sudo chmod -R 777 /screenshots/" . $taskid . "/screenshots && ln -s /screenshots/" . $taskid . "/screenshots /var/www/app/frontend/web/screenshots/" . $taskid . " ";

            //&& sudo chmod -R 777 /var/www/app/frontend/web/screenshots/" . $taskid . "

            $fileaquatone = preg_replace('/\<footer.*\<\/footer\>/', '', $fileaquatone, -1);

            $fileaquatone = preg_replace('/\<nav class.*\<\/nav\>/', '', $fileaquatone, -1);

            exec($movescreenshots);

        } else $fileaquatone="No screenshots";

        return $fileaquatone;
    }

    public function aquatone($taskid, $filename, $queues)
    {
        //exec("sudo mkdir /screenshots/" . $taskid . "/");

        //for amass results we need to scan other ports
        if ( preg_match("/(\w\d\_\-)*\.json/i", $filename) !== 0 ) {
            $command = "cat ". $filename ." | sudo docker run --cpu-shares 256 -v screenshots:/screenshots -v dockerresults:/dockerresults --rm -i 5631/aquatone2 -http-timeout 30000 -threads 5 -scan-timeout 10000 -ports xlarge -http-timeout 30000 -screenshot-timeout 40000  -out /screenshots/" . $taskid . " -save-body false -similarity 0.85 -screenshot-delay 5000 ";
        }
//-chrome-path /usr/bin/chromium-browser
        

        //-follow-redirect


        //for nmap results
        if ( preg_match("/(\w\d\_\-)*\.xml/i", $filename) !== 0 ) {
            $command = "cat " . $filename . " | sudo docker run --cpu-shares 256 -v screenshots:/screenshots -v dockerresults:/dockerresults --rm -i 5631/aquatone2 -http-timeout 15000 -threads 50 -scan-timeout 10000 -screenshot-timeout 45000 -out /screenshots/" . $taskid . " -save-body false -nmap -similarity 0.85 -screenshot-delay 5000 ";

            //echo($command);
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

        //eyewitness -t 10 -x " . $filename . " -d /screenshots/" . $taskid . " –createtargets /screenshots/targets.txt --no-dns  --all-protocols --no-prompt --timeout 20 --max-retries 3 --jitter 1 --results 50 --no-prompt --prepend-https
        //gowitness --db-path /screenshots/gowitness.sqlite3 --screenshot-path /screenshots/" . $taskid . " --timeout 20 
    }

}





















