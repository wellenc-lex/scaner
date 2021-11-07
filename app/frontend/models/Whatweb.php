<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;
use frontend\models\Queue;

ini_set('max_execution_time', 0);

class Whatweb extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function savetodb($output)
    {
        try{
            Yii::$app->db->open();

            $task = new Tasks();

            $task->whatweb = $output;
            $task->hidden = "1";
            $task->notify_instrument = "5";
            $task->status = "Done.";
            $task->date = date("Y-m-d H-i-s");

            $task->save();

            Yii::$app->db->close();
            
        } catch (\yii\db\Exception $exception) {

            sleep(2000);

            whatweb::savetodb($taskid, $output);
        }

        return 1;
    }

    public function httpxhosts()
    {
        //httpx -o  добавляет результаты или заменяет?

        $randomid = 1;

        $wordlist = "/dockerresults/" . $randomid . "whatwebhosts.txt";
        $output = "/dockerresults/" . $randomid . "whatwebhttpx.txt";

        $allresults = Tasks::find()
            ->select(['tasks.taskid','tasks.amass', ])
            ->andWhere(['not', ['tasks.amass' => null]])
            ->all();

        Yii::$app->db->close();

        $urls = array();

        foreach ($allresults as $results) {

            $amassoutput = json_decode($results->amass, true);

            //Get vhost names from amass scan & wordlist file + use only unique ones
            foreach ($amassoutput as $amass) {

                $urls[] = $amass["name"];
            }
        }

        file_put_contents($wordlist, implode( PHP_EOL, $urls) );

        $httpx = "sudo docker run --cpu-shares 1024 --rm -v dockerresults:/dockerresults projectdiscovery/httpx -exclude-cdn -ports 80,443,8080,8443,8000,3000,8888,8880,9999,10000,4443,6443,10250 -t 3 -rl 5 -maxr 4 -timeout 20 -retries 5 -silent -o ". $output ." -l ". $wordlist ."";
            
        exec($httpx);

        Yii::$app->db->open();
        $hostnames = array(); //we dont need duplicates like http://goo.gl and https://goo.gl so we parse everything after scheme and validate that its unique

            if (file_exists($output)) {
                $alive = file_get_contents($output);
                $alive = explode(PHP_EOL,$alive);

                $alive = array_unique($alive); 

                rsort($alive); //rsort so https:// will be at the top and we get less invalid duplicates below

                foreach($alive as $url) {

                    if($url != "" ){ //check that domain corresponds to amass domain. (in case gau gave us wrong info)

                        $currenthost = dirscan::ParseHostname($url).dirscan::ParsePort($url);

                        if( !in_array($currenthost, $hostnames ) ){

                            $queue = new Queue();
                            $queue->dirscanUrl = dirscan::ParseScheme($url).$currenthost;
                            $queue->instrument = 5; //whatweb
                            $queue->save();
                                
                            $hostnames[] = $currenthost;
                        }
                    }
                }
            }

        Yii::$app->db->close();

        return 1;
    }

    public static function whatweb($input)
    {
        
        $randomid = rand(1,100000000);

        $inputurlsfile = "/dockerresults/" . $randomid . "whatwebhttpx.txt";
        $whatweboutput = "/dockerresults/" . $randomid . "whatweboutput.txt";

        if( $input["url"] != "") file_put_contents($inputurlsfile, $input["url"] ); else return 0; //no need to scan without supplied urls
//--tty --interactive
        exec('sudo docker run --rm -v dockerresults:/dockerresults guidelacour/whatweb \
            ./whatweb --input-file=' . $inputurlsfile . ' --aggression 3 --max-threads 2 --wait=1 \
            --user-agent "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36" > ' . $whatweboutput . ' ');

        if ( file_exists($whatweboutput) ) {
            $output = file_get_contents($whatweboutput);
        }

        if($output != ""){
            whatweb::savetodb($output);
        }

        if( isset($input["queueid"]) ) {
            $queues = explode(PHP_EOL, $input["queueid"]); 

            foreach($queues as $queue){
                dirscan::queuedone($queue);
            }
        }

        //exec("sudo rm -r /dockerresults/" . $randomid . "whatweb*");

        return 3;
    }

}

