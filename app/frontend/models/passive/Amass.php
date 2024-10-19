<?php

namespace frontend\models\passive;

use Yii;
use frontend\models\Queue;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;
use frontend\models\PassiveScan;
use frontend\models\Whatweb;
use frontend\models\Vhostscan;
use frontend\models\Aquatone;

class Amass extends ActiveRecord
{
    public static function custom_sort($a,$b) {
      return $a['url']<$b['url'];
    }

    public static function var_dump_f ($val) {
      ob_start();
      var_dump($val);
      $output = ob_get_clean();
      file_put_contents('/tmp/'.rand(10000, 10000000000).'phpdump.txt', $output);
    }

    public static function var_dump_all ($val) {
      ob_start();
      var_dump($val);
      $output = ob_get_clean();
      file_put_contents('/tmp/DUMPALL'.rand(10000, 10000000000).'.txt', $output);
    }

    /**
     * 0 == no diffs between subdomains
     * 1 == previous != new information, needs diff.
    */

    public static function amassscan($input)
    {
        global $maindomain;
        global $NEWips;
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

        //$amassconfig = "/configs/amass/amass1.ini";

        if( !file_exists($amassconfig) ){
            $amassconfig = "/configs/amass/amass1.ini.example";
        }

	    exec("sudo mkdir -p /dev/shm/amass" . $randomid); //run in memory

//--net=host 

        $command = "sudo docker run --privileged=true --cpu-shares 256 --rm -v configs:/configs/ -v dockerresults:/dockerresults aortmann/amass:v3.23.3-extra-dns-resolvers -dir /dev/shm/amass" . $randomid . " -d " . escapeshellarg($url) . " -active -alts -brute -min-for-recursive 2 -timeout 2800 -config ". $amassconfig ." -w /configs/amass/amasswordlist.txt -trf /configs/amass/resolvers.txt -json " . $enumoutput . " ";

        exec($command);
        
        //docker run --privileged=true --cpu-shares 256 --rm -v configs:/configs/ -v dockerresults:/dockerresults aortmann/amass:v3.23.3-extra-dns-resolvers -dir /dev/shm/amass1 -d ya.ru -active -alts -brute -min-for-recursive 2 -timeout 5 -w /configs/amass/amasswordlist.txt -config /configs/amass/amass1.ini
        
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

            $NEWips = array();

            $amassoutput = json_decode($amassoutput, true);

            if ( !empty($amassoutput) && $amassoutput != NULL ){

                //amass::var_dump_all($amassoutput);

                $maindomain = $amassoutput[0]["domain"];

                //Get vhost names from amass scan & wordlist file + use only unique ones
                foreach ($amassoutput as $amass) {

                    $name = $amass["name"];

                    if (strpos($name, 'https://xn--') === false) {

                        $NEWsubdomains[] = $name; // full hostname for Host: header
                    }

                    foreach( $amass["addresses"] as $vhostarr ){
                        //print_r($vhostarr);
                        $ip = $vhostarr;
                        if ( vhostscan::ipCheck( $ip["ip"] == 0 ) ) $NEWips[] = $ip["ip"];
                    }
                }
            }
        }

