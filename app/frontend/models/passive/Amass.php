<?php

namespace frontend\models\passive;

use Yii;
use frontend\models\Queue;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;
use frontend\models\PassiveScan;

class Amass extends ActiveRecord
{

    /**
     * 0 == no diffs between subdomains
     * 1 == previous != new information, needs diff.
    */

    public static function amassscan($input)
    {
        $changes = 0;
   
        $url = $input["url"];     
        $scanid = $input["scanid"];

        $url = strtolower($url);
        $url = dirscan::ParseHostname($url);
        $url = str_replace("www.", "", $url);
        $url = htmlspecialchars($url);

        $randomid = rand(10000, 10000000000);

        $enumoutput = "/dockerresults/" . $randomid . "amass.json";

        $amassconfig = "/configs/amass/amass". rand(1,25). ".ini";

        if( !file_exists($amassconfig) ){
            $amassconfig = "/configs/amass/amass1.ini.example";
        }

        $command = "sudo sudo docker run --net=host --cpu-shares 256 --rm -v configs:/configs/ -v dockerresults:/dockerresults caffix/amass enum -w /configs/amass/amasswordlistALL2.txt -d " . escapeshellarg($url) . " -json " . $enumoutput . " -active -brute -ip -timeout 2200 -config ".$amassconfig;

        exec($command);

        if ( file_exists($enumoutput) ) {
            $fileamass = file_get_contents($enumoutput);
        } else {
            sleep(1000);
            exec($command);

            if ( file_exists($enumoutput) ) {
                $fileamass = file_get_contents($enumoutput);
            }
        }

        $fileamass = str_replace("}
{\"Timestamp\"", "},{\"Timestamp\"", $fileamass);

        $fileamass = str_replace("} {", "},{", $fileamass);

        $fileamass = str_replace("}
{", "},{", $fileamass);

        $fileamass = str_replace("}
{\"name\"", "},{\"name\"", $fileamass);

        $amassoutput = '[' . $fileamass . ']';

        if($amassoutput!= "" && !empty($amassoutput) ){

            $amassoutput = json_decode($amassoutput, true);

            if ( !empty($amassoutput) && $amassoutput != NULL ){

                $maindomain = $amassoutput[0]["domain"];

                //Get vhost names from amass scan & wordlist file + use only unique ones
                foreach ($amassoutput as $amass) {

                    $name = $amass["name"];

                    if (strpos($name, 'https://xn--') === false) {

                        $NEWsubdomains[] = $name; // full hostname for Host: header
                    } 
                }
            }
        }

        $amass = PassiveScan::find()
            ->where(['PassiveScanid' => $scanid])
            ->limit(1)
            ->one();

        if ($amass->amass_new == "") {
            $amass->amass_new = json_encode($NEWsubdomains);

            $amass->save();

            return 0; // no changes between scans

        } elseif ($amass->amass_new != "") { //latest scan info in DB

            if ($NEWsubdomains === $amass->amass_new) {
                $changes =  0; // no changes between scans
            } else {

                $OLDsubdomains = json_decode($amass->amass_new);

                $changes =  1; // check changes between scans

                $amass->amass_previous = json_encode($OLDsubdomains);
                $amass->amass_new = json_encode($NEWsubdomains); 
                $amass->save();

                if( !empty($NEWsubdomains) && !empty($OLDsubdomains)) {
                    $diff = array_diff( $NEWsubdomains, $OLDsubdomains ); // only new subdomains in the list

                    amass::httpxhosts( array_unique($diff), $scanid, $randomid );
                }
            }
        }

        return $changes;
    }

    public static function bannedwords($in)
    {
        if (preg_match("/dev|stage|test|proxy|stg|int|adm|uat/i", $in) === 1) {
            return 0; //if its used for internal or develop purposes - scan anyway
        } else { 
            return preg_match("/sentry|support|^ws|wiki|status|socket|docs|url(\d)*/i", $in);
        }
    }

    public static function tableName()
    {
        return 'passive_scan';
    }

    public static function httpxhosts($vhostslist, $taskid, $randomid)
    {
        global $maindomain;

        $wordlist = "/dockerresults/" . $randomid . "hosts.txt";
        $output = "/dockerresults/" . $randomid . "httpx.txt";
        $httpxresponsesdir = "/httpxresponses/" . $randomid. "/";
        
        file_put_contents($wordlist, implode( PHP_EOL, $vhostslist) );

        //--net=container:vpn1
        $httpx = "sudo docker run --cpu-shares 512 --rm -v dockerresults:/dockerresults projectdiscovery/httpx -ports 80,443,8080,8443,8000,3000,8083,8088,8888,8880,9999,10000,4443,6443,10250,8123,8000,2181,9092 -rate-limit 15 -timeout 50 -threads 10  -retries 3 -silent -o ". $output ." -l ". $wordlist ." -json -tech-detect -title -favicon -ip -sr -srd ". $httpxresponsesdir;
        
        exec($httpx);

        $hostnames = array(); //we dont need duplicates like http://goo.gl and https://goo.gl so we parse everything after scheme and validate that its unique

        if (file_exists($output) && filesize($output) != 0) {

            $output = file_get_contents($output);

            //convert json strings into one json array to decode it
            $output = str_replace("}
{", "},{", $output);

            $output = '[' . $output . ']';

            $alive = json_decode($output, true);

            if ( !empty($alive) ){

                rsort($alive); //rsort so https:// will be at the top and we get less invalid duplicates with http:// below

                Yii::$app->db->open();

                foreach($alive as $url) {

                    if($url["input"] != "" && strpos($url["input"], $maindomain) !== false ){ //check that domain corresponds to amass domain. (in case gau gave us wrong info)

                        $scheme = $url["scheme"]."://";
                        $port = ":".$url["port"]; 

                        if( ($scheme==="http://" && $port===":443") || ($scheme==="https://" && $port===":80")){
                            continue; //scanning https port with http scheme is pointless so we get to the next host
                        }

                        if( $port===":80" || $port===":443"){
                            $currenthost = $url["input"];
                        } else $currenthost = $url["input"].$port;

                        if( !in_array($currenthost, $hostnames ) ){ //if this exact host:port havent been processed already

                            if( amass::bannedwords($currenthost) === 0 ){ //we dont need to ffuf hosts like jira,zendesk,etc - low chances of juicy fruits?

                                $queue = new Queue();
                                $queue->taskid = $taskid;
                                $queue->dirscanUrl = $scheme.$currenthost;
                                $queue->instrument = 3; //ffuf
                                $queue->wordlist = 1;
                                $queue->save();
                            }

                            $queue = new Queue();
                            $queue->taskid = $taskid;
                            $queue->dirscanUrl = $scheme.$currenthost;
                            $queue->instrument = 5; //whatweb
                            $queue->save();

                            $queue = new Queue();
                            $queue->taskid = $taskid;
                            $queue->dirscanUrl = $scheme.$currenthost;
                            $queue->instrument = 8; //nuclei
                            $queue->save();

                            $whatweb = new Whatweb();
                            $whatweb->url = $scheme.$currenthost;
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
            }
        } else file_get_contents($output); //we need an error to check it out in debugger and rescan later
        
        return 1;
    }

}