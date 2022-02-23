<?php

namespace frontend\models;

use Yii;
use frontend\models\Queue;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;

ini_set('max_execution_time', 0);

class Amass extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function bannedwords($in)
    {
        if (preg_match("/dev|stage|test|proxy|stg|int|adm|uat/i", $in) === 1) {
            return 0; //if its used for internal or develop purposes - scan anyway
        } else { 
            return preg_match("/img|cdn|sentry|support|^ws|wiki|status|socket|docs|url(\d)*/i", $in);
        }
    }

     public function dosplit($input){
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

    public function split2($input){
        //www.test.google.com -> www.test -> www
        global $wordlist;

        preg_match_all("/(\w[\-\_\d]?)*\./", $input, $matches);

        foreach($matches[0] as $match){
            $wordlist[] = rtrim($match, "."); 
        }
    }

    //resume scan after some IO/DB error
    public function RestoreAmass()
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

                if($amassoutput != "[]" && $amassoutput != "[{}]" && !empty($amassoutput)){

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
                }

                exec("sudo rm /dockerresults/" . $randomid . "*");
            }
        }
        
        return 1;
    }




    public function saveToDB($taskid, $amassoutput, $aquatoneoutput, $subtakeover, $vhosts)
    {
        if($amassoutput != "[]" && $amassoutput != '"No file."'){

            try{
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

                    $amass->save();

                }

                /*
                //add git scan to queue
                $queue = new Queue();
                $queue->taskid = $taskid;
                $queue->instrument = 4;
                $queue->save();
                */
                
                return Yii::$app->db->close();;

            } catch (\yii\db\Exception $exception) {
                var_dump($exception);
                sleep(1000);

                amass::saveToDB($taskid, $amassoutput, $aquatoneoutput, $subtakeover, $vhosts);

                return $exception.json_encode($output);
            }
        }
    }

    public function saveintelToDB($taskid, $intelamass)
    {
        if($intelamass != ""){

            try{

                $intelresults = array();

                Yii::$app->db->open();

                $all = Amassintel::find()
                    ->where(['id' => "1"])
                    ->limit(1)
                    ->one();

                Yii::$app->db->close();

                $domains = json_decode($all->domains, true);

                foreach ($intelamass as $inteldomain) {

                    if ( !in_array($inteldomain, $domains) ) { //if not found in array
                        $intelresults[] = $inteldomain;
                        $domains[] = $inteldomain; // all the domains ever found by amass intel
                    }
                }

                //if there are unique domains in output
                if ( $intelresults != "" && $intelresults != "[]" ){

                    Yii::$app->db->open();

                    $amass = Tasks::find()
                    ->where(['taskid' => $taskid])
                    ->limit(1)
                    ->one();

                    if(!empty($amass) && empty($amass->amass_intel) ){ //if querry exists in db

                        $amass->amass_intel  = json_encode($intelresults);
                        $amass->save();

                    } else {
                        $amass = new Tasks();
                        
                        $amass->taskid = $taskid;
                        $amass->amass_status = 'Done.';
                        $amass->amass_intel  = json_encode($intelresults);
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
                sleep(1000);

                amass::saveintelToDB($taskid, $intelamass);

                return $exception.json_encode(array_unique($output));
            }
        }
    }

    public function vhosts($amassoutput, $gau, $taskid, $randomid)
    {
        global $maindomain; global $wordlist;
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

            if (!empty($amassoutput) ){

                //Get vhost names from amass scan & wordlist file + use only unique ones
                foreach ($amassoutput as $amass) {

                    $name = $amass["name"];

                    $maindomain = $amass["domain"];

                    if (strpos($name, 'https://xn--') === false) {

                        $hostwordlist[] = $name; // full hostname for Host: header

                        amass::dosplit($name);

                        amass::split2($name);
                    }
                }
            }
        }

        if($gau!=""){
            
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

        return $vhostswordlist;
    }

    public function httpxhosts($vhostslist, $taskid, $randomid)
    {
        global $maindomain;

        $wordlist = "/dockerresults/" . $randomid . "hosts.txt";
        $output = "/dockerresults/" . $randomid . "httpx.txt";
        
        file_put_contents($wordlist, implode( PHP_EOL, $vhostslist) );

        $httpx = "sudo docker run --cpu-shares 512 --rm -v dockerresults:/dockerresults projectdiscovery/httpx -exclude-cdn -ports 80,443,8080,8443,8000,3000,8083,8088,8888,8880,9999,10000,4443,6443,10250 -rate-limit 5 -timeout 15 -retries 5 -silent -o ". $output ." -l ". $wordlist ."";
        
        exec($httpx);

        $hostnames = array(); //we dont need duplicates like http://goo.gl and https://goo.gl so we parse everything after scheme and validate that its unique

        if (file_exists($output)) {
            $alive = file_get_contents($output);
            $alive = explode(PHP_EOL,$alive);

            $alive = array_unique($alive); 

            rsort($alive); //rsort so https:// will be at the top and we get less invalid duplicates below

            foreach($alive as $url) {

                if($url != "" && strpos($url, $maindomain) !== false ){ //check that domain corresponds to amass domain. (in case gau gave us wrong info)

                    $currenthost = dirscan::ParseHostname($url).dirscan::ParsePort($url);

                    $scheme = dirscan::ParseScheme($url);
                    $port = dirscan::ParsePort($url); 

                    if( ($scheme==="http://" && $port===":443") || ($scheme==="https://" && $port===":80")){
                        continue; //scanning https port with http scheme is pointless so we get to the next host
                    }

                    if( !in_array($currenthost, $hostnames ) ){

                        if( amass::bannedwords($currenthost) === 0 ){

                            $queue = new Queue();
                            $queue->taskid = $taskid;
                            $queue->dirscanUrl = dirscan::ParseScheme($url).$currenthost;
                            $queue->instrument = 3; //ffuf
                            $queue->wordlist = 1;
                            $queue->save();

                            $queue = new Queue();
                            $queue->taskid = $taskid;
                            $queue->dirscanUrl = dirscan::ParseScheme($url).$currenthost;
                            $queue->instrument = 5; //whatweb
                            $queue->save();
                            
                            $hostnames[] = $currenthost;
                        }
                    }
                }
            }

            $queue = new Queue();
            $queue->taskid = $taskid;
            $queue->ipscan = $maindomain;
            $queue->instrument = 6; //ipscan - find IPS associated with this domain
            $queue->save();
        }
        
        return 1;
    }

    public function gauhosts($domain, $randomid, $gauoutputname)
    {
        //Get subdomains from gau
        $name="/dockerresults/" .$randomid. "gau.txt";

        $blacklist = "'js,eot,jpg,jpeg,gif,css,tif,tiff,png,ttf,otf,woff,woff2,ico,pdf,svg,txt,ico,icons,images,img,images,fonts,font-icons'";

        $gau = "sudo chmod -R 777 /dockerresults/ && timeout 5000 sudo docker run --cpu-shares 512 --rm -v dockerresults:/dockerresults sxcurity/gau:latest --blacklist ". $blacklist ." --threads 1 --retries 15 --fc 404,302,301 --subs --o ". $name ." " . escapeshellarg($domain) . " ";

        exec($gau);

        //filters url scheme and some unicode symbols
        exec("cat ". $name ." | grep -vE '(https?:\/\/)xn\-\-*' | grep -E '(https?:\/\/).[^\/\:]*' -o | sed -nre 's~https?://~~p' | sort -u | tee -a ". $gauoutputname ."");

        if (file_exists($gauoutputname)) {
            $output = file_get_contents($gauoutputname);
            $output = explode(PHP_EOL,$output);
        } else $output="[]";
        
        return $output;
    }

    public static function amassscan($input)
    {

        $url = $input["url"];
        $taskid = (int) $input["taskid"]; if($taskid=="") {
            $tasks = new Tasks();
            $taskid = $tasks->taskid;
        }

        $url = strtolower($url);
        $url = dirscan::ParseHostname($url);

        $url = str_replace("www.", "", $url);

        $randomid =  (int) $input["queueid"];//rand(1,100000000);
        htmlspecialchars($url);

        $gauoutputname="/dockerresults/" . $randomid . "uniquegau.txt";
        $gau = amass::gauhosts($url, $randomid, $gauoutputname);

        $enumoutput = "/dockerresults/" . $randomid . "amass.json";

        $inteloutput = "/dockerresults/" . $randomid . "amassINTEL.txt";

        //$amassconfig = "/configs/amass". rand(1,6). ".ini";

        $amassconfig = "/configs/amass4.ini";





        if( !file_exists($amassconfig) ){
            $amassconfig = "/configs/amass1.ini";
        }




        $command = ("sudo docker run --cpu-shares 512 --rm -v configs:/configs/ -v dockerresults:/dockerresults caffix/amass enum -w " . $gauoutputname . " -w /configs/amasswordlistALL.txt -d  " . escapeshellarg($url) . " -json " . $enumoutput . " -active -brute -timeout 2500 -ip -config ".$amassconfig);

        exec($command);

        if ( file_exists($enumoutput) ) {
            $fileamass = file_get_contents($enumoutput);
        } else {
            sleep(2000);
            exec($command);
            
            if ( !file_exists($enumoutput) ) {
                exec("sudo rm /dockerresults/" . $randomid . "*");
            }

            $fileamass = file_get_contents($enumoutput); // to get the error in the debug panel and investigate why there were no amass file created
        }

        exec("sudo docker run --rm -v configs:/configs/ -v dockerresults:/dockerresults caffix/amass intel -d  " . escapeshellarg($url) . " -o " . $inteloutput . " -active -whois -config ".$amassconfig);

        if ( file_exists($inteloutput) ){
            $intelamass = file_get_contents($inteloutput);

            $intelamass = array_unique(explode(PHP_EOL,$intelamass));

            if ( $intelamass != "" ){
                amass::saveintelToDB($taskid, $intelamass);
            }
        }

        //We need valid json object instead of separate json strings
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

}


/*$command = "/bin/cat /var/www/output/amass/" . $randomid . ".json | /usr/bin/jq --raw-output '.name' > /var/www/output/amass/jq" . $randomid .
            ".json && /usr/local/bin/subjack -w /var/www/output/amass/jq" . $randomid .
            ".json -t 10 -timeout 30 -a -o /var/www/output/subtakeover" . $randomid . ".txt -ssl -c /var/www/soft/subjack/fingerprints.json ";

        exec($command);

        if (file_exists("/var/www/output/subtakeover" . $randomid . ".txt")) {
            $subtakeover = file_get_contents("/var/www/output/subtakeover" . $randomid . ".txt");
        }

        else */


















