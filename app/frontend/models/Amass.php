<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

class Amass extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function ParseHostname($url)
    {
        $url = strtolower($url);
        $url = rtrim($url, '/');
        $url = rtrim($url, ' ');
        $url = rtrim($url, '/');

        preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/is", $url, $domain); //get hostname only
        
        return $domain[0];
    }

    public function altdns($inputhosts, $outputhosts, $alterationswordlist)
    {
        //$altpid = exec("sudo docker run --rm -v masscan:/masscan -v configs:/configs/ -v dockerresults:/dockerresults 5631/altdns -i " . $inputhosts . " -o " . $outputhosts . " -w " . $alterationswordlist . " >/dev/null 2>&1 & echo $!");

        $altpid = exec("sudo docker run --rm -v masscan:/masscan -v configs:/configs 5631/dnsgen -w " . $alterationswordlist . " " . $inputhosts . " " . $outputhosts . " >/dev/null 2>&1 & echo $! ");

        while (file_exists( "/proc/" . $altpid . "" )){
            sleep(1);
            echo("altdns exists\n");
        } 
        return 1;
    }

    public function MassDNS($hoststoscan, $resolvedhosts)
    {
        $masspid = exec("sudo docker run --rm -v masscan:/masscan -v configs:/configs/ -v dockerresults:/dockerresults 5631/masscan -r /configs/resolvers.txt --hashmap-size 5000 --resolve-count 15 -w " . $resolvedhosts . " -o J --retry SERVFAIL,REFUSED " . $hoststoscan . " >/dev/null 2>&1 & echo $!");

        while (file_exists( "/proc/" . $masspid . "" )){
            sleep(1);
            echo("MassDNS exists\n");
        } 
        return 1;
    }

    public function FdnsScan($domain, $inputdomainsfile, $outputdomainsfile)
    {

//record A!! + change wordlist file
        $fdnspid = exec("sudo docker run -v dockerresults:/dockerresults -v masscan:/masscan -v configs:/configs --rm 5631/fdns -domain " . $domain . " -record CNAME -file " . $inputdomainsfile . " > " . $outputdomainsfile . " 2>&1 & echo $!"); 

        while (file_exists( "/proc/" . $fdnspid . "" )){
            sleep(1);
            echo("fdns exists\n");
        } 
        return 1;
    }

    public function Amass($url, $randomid)
    {

        //$amasspid = exec("sudo docker run --rm -v configs:/configs/ -v masscan:/masscan caffix/amass enum -d  " . $url . " -json /masscan/" . $randomid . "/amass.json -active -brute -ip -config /configs/amass.ini -timeout 1000 >/dev/null 2>&1 & echo $! ");

        $amasspid = exec("sudo docker run --rm -v configs:/configs/ -v masscan:/masscan caffix/amass enum -d  " . $url . " -json /masscan/" . $randomid . "/amass.json -passive -ip -timeout 4 >/dev/null 2>&1 & echo $! ");

        while (file_exists( "/proc/" . $amasspid . "" )){
            sleep(1);
            echo("AMASS exists\n");
        } 
        return 1;
    }

    public function Aquatone($file, $randomid)
    {

        $aquatonepid = exec("cat " . $file . " | sudo docker run -v screenshots:/screenshots --rm -i 5631/aquatone -http-timeout 20000 -threads 5 -ports large -scan-timeout 5000 -screenshot-timeout 10000 -chrome-path /usr/bin/chromium-browser -out /screenshots/" . $randomid . " -save-body false > /dev/null 2>&1 & echo $! ");

        while (file_exists( "/proc/" . $aquatonepid . "" )){
            sleep(1);
            echo("Aquatone exists\n");
        } 
        return 1;
    }

    public function ServiceDesk($file, $randomid)
    {

        

        //domain
        //domain+git


//db servicedesk +scanresult


        //https://yourcompanyname.atlassian.net/servicedesk/customer/user/login
        //https://yourcompanyname.com.atlassian.net/servicedesk/customer/user/login
        //domain.club -> domainclub -> domain-club

        //str_replace . -> 
        // . -> -



        //(\w)*\.  $url = rtrim($url, '.'); //-> clean domain name
                

        //$amassoutput[0]["domain"] = "bitbank";

        $bitbucketurl = "https://bitbucket.org/" . $amassoutput[0]["domain"]  . "/profile/projects";
                
        $bitbucketout = shell_exec("curl ".$servicedeskurl);

        if (!(strpos($bitbucketout,"<title>404") !== false)) $servicedesk["bitbucket"] = $bitbucketout;

        $servicedeskurl = "https://" . $amassoutput[0]["domain"]  . ".atlassian.net/servicedesk/customer/user/login";
                
        $servicedeskout = shell_exec("curl ".$servicedeskurl);

        if (strpos($servicedeskout,"sdUserSignUpEnabled&quot;:true") !== false) $servicedesk["servicedesk"] = $servicedeskout;

        $atlassianurl = "https://" . $amassoutput[0]["domain"]  . ".atlassian.net/";
                
        $atlassianout = shell_exec("curl ".$atlassianurl);

        $servicedesk["atlassian"] = $atlassianout;

        if (isset($servicedesk)) json_encode($servicedesk);

        //var_dump($servicedesk);
                
        $aquatonepid = exec("cat " . $file . " | sudo docker run -v screenshots:/screenshots --rm -i 5631/aquatone -http-timeout 20000 -threads 5 -ports large -scan-timeout 5000 -screenshot-timeout 10000 -chrome-path /usr/bin/chromium-browser -out /screenshots/" . $randomid . " -save-body false > /dev/null 2>&1 & echo $! ");

        while (file_exists( "/proc/" . $aquatonepid . "" )){
            sleep(1);
            echo("Aquatone exists\n");
        } 
        return 1;
    }

    public function ParseFromMassDNS($file)
    {
        $domains = array();

        if (file_exists($file)) {
            $massdomains = file_get_contents($file);
            $massdomains = str_replace("}
{", "},{", $massdomains);
            $massdomains = '[' . $massdomains . ']';
            $massdomains = json_decode($massdomains, true);

            foreach ($massdomains as $massdomain) {
                if ( $massdomain["status"] === "NOERROR" ) {

                    if ( isset($massdomain["data"]["answers"]) && $massdomain["data"]["answers"] != "" ) {
                        $domains[] = rtrim($massdomain["name"], '.');
                    }

                    elseif ( isset($massdomain["data"]["authorities"]) && $massdomain["data"]["authorities"] != "" ){
                        $domains[] = rtrim($massdomain["name"], '.');
                    }
                }
            }           
        }
        return $domains;
    }

    public function ParseFromAmass($file)
    {
        $amassoutput = array();

        if (file_exists($file)) {

            $fileamass = file_get_contents($file);

            $fileamass = str_replace("}
{\"Timestamp\"", "},{\"Timestamp\"", $fileamass);

            $fileamass = str_replace("} {", "},{", $fileamass);

            $fileamass = str_replace("}
    {", "},{", $fileamass);

            $fileamass = str_replace("}
    {\"name\"", "},{\"name\"", $fileamass);

            $amassoutput = '[' . $fileamass . ']';
            return $amassoutput;
        }
        return 1;
    }

    public function ParseFromAquatone($file)
    {
        /** Copy the screenshots from the folder to volume root in order to be accessible all the time **/

        if (file_exists($file)) {
            $fileaquatone = file_get_contents($file);

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

            $aquatoneoutput = $fileaquatone;

            return $aquatoneoutput;
        }
        return 1;
    }

    public function Prepareforscanning($hostname, $randomid)
    {
        exec("sudo mkdir /masscan/" . $randomid . "/");
        exec("sudo cp /configs/vhostwordlist.txt /masscan/" . $randomid . "/masscanwordlist.txt");
        exec("sudo sed -e 's/$/." . $hostname . "/' -i /masscan/" . $randomid . "/masscanwordlist.txt");

        return 1;
    }

    public function Cleanup()
    {
        exec("sudo chmod -R 755 /screenshots/" . $randomid . "/screenshots && sudo cp -R --remove-destination /screenshots/" . $randomid . "/screenshots /var/www/app/frontend/web/ && sudo chmod -R 755 /var/www/app/frontend/web/screenshots/ && sudo rm -r /screenshots/" . $randomid . "/ && sudo rm -r /masscan/" . $randomid . "/ ");

        return 1;
    }

    public static function amassscan($input)
    {


//cat out.txt | awk '{print $1}' | sed 's/.$//' | sort -u > hosts-online.txt

//cat out.txt | awk '{print $1}' | sed 's/.$//' | sort -u > hosts-online.txt


        $url = amass::ParseHostname($input["url"]);

        $amass = Tasks::find()
            ->where(['taskid' => $input["taskid"]])
            ->limit(1)
            ->one();

        $randomid = $amass->taskid;

        amass::Prepareforscanning($url, $randomid);

        //$MassDNSWordlist = "/configs/MassDNSwordlist.txt";
        //$AltDNSInputFile = "/dockerresults/MassDNSout.txt";
        $MassDNSWordlist = "/masscan/" . $randomid . "/masscanwordlist.txt";
        $MassDNSOutputFile = "/masscan/" . $randomid . "/MassDNSout.txt";
        $MassDNSFNDSoutputfile = "/masscan/" . $randomid . "/massDNSFDNS.txt";

        $AltDNSOutputFile = "/masscan/" . $randomid . "/generatedAltdns.txt";
        $AltDNSWordlist = "/configs/altdnswordlist.txt";

        $FndsWordlistFile = "/configs/small1.json.gz";
        $FndsOutputFile = "/masscan/" . $randomid . "/FDNSout.txt";

        $AmassOutput = "/masscan/" . $randomid . "/amass.json";

        $AllDomains = "/masscan/" . $randomid . "/alldomains.txt";

        $AquatoneOutput = "/screenshots/" . $randomid . "/aquatone_report.html";


//run MassDNS for 30 mb wordlist prepare for scan change wordlist file

        //Resolve subdomains from huge wordlist 
        amass::MassDNS($MassDNSWordlist, $MassDNSOutputFile);

        //Alterations and permutations
        $domainsAlterations = amass::ParseFromMassDNS($MassDNSOutputFile);
        file_put_contents($MassDNSOutputFile."parsedAltdns", implode(PHP_EOL, $domainsAlterations));
        amass::altdns($MassDNSOutputFile."parsedAltdns", $AltDNSOutputFile, $AltDNSWordlist);

        if (file_exists($AltDNSOutputFile)) {
            //$domains = explode("\n", file_get_contents($AltDNSOutputFile));
            echo("altdns\n");    
            
            //Resolve subdomains generated with altdns 
            amass::MassDNS($AltDNSOutputFile, $MassDNSOutputFile."resolvedAltdns");
            
            $domainsAlterations = amass::ParseFromMassDNS($MassDNSOutputFile."resolvedAltdns");

            echo("got domains from first MassDNS+altdns\n");
        } else $domainsAlterations = "ALTDNS error";


//$url
        if (file_exists($FndsWordlistFile)) { 
            //Find subdomains in FDNS wordlist and write them to file
            amass::FdnsScan("rackcdn.com", $FndsWordlistFile, $FndsOutputFile);

            echo("fdns executed\n");

            if (file_exists($FndsOutputFile)) {
                //resolve domains from FDNS wordlist to find valid ones
                amass::MassDNS($FndsOutputFile, $MassDNSFNDSoutputfile);
                echo("scanned FDNS with MassDNS\n");

                //got domains resolved from FDNS;
                $domainsFDNS = amass::ParseFromMassDNS($MassDNSFNDSoutputfile);
            } else $domainsFDNS = "FDNS error";
        } else $domainsFDNS = "FDNS error";

        amass::Amass($url, $randomid);
        $amassoutput = amass::ParseFromAmass($AmassOutput);

        if($amassoutput !== 1){
            
            echo("amass\n");

            file_put_contents($AmassOutput."parsedAmass", implode(PHP_EOL, $amassoutput));
            amass::altdns($AmassOutput."parsedAmass", $AltDNSOutputFile."amass", $AltDNSWordlist);

            if (file_exists($AltDNSOutputFile."amass")) {
                $domains = explode("\n", file_get_contents($AltDNSOutputFile."amass"));
                echo("altdnsAMASS\n");    
                amass::MassDNS($AltDNSOutputFile."amass", $MassDNSOutputFile."amass");
                $domains = amass::ParseFromMassDNS($MassDNSFNDSoutputfile);
            }

            var_dump($domains);
            echo("last domains amass");

            file_put_contents($AllDomains, implode(PHP_EOL, $domains));

            amass::Aquatone($AllDomains, $randomid);

            $aquatoneoutput = amass::ParseFromAquatone($AquatoneOutput);
        } else $amassoutput = "Amass error";

        if($domainsFDNS == "FDNS error" && $domainsAlterations == "ALTDNS error" && $amassoutput == "Amass error"){
            $domains[] = "Execution error";
        }

        $domains = array_unique($domains);

        var_dump($domains);
        return 1;        

        amass::cleanup($randomid);

        $subtakeover = 0;

        $date_end = date("Y-m-d H-i-s");

        $amass->amass_status = 'Done.';
        $amass->amass = $done;
        $amass->aquatone = $aquatoneoutput;
        $amass->subtakeover = $subtakeover;
        $amass->servicedesk = $servicedesk;
        $amass->date = $date_end;

        $amass->save(); 

        $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
        $auth = getenv('Authorization', 'Basic bmdpbng6QWRtaW4=');
        exec('curl --insecure  -H \'Authorization: ' . $auth . '\' --data "taskid=' . $amass->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/vhostscan > /dev/null 2>/dev/null &');
                
        $decrement = ToolsAmount::find()
            ->where(['id' => 1])
            ->one();

        $value = $decrement->amass;
                
        if ($value <= 1) {
            $value=0;
        } else $value = $value-1;

        $decrement->amass=$value;
        $decrement->save();

        return 1;

    }

}



/* RIP DNS resolution (no unique DNS response for subdomain)
docker run -e AWS_ACCESS_KEY_ID=getenv('AWS_ACCESS_KEY_ID') -e AWS_SECRET_ACCESS_KEY=getenv('AWS_SECRET_ACCESS_KEY') -e AWS_DEFAULT_REGION=getenv('AWS_DEFAULT_REGION') -e AWS_DEFAULT_OUTPUT=text --rm -v /Users/mac/Documents/123/buckets/:/data 5631/buckets  --bucket /data/listing.txt --thread 5 --permut 2 --provider amazon --prefix /data/prefixes.txt --output /data/result2.json
*/


/*
                if (file_exists("/dockerresults/fdnsout1.txt")) {
                    $fdnsdomains = explode("\n", file_get_contents("/dockerresults/fdnsout1.txt"));

                    foreach ($fdnsdomains as $fdnsdomain) {
                        $domains[] = rtrim($fdnsdomain, '.');
                    }

                    $domains[]="gmail.google.com";
                    $domains[]="ya.ru";
                }

                var_dump($domains);
                echo("200!");
                return 123;
//}
file_put_contents('/dockerresults/newdomains1.txt', implode(PHP_EOL, $domains));
*/


/*$command = "/bin/cat /var/www/output/amass/" . $randomid . ".json | /usr/bin/jq --raw-output '.name' > /var/www/output/amass/jq" . $randomid .
                    ".json && /usr/local/bin/subjack -w /var/www/output/amass/jq" . $randomid .
                    ".json -t 10 -timeout 30 -a -o /var/www/output/subtakeover" . $randomid . ".txt -ssl -c /var/www/soft/subjack/fingerprints.json ";

                exec($command);

                if (file_exists("/var/www/output/subtakeover" . $randomid . ".txt")) {
                    $subtakeover = file_get_contents("/var/www/output/subtakeover" . $randomid . ".txt");
                }

                else */