        return amass::saveToDB($scanid, $NEWsubdomains, $randomid, $NEWips);
    }

    public static function bannedwords($in)
    {
        if (preg_match("/dev|stage|test|proxy|stg|int|adm|uat|support/i", $in) === 1) {
            return 0; //if its used for internal or develop purposes - scan anyway
        } else { 
            return preg_match("/sentry|^ws|wiki|status|socket|cloclo\d*.cldmail.ru|docs|sf\d*.m.smailru.net|spider*.yandex.com|scf\d*.m.smailru.net|upload-.*.hb.bizmrg.com|vb.*\.sberdevices.*|url(\d)*/i", $in);
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
        $httpx = "sudo docker run --cpu-shares 512 --rm -v dockerresults:/dockerresults projectdiscovery/httpx -ports 1080,1100,80,443,8080,8443,8000,3000,3301,8083,8088,8888,2379,8880,5553,6443,9999,10000,13000,10250,4443,6443,10255,2379,6666,8123,2181,9092,9200,28080 -rate-limit 15 -timeout 35 -threads 50 -retries 3 -fc 301,302 -silent -o ". $output ." -l ". $wordlist ." -json -tech-detect -title -favicon -ip -sr -srd ". $httpxresponsesdir;
        
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

                usort($alive, "custom_sort"); //https:// will be at the top and we get less invalid duplicates with http:// below

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

    public static function saveToDB($scanid, $NEWsubdomains, $randomid, $NEWips)
    {
        global $NEWips;
        do{
            try{
                //amass::var_dump_f($NEWips);
                $tryAgain = false;

                Yii::$app->db->open();

                $amass = PassiveScan::find()
                    ->where(['PassiveScanid' => $scanid])
                    ->limit(1)
                    ->one();

                Yii::$app->db->close();

                $aquatonefile = "/dockerresults/" . $randomid . "aquatoneinput.txt";

                if ($amass->amass_new == "") {
                    if( !empty($NEWsubdomains) && $NEWsubdomains!= "[]" ) { 
                        $amass->amass_new = json_encode($NEWsubdomains);
                        $amass->save();
                    }

                    if( !empty($NEWsubdomains) ) {
                        amass::httpxhosts( array_unique($NEWsubdomains), $scanid, $randomid );

                        file_put_contents($aquatonefile, implode( PHP_EOL, array_unique($NEWsubdomains) ) );
                        aquatone::aquatonepassive($randomid, $aquatonefile);
                    }

                    // no changes between scans
                    //wrong logic,rewrite

                } elseif ($amass->amass_new != "") { //latest scan info in DB

                    if ($NEWsubdomains === json_decode($amass->amass_new) ) {
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
                            
                            file_put_contents($aquatonefile, implode( PHP_EOL, array_unique($diff) ) );
                            aquatone::aquatonepassive($randomid, $aquatonefile);
                        }

                        if( !empty($NEWsubdomains) && empty($OLDsubdomains)) {
                            amass::httpxhosts( array_unique($NEWsubdomains), $scanid, $randomid );

                            file_put_contents($aquatonefile, implode( PHP_EOL, array_unique($NEWsubdomains) ) );
                            aquatone::aquatonepassive($randomid, $aquatonefile);
                        }
                    }
                }

                if ($amass->amass_ips_new != "") {
                    if ( !empty($NEWips) ) {

                        $OLDips = json_decode($amass->amass_ips_new);

                        if ($NEWips !== $OLDips ) {

                            $amass->amass_ips_old = $amass->amass_ips_new;
                            $amass->amass_ips_new = json_encode( $NEWips );

                            if( !empty($NEWips) && !empty($OLDips) ) {
                                $diff = array_diff( $NEWips, $OLDips ); // only new subdomains in the list

                                if ( !empty($diff) ) {
                                    $queue = new Queue();
                                    $queue->taskid = $task->taskid;
                                    $queue->instrument = 1;
                                    $queue->nmap = implode(" ", array_unique($diff) );
                                    $queue->save();
                                }
                            }

                            //All ips ever found with amass
                            $amass->amass_ips = array_unique(
                                array_merge($NEWips, json_decode($amass->amass_ips, true) )
                            );

                            $amass->amass_ips = json_encode($amass->amass_ips);
                            $amass->save();
                        }
                    }
                }

                if (empty($amass->amass_ips) && !empty($NEWips) ) {
                    $amass->amass_ips = json_encode($NEWips);
                    $amass->amass_ips_new = json_encode($NEWips);
                    
                    $queue = new Queue();
                    $queue->taskid = $task->taskid;
                    $queue->instrument = 1;
                    $queue->nmap = implode(" ", array_unique($NEWips) );
                    $queue->save();

                    $amass->save();
                }

		        exec("sudo rm -rf /dev/shm/amass" . $randomid);

                return $changes;

            } catch (\yii\db\Exception $exception) {
                sleep(6000);

                $tryAgain = true;

                amass::saveToDB($scanid, $NEWsubdomains, $randomid, $NEWips);
            }
        } while($tryAgain);
    }

}