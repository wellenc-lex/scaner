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

            exec("sudo mkdir /dockerresults/whatweb" . $randomid . " &");

            $inputurlsfile = "/dockerresults/whatweb" . $randomid . "/whatwebhttpx.txt";

            file_put_contents($inputurlsfile, $input["url"] ); 
        } else return 0; //no need to scan without supplied urls
        
        $whatweboutput = "/dockerresults/whatweb" . $randomid . "/whatweboutput.txt";

        /*exec('sudo docker run --rm -v dockerresults:/dockerresults guidelacour/whatweb \
            ./whatweb --input-file=' . $inputurlsfile . ' --aggression 3 --max-threads 1 --no-errors --wait=1 \
            --user-agent "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36" --log-brief ' . $whatweboutput . ' ');
*/

        //exec('sudo docker run --cpu-shares 128 --rm -v dockerresults:/dockerresults intrigueio/intrigue-ident --threads 10 --debug --file ' . $inputurlsfile . ' --json ' . $whatweboutput."out");

        whatweb::httpxhosts($inputurlsfile, $whatweboutput);

        /*if ( file_exists($whatweboutput) ) {
            $output = file_get_contents($whatweboutput);
        }

        if($output != ""){
            whatweb::savetodb($output);
        }*/

        if( isset($input["queueid"]) ) {
            $queues = explode(PHP_EOL, $input["queueid"]); 

            foreach($queues as $queue){
                dirscan::queuedone($queue);
            }
        }

        //whatweb::jsscan($inputurlsfile);

        //exec("sudo rm -r /dockerresults/whatweb" . $randomid . "/");

        return 1;
    }

    public function savetodb($url)
    {
        try{
            Yii::$app->db->open();

            

            Yii::$app->db->close();
            
        } catch (\yii\db\Exception $exception) {

            sleep(2000);

            whatweb::savetodb($url);
        }

        return 1;
    }

    
    public function httpxhosts($inputfile, $output)
    {
        global $randomid;

        $wordlist = $inputfile;

        $httpx = "sudo docker run --cpu-shares 256 --rm -v dockerresults:/dockerresults projectdiscovery/httpx -ports 80,443,8080,8443,8000,3000,8083,8088,8888,8880,9999,10000,4443,6443,10250,8123,8000 -rate-limit 50 -timeout 60 -retries 2 -silent -o ". $output ." -l ". $wordlist ." -json -tech-detect -title -favicon -ip ";
        
        exec($httpx);

        $hostnames = array(); //we dont need duplicates like http://goo.gl and https://goo.gl so we parse everything after scheme and validate that its unique

        if (file_exists($output)) {

            //convert json strings into one json array to decode it
            $output = str_replace("}
{", "},{", $output);

            $output = '[' . $output . ']';

            $alive = json_decode($output, true);

            rsort($alive); //rsort so https:// will be at the top and we get less invalid duplicates with http:// below

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

                    if( !in_array($currenthost, $hostnames ) ){ //if this exact host:port havent been processed already

                        $whatweb = new Whatweb();
                        $whatweb->url = $url["scheme"].$currenthost;
                        $whatweb->ip = $url["host"];
                        $whatweb->favicon = $url["favicon-mmh3"];
                        $whatweb->date = date("Y-m-d");

                        if (isset( $url["technologies"] )) $whatweb->tech = json_encode( $url["technologies"] );

                        $whatweb->save();

                        $hostnames[] = $currenthost; //we add https://google.com:443 to get rid of http://google.com because thats duplicate
                    }
                }
            } 

            Yii::$app->db->close();

        } else file_get_contents($output); //we need an error to check it out in debugger and rescan later
        
        return 1;
    }

    //searches secrets and subdomains in JS files with crawl + signatures
    public function jsscan($filename) //file with subdomains urls
    {

        $jsoutput = $filename ."jsscan";

        $jsscan = "timeout 1500 /tmp/jsubfinder.binary search --crawl -t 10 -s --sig '/tmp/.jsf_signatures.yaml' -f ". $filename ." -o ". $jsoutput;

        exec($jsscan);

        if (file_exists($jsoutput)) {
            $output = file_get_contents($jsoutput);
        } else $output="[]";

        whatweb::httpxhosts($jsoutput); //scan JSscan output file with httpx and scan those later with dirscan + whatweb again and again untill there are no new domains left

        return 1;
    }

}

