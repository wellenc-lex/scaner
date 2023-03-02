<?php

namespace frontend\models;

use Yii;
use frontend\models\Queue;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;
use frontend\models\Whatweb;
use frontend\models\Vhostscan;
use frontend\models\Aquatone;

ini_set('max_execution_time', 0);

class Amass extends ActiveRecord
{
    public static function amassscan($input)
    {
        global $amassconfig;

        sleep(rand(1,10));

        $url = $input["url"];
        $taskid = (int) $input["taskid"]; if($taskid=="") {
            $tasks = new Tasks();
            $taskid = $tasks->taskid;

            Yii::$app->db->close();
        }

        $url = strtolower($url);
        $url = dirscan::ParseHostname($url);

        $url = str_replace("www.", "", $url);

        $randomid =  (int) $input["queueid"];
        $url = htmlspecialchars($url);

        $gauoutputname="/dockerresults/" . $randomid . "uniquegau.txt";
        $gau = amass::gauhosts($url, $randomid, $gauoutputname);

        $enumoutput = "/dockerresults/" . $randomid . "amass.json";

        $amassconfig = "/configs/amass/amass". rand(1,25). ".ini";

        if( !file_exists($amassconfig) ){
            $amassconfig = "/configs/amass/amass1.ini.example";
        }
//--net=host
        $command = ("sudo docker run  --cpu-shares 256 --rm -v configs:/configs/ -v dockerresults:/dockerresults caffix/amass enum -w /configs/amass/amasswordlistASSETNOTE -d  " . escapeshellarg($url) . " -json " . $enumoutput . " -active -brute -timeout 4500 -ip -config ".$amassconfig);

        if (file_exists($gauoutputname) && filesize($gauoutputname) != 0){
            $command = $command . " -w " . $gauoutputname;
        }

        exec($command);

        if ( file_exists($enumoutput) ) {
            $fileamass = file_get_contents($enumoutput);
        } else {
            sleep(2000);
            exec($command);
            
            if ( !file_exists($enumoutput) ) {
                //exec("sudo rm /dockerresults/" . $randomid . "*");
            }

            if ( file_exists($enumoutput) ) {
                $fileamass = file_get_contents($enumoutput);
            } else dirscan::queuedone($input["queueid"]); return 0;

            //$fileamass = file_get_contents($enumoutput); // to get the error in the debug panel and investigate why there were no amass file created
        }

        //convert json strings into one json array to decode it
        $fileamass = str_replace("}
{\"Timestamp\"", "},{\"Timestamp\"", $fileamass);

        $fileamass = str_replace("} {", "},{", $fileamass);

        $fileamass = str_replace("}
{", "},{", $fileamass);

        $fileamass = str_replace("}
{\"name\"", "},{\"name\"", $fileamass);

        $amassoutput = '[' . $fileamass . ']';

        $aquatoneoutput = "[]"; #aquatone::aquatone($randomid);

        $subtakeover = 0;

        $vhosts = json_encode( amass::vhosts($amassoutput, $gau, $taskid, $randomid) );

        amass::saveToDB($taskid, $amassoutput, $aquatoneoutput, $subtakeover, $vhosts);

        dirscan::queuedone($input["queueid"]);

        return exec("sudo rm /dockerresults/" . $randomid . "*");
    }

    public static function tableName()
    {
        return 'tasks';
    }

    public static function bannedwords($in)
    {
        if (preg_match("/dev|stage|test|proxy|stg|int|adm|uat/i", $in) === 1) {
            return 0; //if its used for internal or develop purposes - scan anyway
        } else { 
            return preg_match("/sentry|support|^ws|wiki|status|socket|docs|url(\d)*/i", $in);
        }
    }

     public static function dosplit($input){
        //www.test.google.com -> www test google
        global $wordlist;
        preg_match_all("/(\w[\-\_\d]?)*\./", $input, $out);

        if($out[0][0]!=""){
            $word = implode("", $out[0]);
            $word = rtrim($word, ".");
            $wordlist[] = $word;
            amass::dosplit($word);
        }
    }

    public static function split2($input){
        //www.test.google.com -> www.test -> www
        global $wordlist;

        preg_match_all("/(\w[\-\_\d]?)*\./", $input, $matches);

        foreach($matches[0] as $match){
            $wordlist[] = rtrim($match, "."); 
        }
    }

    //resume scan after some IO/DB error
    public static function RestoreAmass()
    {   
        exec("find /dockerresults/*amass.json -mtime +3", $notdone);

        foreach ($notdone as $id){
            preg_match("/(\d)+/", $id, $out);
            
            $randomid = $out[0];

            $gauoutputname="/dockerresults/" .$randomid. "uniquegau.txt";
            //we need to store vhosts somewhere + httpx needs taskid.

            $fileamass = file_get_contents("/dockerresults/" . $randomid . "amass.json");

            if($fileamass != ""){

                $fileamass = str_replace("}
{\"Timestamp\"", "},{\"Timestamp\"", $fileamass);

                $fileamass = str_replace("} {", "},{", $fileamass);

                $fileamass = str_replace("}
{", "},{", $fileamass);

                $fileamass = str_replace("}
{\"name\"", "},{\"name\"", $fileamass);

                $amassoutput = '[' . $fileamass . ']';

                $aquatoneoutput = "[]"; 

                $subtakeover = 0;

                if($amassoutput != "[]" && $amassoutput != "[{}]" && !empty($amassoutput) ){

                    Yii::$app->db->open();  
                    
                    $tasks = new Tasks();
                    $tasks->hidden = 1;
                    $tasks->amass_status = 'Working';
                    $tasks->save();

                    $taskid = $tasks->taskid;

                    Yii::$app->db->close();

                    if (file_exists($gauoutputname)) {
                        $gau = file_get_contents($gauoutputname);
                        $gau = explode(PHP_EOL,$gau);
                    } else $gau="";

                    $vhosts = amass::vhosts($amassoutput, $gau, $taskid, $randomid);

                    $vhosts = json_encode($vhosts);

                    amass::saveToDB($taskid, $amassoutput, $aquatoneoutput, $subtakeover, $vhosts);

                    dirscan::queuedone($randomid);
                }

                $inteloutput = "/dockerresults/" . $randomid . "amassINTEL.txt";

                if ( file_exists($inteloutput) ){
                    $intelamass = file_get_contents($inteloutput);

                    $intelamass = array_unique(explode(PHP_EOL, $intelamass));

                    if ( $intelamass != "" ){
                        amass::saveintelToDB($taskid, $intelamass, $maindomain);
                    }
                }

                exec("sudo rm /dockerresults/" . $randomid . "*");
            }
        }


/*
        $inteloutput = "/dockerresults/1amassINTEL.txt"; 
        //Intel mail.ru ASNs  -ipv4
        exec("sudo docker run --dns 8.8.4.4 --rm -v configs:/configs/ -v dockerresults:/dockerresults caffix/amass intel -asn 47764,60476,47541,47542 -o ". $inteloutput . " -active -whois -config /configs/amass/amass". rand(1,9). ".ini -d vk.com,vk.company,mail.ru,skillbox.ru,gb.ru,skillfactory.ru -org Mail.ru,VK company,Mailru");

        if ( file_exists($inteloutput) ){
            $intelamass = file_get_contents($inteloutput);

            $intelamass = array_unique(explode(PHP_EOL,$intelamass));

            if ( $intelamass != "" ){
                amass::saveintelToDB(100, $intelamass);
            }
        }

        var_dump("sudo docker run --dns 8.8.4.4 --rm -v configs:/configs/ -v dockerresults:/dockerresults caffix/amass intel -asn 47764,60476,47541,47542 -o ". $inteloutput . " -active -whois -config /configs/amass/amass". rand(1,9). ".ini -d vk.com,vk.company,mail.ru,skillbox.ru,gb.ru,skillfactory.ru");
*/
        return 1;
    }




    public static function saveToDB($taskid, $amassoutput, $aquatoneoutput, $subtakeover, $vhosts)
    {
        global $ips;
        if($amassoutput != "[]" && $amassoutput != '"No file."'){

            do{
                try{
                    $tryAgain = false;
                    Yii::$app->db->open();

                    $amass = Tasks::find()
                        ->where(['taskid' => $taskid])
                        ->limit(1)
                        ->one();

                    if(!empty($amass) && $amass->amass == "" ){ //if querry exists in db

                        $amass->amass_status = 'Done.';
                        $amass->amass = $amassoutput;
                        $amass->notify_instrument = $amass->notify_instrument."2";
                        $amass->aquatone = $aquatoneoutput;
                        $amass->vhostwordlist = $vhosts;
                        $amass->subtakeover = $subtakeover;
                        $amass->hidden = 1;
                        $amass->date = date("Y-m-d H-i-s");

                        if ( !empty($ips) ) $amass->ips = implode(" ", array_unique($ips) );

                        $amass->save(); 
                    } else {
                        $amass = new Tasks();
                        
                        $amass->taskid = $taskid;
                        $amass->amass_status = 'Done.';
                        $amass->amass = $amassoutput;
                        $amass->notify_instrument = $amass->notify_instrument."2";
                        $amass->aquatone = $aquatoneoutput;
                        $amass->vhostwordlist = $vhosts;
                        $amass->subtakeover = $subtakeover;
                        $amass->hidden = 1;
                        $amass->date = date("Y-m-d H-i-s");

                        if ( !empty($ips) ) $amass->ips = implode(" ", array_unique($ips) );

                        $amass->save();

                    }

                    /*
                    //add git scan to queue
                    $queue = new Queue();
                    $queue->taskid = $taskid;
                    $queue->instrument = 4;
                    $queue->save();
                    */
                    
                    return Yii::$app->db->close();

                } catch (\yii\db\Exception $exception) {
                    sleep(6000);

                    $tryAgain = true;

                    amass::saveToDB($taskid, $amassoutput, $aquatoneoutput, $subtakeover, $vhosts);
                }
            } while($tryAgain);
        }
    }

    public static function saveintelToDB($taskid, $intelamass, $maindomain)
    {
        if($intelamass != ""){

            do{
                try{
                    $tryAgain = false;

                    $intelresults = array();

                    Yii::$app->db->open();

                    $all = Amassintel::find()
                        ->where(['id' => "1"])
                        ->limit(1)
                        ->one();

                    Yii::$app->db->close();

                    $domains = json_decode($all->domains, true);

                    foreach ($intelamass as $inteldomain) {

                        if( !empty($inteldomain) ) {

                            if (preg_match("/^xn\-\-/i", $inteldomain) === 1){
                                $inteldomain = idn_to_utf8($inteldomain);
                            }

                            if ( empty($domains) ){
                                $domains[] = $inteldomain; // all the domains ever found by amass intel
                            }

                            else if ( !in_array($inteldomain, $domains) ) { //if not found in array
                                $intelresults[] = $inteldomain;
                                $domains[] = $inteldomain; // all the domains ever found by amass intel
                            }
                        }
                    }

                    //if there are unique domains in output
                    if ( !empty($intelresults) ){

                        Yii::$app->db->open();

                        $amass = Tasks::find()
                        ->where(['taskid' => $taskid])
                        ->limit(1)
                        ->one();

                        if(!empty($amass) && empty($amass->amass_intel) ){ //if querry exists in db

                            $amass->amass_intel  = json_encode($intelresults);
                            $amass->host = $maindomain;
                            $amass->save();

                        } else {
                            $amass = new Tasks();
                            
                            $amass->taskid = $taskid;
                            $amass->amass_status = 'Done.';
                            $amass->amass_intel  = json_encode($intelresults);
                            $amass->host = $maindomain;
                            $amass->notify_instrument = "2";
                            $amass->hidden = 1;
                            $amass->date = date("Y-m-d H-i-s");

                            $amass->save(); 
                        }

                        $all->domains = json_encode( array_unique($domains) );
                        $all->save(); 
                    }
                    
                    return Yii::$app->db->close();

                } catch (\yii\db\Exception $exception) {
                    var_dump($exception);
                    sleep(6000);

                    $tryAgain = true;

                    amass::saveintelToDB($taskid, $intelamass);
                }
            } while($tryAgain);
        }
    }

    public static function vhosts($amassoutput, $gau, $taskid, $randomid)
    {
        global $maindomain; global $wordlist; global $amassconfig; global $ips;
        //get subdomain names from amass and gau to use it as virtual hosts wordlist

        /*
        app.dev.cloud.google.com ->
        app.dev.cloud
        app.dev
        app
        dev
        cloud
        */

        $vhostswordlist = array(); //subdomains from gau and amass with slices and alterations to find virtual hosts later

        $hostwordlist = array();

        if(isset($amassoutput) && $amassoutput!= "" && !empty($amassoutput) ){

            $amassoutput = json_decode($amassoutput, true);

            if ( !empty($amassoutput) && $amassoutput != NULL ){

                $maindomain = $amassoutput[0]["domain"];

                //Get vhost names from amass scan & wordlist file + use only unique ones
                foreach ($amassoutput as $amass) {

                    $name = $amass["name"];

                    if (strpos($name, 'https://xn--') === false) {

                        $hostwordlist[] = $name; // full hostname for Host: header

                        amass::dosplit($name);

                        amass::split2($name);
                    }

                    //add all ips observed by amass to the db to scan them later w nmap
                    foreach( $amass["addresses"] as $vhostarr ){
                        //print_r($vhostarr);
                        $ip = $vhostarr;
                        if ( vhostscan::ipCheck( $ip["ip"] == 0 ) ) $ips[] = $ip["ip"];
                    }
                }
            }
        }

        if($gau!="" && !empty($amassoutput) ){
            
            foreach($gau as $subdomain){
                
                if (strpos($subdomain, $maindomain) !== false && amass::bannedwords($subdomain) === 0 ) {

                    $hostwordlist[] = dirscan::ParseHostname($subdomain);

                    amass::split2($subdomain);
                }
            }
        }
        
        if ( $wordlist != "" ) {

            $vhostswordlist = array_unique(array_merge($wordlist,$hostwordlist));

        } else $vhostswordlist = array_unique($hostwordlist); // vhostwordlist to save into the DB
        
        amass::httpxhosts(array_unique($hostwordlist), $taskid, $randomid); // scanned amass subdomains with httpx to get alive hosts + scan it with ffuf later

        //if domain is not dummy - execute intel on it.
        if (isset($amassoutput) && $amassoutput!= "" && count($amassoutput) > 6 && !empty($maindomain) ){

            $inteloutput = "/dockerresults/" . $randomid . "amassINTEL.txt";

            exec("sudo docker run --cpu-shares 32 --rm -v configs:/configs/ -v dockerresults:/dockerresults caffix/amass intel -d  " . $maindomain . " -o " . $inteloutput . " -active -timeout 100 -whois -config ".$amassconfig);

            if ( file_exists($inteloutput) ){
                $intelamass = file_get_contents($inteloutput);

                $intelamass = array_unique(explode(PHP_EOL,$intelamass));

                if ( $intelamass != "" ){
                    amass::saveintelToDB($taskid, $intelamass, $maindomain);
                }
            }
        }

        return $vhostswordlist;
    }

    public static function httpxhosts($vhostslist, $taskid, $randomid)
    {
        global $maindomain;

        $wordlist = "/dockerresults/" . $randomid . "hosts.txt";
        $output = "/dockerresults/" . $randomid . "httpx.txt";
        $httpxresponsesdir = "/httpxresponses/" . $randomid. "/";
        
        file_put_contents($wordlist, implode( PHP_EOL, $vhostslist) );

        //--net=container:vpn1
        $httpx = "sudo docker run --net=host --cpu-shares 512 --rm -v dockerresults:/dockerresults projectdiscovery/httpx -ports 80,81,443,8080,8443,8000,3000,8083,8088,8888,8880,9999,10000,4443,6443,10250,8123,8000,2181,9092,9200,9100,9080,9443 -rate-limit 30 -timeout 30 -threads 30 -retries 3 -silent -o ". $output ." -l ". $wordlist ." -json -tech-detect -title -favicon -ip -sr -srd ". $httpxresponsesdir;
        
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

                $queue = new Queue();
                $queue->taskid = $taskid;
                $queue->ipscan = $maindomain;
                $queue->instrument = 6; //ipscan - find IPS associated with this domain
                $queue->save();

                Yii::$app->db->close();
            }
        } else file_get_contents($output); //we need an error to check it out in debugger and rescan later
        
        return 1;
    }

    public static function gauhosts($domain, $randomid, $gauoutputname)
    {
        //Get subdomains from gau
        $name="/dockerresults/" .$randomid. "gau.txt";

        $blacklist = "'js,eot,jpg,jpeg,gif,css,tif,tiff,png,ttf,otf,woff,woff2,ico,pdf,svg,txt,ico,icons,images,img,images,fonts,font-icons'";

        $gau = "timeout 5000 sudo docker run --net=host --cpu-shares 256 --rm -v dockerresults:/dockerresults sxcurity/gau:latest --blacklist ". $blacklist ." --threads 1 --retries 20 --timeout 95 --fc 504,404,302,301 --subs --o ". $name ." " . escapeshellarg($domain) . " ";

        exec($gau);

        //filters url scheme and some unicode symbols
        exec("cat ". $name ." | grep -vE '(https?:\/\/)xn\-\-*' | grep -E '(https?:\/\/).[^\/\:]*' -o | sed -nre 's~https?://~~p' | sort -u | tee -a ". $gauoutputname ."");

        if (file_exists($gauoutputname)) {
            $output = file_get_contents($gauoutputname);
            $output = explode(PHP_EOL,$output);
        } else $output="[]";
        
        return $output;
    }

}


/*$command = "/bin/cat /var/www/output/amass/" . $randomid . ".json | /usr/bin/jq --raw-output '.name' > /var/www/output/amass/jq" . $randomid .
            ".json && /usr/local/bin/subjack -w /var/www/output/amass/jq" . $randomid .
            ".json -t 10 -timeout 30 -a -o /var/www/output/subtakeover" . $randomid . ".txt -ssl -c /var/www/soft/subjack/fingerprints.json ";

        exec($command);

        if (file_exists("/var/www/output/subtakeover" . $randomid . ".txt")) {
            $subtakeover = file_get_contents("/var/www/output/subtakeover" . $randomid . ".txt");
        }

        else */


















