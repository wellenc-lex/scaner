<?php

namespace frontend\models;

use Yii;
use frontend\models\Queue;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;
require_once 'Dirscan.php';

ini_set('max_execution_time', 0);

class Amass extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function bannedwords($in)
    {
        return preg_match("/img|cdn|sentry|support/i", $in);
    }

    //scans again after error
    public function RestoreAmass($randomid)
    {   
        $gauoutputname="/dockerresults/" .$randomid. "unique.txt";
        //we need to store vhosts somewhere + httpx needs taskid.
        $tasks = new Tasks();
        $tasks->hidden = 1;
        $tasks->amass_status = 'Working';
        $tasks->save();

        $taskid = $tasks->taskid;

        Yii::$app->db->close();  

        $fileamass = file_get_contents("/dockerresults/" . $randomid . "amass.json");

        $fileamass = str_replace("}
{\"Timestamp\"", "},{\"Timestamp\"", $fileamass);

        $fileamass = str_replace("} {", "},{", $fileamass);

        $fileamass = str_replace("}
{", "},{", $fileamass);

        $fileamass = str_replace("}
{\"name\"", "},{\"name\"", $fileamass);

        $amassoutput = '[' . $fileamass . ']';

        $aquatoneoutput = "[]"; #amass::aquatone($randomid);

        $subtakeover = 0;

        if (file_exists($gauoutputname)) {
            $gau = file_get_contents($gauoutputname);
            $gau = explode(PHP_EOL,$gau);
        } else $gau="[]";

        $vhosts = amass::vhosts($amassoutput, $gau, $taskid);

        amass::httpxhosts($vhosts, $taskid, $randomid); // dirscan domains found by amass+gau

        $vhosts = json_encode($vhosts);

        amass::saveToDB($taskid, $amassoutput, $intelamass, $aquatoneoutput, $subtakeover, $vhosts);

        return exec("sudo rm -r /dockerresults/" . $randomid . "");
    }

    public function saveToDB($taskid, $amassoutput, $intelamass, $aquatoneoutput, $subtakeover, $vhosts)
    {
        if($amassoutput != "[]" && $amassoutput != '"No file."'){

            try{
                Yii::$app->db->open();

                $amass = Tasks::find()
                    ->where(['taskid' => $taskid])
                    ->limit(1)
                    ->one();

                if(!empty($amass)){ //if querry exists in db

                    $amass->amass_status = 'Done.';
                    $amass->amass = $amassoutput;
                    $amass->amass_intel = $intelamass;
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
                    $amass->amass_intel = $intelamass;  
                    $amass->notify_instrument = $amass->notify_instrument."2";
                    $amass->aquatone = $aquatoneoutput;
                    $amass->vhostwordlist = $vhosts;
                    $amass->subtakeover = $subtakeover;
                    $amass->hidden = 1;
                    $amass->date = date("Y-m-d H-i-s");

                    $amass->save(); 
                }

                //add vhost scan to queue
                $queue = new Queue();
                $queue->taskid = $taskid;
                $queue->instrument = 7;
                $queue->save();

                /*
                //add git scan to queue
                $queue = new Queue();
                $queue->taskid = $taskid;
                $queue->instrument = 4;
                $queue->save();
                */
                
                return 1;

            } catch (\yii\db\Exception $exception) {
                var_dump($exception);
                sleep(360);

                $amass = new Tasks();
                        
                $amass->taskid = $taskid;
                $amass->amass_status = 'Done.';
                $amass->amass = $amassoutput;
                $amass->amass_intel = $intelamass;  
                $amass->aquatone = $aquatoneoutput;
                $amass->vhostwordlist = $vhosts;
                $amass->subtakeover = $subtakeover;
                $amass->hidden = 1;
                $amass->date = date("Y-m-d H-i-s");

                $amass->save(); 

                return $exception.json_encode(array_unique($output));
            }
        }
    }

    public function vhosts($amassoutput, $gau, $taskid)
    {
        global $maindomain;
        //get subdomain names from amass and gau to use it as virtual hosts wordlist

        /*
        app.dev.cloud.google.com ->
        app.dev.cloud
        app.dev
        app
        dev
        cloud
        */

        function splitting($input){
            global $wordlist;
            preg_match_all("/\w*\./", $input, $out);

            if($out[0][0]!=""){
                $word = implode("", $out[0]);
                $word = rtrim($word, ".");
                $wordlist[] = $word;
                splitting($word);
            }

        }

        $wordlist = array(); //all words from subdomain names
        $vhostswordlist = array(); //subdomains from gau and amass

        if(isset($amassoutput) && $amassoutput!=""){

            $amassoutput = json_decode($amassoutput, true);

            //Get vhost names from amass scan & wordlist file + use only unique ones
            foreach ($amassoutput as $amass) {

                $name = $amass["name"];

                $maindomain = $amass["domain"];

                if (strpos($name, 'https://xn--') === false) {

                    $vhostswordlist[] = $name;

                    splitting($name);

                    preg_match_all("/\w*\./", $name, $matches);

                    foreach($matches[0] as $match){
                        $wordlist[] = rtrim($match, ".");
                    }
                }
            }
        }

        if($gau!=""){
            
            foreach($gau as $subdomain){
                
                if (strpos($subdomain, $maindomain) !== false && amass::bannedwords($subdomain) === 0 ) {

                    $vhostswordlist[] = dirscan::ParseHostname($subdomain);
                
                }
            }
        }
        
        $vhostswordlist = array_unique(array_merge($wordlist,$vhostswordlist));

        return $vhostswordlist;
    }

    public function httpxhosts($vhostslist, $taskid, $randomid)
    {
        global $maindomain;

        $wordlist = "/dockerresults/" . $randomid . "hosts.txt";
        $output = "/dockerresults/" . $randomid . "httpx.txt";
        
        file_put_contents($wordlist, implode( PHP_EOL, $vhostslist) );

        $httpx = "sudo docker run --cpu-shares 256 --rm -v dockerresults:/dockerresults projectdiscovery/httpx -exclude-cdn -ports 80,443,8080,8443,8000,3000,8888,8880,10000,4443,6443,10250 -silent -o ". $output ." -l ". $wordlist ."";
        
        exec($httpx);

        $hostnames = array(); //we dont need duplicates like http://goo.gl and https://goo.gl so we parse everything after scheme and validate that its unique

        if (file_exists($output)) {
            $alive = file_get_contents($output);
            $alive = explode(PHP_EOL,$alive);

            $alive = array_unique($alive); 

            rsort($alive); //rsort so https:// will be first and we get less invalid duplicates below

            foreach($alive as $url) {

                if($url != "" && strpos($url, $maindomain) !== false ){ //check that domain corresponds to amass domain. (in case gau gave us wrong info)

                    $currenthost = dirscan::ParseHostname($url).dirscan::ParsePort($url);

                    if( !in_array($currenthost, $hostnames ) ){

                        if( amass::bannedwords($subdomain) === 0 ){
                            $queue = new Queue();
                            $queue->taskid = $taskid;
                            $queue->dirscanUrl = dirscan::ParseScheme($url).$currenthost;
                            $queue->instrument = 3;
                            $queue->wordlist = 1;
                            $queue->save();

                            $hostnames[] = $currenthost;
                        }    
                    }
                }
            }
        }
        
        return 1;
    }

    public function gauhosts($domain, $randomid, $gauoutputname)
    {
        //Get subdomains from gau
        $name="/dockerresults/" .$randomid. "gau.txt";

        $blacklist = "'js,eot,jpg,jpeg,gif,css,tif,tiff,png,ttf,otf,woff,woff2,ico,pdf,svg,txt,ico,icons,images,img,images,fonts,font-icons'";

        $gau = "sudo chmod -R 777 /dockerresults && sudo chmod -R 777 /dockerresults/ && sudo docker run --cpu-shares 512 --rm -v dockerresults:/dockerresults 5631/gau timeout 10000 gau -b ". $blacklist ." -t 1 -retries 15 -subs -o ". $name ." " . escapeshellarg($domain) . " ";

        exec($gau);

        //filters url scheme and some unicode symbols
        exec("cat ". $name ." | grep -vE '(https?:\/\/)xn\-\-*' | grep -E '(https?:\/\/).[^\/\:]*' -o | sed -nre 's~https?://~~p' | sort -u | tee -a ". $gauoutputname ."");

        if (file_exists($gauoutputname)) {
            $output = file_get_contents($gauoutputname);
            $output = explode(PHP_EOL,$output);
        } else $output="[]";
        
        return $output;
    }
    
    public function aquatone($randomid)
    {

        $command = "cat /dockerresults/" . $randomid . "amass.json | sudo docker run -v screenshots:/screenshots --rm -i 5631/aquatone -http-timeout 20000 -threads 3 -ports large -scan-timeout 5000 -screenshot-timeout 6000 -chrome-path /usr/bin/chromium-browser -out /screenshots/" . $randomid . " -save-body false > /dev/null";

        exec($command);

        if (file_exists("/screenshots/" . $randomid . "/aquatone_report.html")) {
            $fileaquatone = file_get_contents("/screenshots/" . $randomid . "/aquatone_report.html");
            $fileaquatone = str_replace('<img src="screenshots', '<img src="../../screenshots', $fileaquatone);

            $fileaquatone = str_replace('<a href="screenshots', '<a href="../../screenshots', $fileaquatone);

            $fileaquatone = str_replace('<link rel="stylesheet" href="https://bootswatch.com/4/darkly/bootstrap.min.css" integrity="sha384-RVGPQcy+W2jAbpqAb6ccq2OfPpkoXhrYRMFFD3JPdu3MDyeRvKPII9C82K13lxn4" crossorigin="anonymous">', '<link rel="stylesheet" href="https://bootswatch.com/3/darkly/bootstrap.min.css">', $fileaquatone);

            $fileaquatone = str_replace('</html>>', '</html>', $fileaquatone);

            $fileaquatone = str_replace('.cluster {
                border-bottom: 1px solid rgb(68, 68, 68);
                padding: 30px 20px 20px 20px;
                overflow-x: auto;
                white-space: nowrap;
            }', '.cluster {
                border-bottom: 1px solid rgb(68, 68, 68);
                box-shadow: inset 0px 6px 8px rgb(24, 24, 24);
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
                box-shadow: inset 0px 6px 8px rgb(24, 24, 24);
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
            $clearthemess = "sudo chmod -R 777 /screenshots/" . $randomid . "/screenshots && cp -R --remove-destination /screenshots/" . $randomid . "/screenshots /var/www/app/frontend/web/ && sudo rm -r /screenshots/" . $randomid . "/ && sudo chmod -R 777 /var/www/app/frontend/web/screenshots && sudo rm /dockerresults/" . $randomid . "amass*";

            exec($clearthemess);

        } else $fileaquatone="No screenshots";

        return $fileaquatone;
    }

    public static function amassscan($input)
    {

        $url = $input["url"];
        $taskid = (int) $input["taskid"]; if($taskid=="") $taskid = 1030;

        $url = trim($url, ' ');
        $url = rtrim($url, '/');

        $url = strtolower($url);
        $url = str_replace("http://", "", $url);
        $url = str_replace("https://", "", $url);
        $url = str_replace("www.", "", $url);
        $url = str_replace(" ", ",", $url);
        $url = str_replace(",", " ", $url);
        $url = str_replace("\r", " ", $url);
        $url = str_replace("\n", " ", $url);
        $url = str_replace("|", " ", $url);
        $url = str_replace("&", " ", $url);
        $url = str_replace("&&", " ", $url);
        $url = str_replace(">", " ", $url);
        $url = str_replace("<", " ", $url);
        $url = str_replace("/", " ", $url);
        $url = str_replace("'", " ", $url);
        $url = str_replace("\"", " ", $url);
        $url = str_replace("\\", " ", $url);

        $url = rtrim($url, '/');

        $randomid = rand(1,100000000);
        htmlspecialchars($url);

        $gauoutputname="/dockerresults/" .$randomid. "unique.txt";
        $gau = amass::gauhosts($url, $randomid, $gauoutputname);

        $enumoutput = "/dockerresults/" . $randomid . "amass.json";

        $inteloutput = "/dockerresults/" . $randomid . "amassINTEL.txt";

        //$amassconfig = "/configs/amass". rand(1,6). ".ini";

        $amassconfig = "/configs/amass4.ini";

        if( !file_exists($amassconfig) ){
            $amassconfig = "/configs/amass1.ini";
        }

        $command = exec("sudo docker run --cpu-shares 256 --rm -v configs:/configs/ -v dockerresults:/dockerresults caffix/amass enum -w " . $gauoutputname . " -w /configs/amasswordlistALL.txt -d  " . escapeshellarg($url) . " -json " . $enumoutput . " -active -brute -timeout 1600 -ip -config ".$amassconfig);

        exec($command);

        if (file_exists($enumoutput)) {
            $fileamass = file_get_contents($enumoutput);
        } else {
            sleep(1800);
            exec($command);
            $fileamass = file_get_contents($enumoutput);
        }

        
        exec("sudo docker run --rm -v configs:/configs/ -v dockerresults:/dockerresults caffix/amass intel -d  " . escapeshellarg($url) . " -o " . $inteloutput . " -active -whois -config ".$amassconfig);

        if (file_exists($inteloutput)) {
            $intelamass = file_get_contents($inteloutput);

            $intelamass = array_unique(explode(PHP_EOL,$intelamass));

            $intelamass = json_encode($intelamass);
        } else {
            $intelamass = NULL;
        }


        $fileamass = str_replace("}
{\"Timestamp\"", "},{\"Timestamp\"", $fileamass);

        $fileamass = str_replace("} {", "},{", $fileamass);

        $fileamass = str_replace("}
{", "},{", $fileamass);

        $fileamass = str_replace("}
{\"name\"", "},{\"name\"", $fileamass);

        $amassoutput = '[' . $fileamass . ']';

        $aquatoneoutput = "[]"; #amass::aquatone($randomid);

        $subtakeover = 0;

        $vhosts = amass::vhosts($amassoutput, $gau, $taskid);

        amass::httpxhosts($vhosts, $taskid, $randomid); // dirscan domains found by amass+gau

        $vhosts = json_encode($vhosts);

        amass::saveToDB($taskid, $amassoutput, $intelamass, $aquatoneoutput, $subtakeover, $vhosts);

        dirscan::queuedone($input["queueid"]);

        return exec("sudo rm -r /dockerresults/" . $randomid . "");
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


















