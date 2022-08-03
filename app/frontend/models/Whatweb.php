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
        return 'whatweb';
    }

    public static function whatweb($input)
    {
        global $randomid;

        if( $input["url"] != "") {

            $randomid = rand(1,1000000000000);

            exec("sudo mkdir /dockerresults/whatweb" . $randomid . " && sudo chmod -R 777 /dockerresults/whatweb" . $randomid . " ");

            $inputurlsfile = "/dockerresults/" . $randomid . "aquatoneinput.txt";

            file_put_contents($inputurlsfile, $input["url"] ); 
        } else return 0; //no need to scan without supplied urls
        
        $whatweboutput = "/dockerresults/whatweb" . $randomid . "/whatweboutput.txt";

        whatweb::httpxhosts($inputurlsfile, $whatweboutput);

        if( isset($input["queueid"]) ) {
            $queues = explode(PHP_EOL, $input["queueid"]); 

            foreach($queues as $queue){
                dirscan::queuedone($queue);
            }
        }


        //exec("sudo rm -r /dockerresults/whatweb" . $randomid . "/");

        return 1;
    }
    
    public static function httpxhosts($inputfile, $output)
    {
        global $randomid;

        $wordlist = $inputfile;

        $httpxresponsesdir = "/httpxresponses/" . $randomid. "/";

        $httpx = "sudo docker run --cpu-shares 256 --rm -v dockerresults:/dockerresults projectdiscovery/httpx -ports 80,443,8080,8443,8000,3000,8083,8088,8888,8880,9999,10000,4443,6443,10250,8123,8000,2181,9092 -rate-limit 10 -timeout 60 -retries 3 -silent -o ". $output ." -l ". $wordlist ." -json -tech-detect -title -favicon -ip -sr -srd ". $httpxresponsesdir;
        
        exec($httpx);

        $hostnames = array(); //we dont need duplicates like http://goo.gl and https://goo.gl so we parse everything after scheme and validate that its unique

        if (file_exists($output)) {

            //convert json strings into one json array to decode it
            $output = str_replace("}
{", "},{", $output);

            $output = '[' . $output . ']';

            $alive = json_decode($output, true);

            if( !empty($alive) ){
                rsort($alive); //rsort so https:// will be at the top and we get less duplicates with http:// below

                Yii::$app->db->open();

                foreach($alive as $url) {

                    if($url["input"] != "" && strpos($url["input"], $maindomain) !== false ){ //check that domain corresponds to amass domain. (in case gau gave us wrong info)

                        $scheme = $url["scheme"];
                        $port = ":".$url["port"]; 

                        if( ($scheme==="http://" && $port===":443") || ($scheme==="https://" && $port===":80")){
                            continue; //scanning https port with http scheme is pointless so we get to the next host
                        }

                        if( $port===":80" || $port===":443"){
                            $currenthost = $url["input"];
                        } else $currenthost = $url["input"].$port;

                        if( !in_array( $currenthost, $hostnames ) ){ //if this exact host:port havent been processed already

                            whatweb::savetodb($url);

                            $hostnames[] = $currenthost; //we add https://google.com to get rid of http://google.com:443 because thats duplicate
                        }
                    }
                } 
            }

            Yii::$app->db->close();

        } //else file_get_contents($output); //we need an error to check it out in debugger and rescan later
        
        return 1;
    }


    public static function savetodb($url)
    {
        do{
            try{
                $tryAgain = false;
                $whatweb = new Whatweb();
                $whatweb->url = $url["scheme"].$currenthost;
                $whatweb->ip = $url["host"];
                $whatweb->favicon = $url["favicon-mmh3"];
                $whatweb->date = date("Y-m-d");

                if (isset( $url["technologies"] )) {
                    $whatweb->tech = json_encode( $url["technologies"] );

                    if (preg_match('/Basic/', $whatweb->tech) === 1) {
                        $queue = new Queue();
                        $queue->dirscanUrl = $whatweb->url;
                        $queue->instrument = 11; //whatweb
                        $queue->save();
                    }
                }

                $whatweb->save();
            } catch (\yii\db\Exception $exception) {
                $tryAgain = true;
                sleep(1000);

                whatweb::savetodb($url);
            }
        } while($tryAgain);
    }
}

