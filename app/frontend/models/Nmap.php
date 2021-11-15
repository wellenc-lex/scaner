<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

use frontend\models\Aquatone;


ini_set('max_execution_time', 0);

class Nmap extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function saveToDB($taskid, $nmapoutput)
    {
        if($nmapoutput != "[]"){

            try{
                Yii::$app->db->open();

                $task = Tasks::find()
                    ->where(['taskid' => $taskid])
                    ->limit(1)
                    ->one();

                if(!empty($task)){ //if querry exists in db

                    $task->nmap = $nmapoutput;
                    $task->nmap_status = "Done.";
                    $task->notify_instrument = $task->notify_instrument."1";
                    $task->date = date("Y-m-d H-i-s");

                    $task->save(); 

                } else {
                    $task = new Tasks();
                    
                    $task->taskid = $taskid;
                    $task->nmap_status = 'Done.';
                    $task->nmap = $nmapoutput;
                    $task->notify_instrument = "1";
                    $task->date = date("Y-m-d H-i-s");

                    $task->save();

                    $taskid = $task->taskid;
                }

            } catch (\yii\db\Exception $exception) {
                var_dump($exception);
                sleep(360);

                nmap::saveToDB($taskid, $output);
            }

            Yii::$app->db->close();
            
            return $taskid;
        }
    }

    public function nmapips($input)
    {
        if( $taskid == "" ){
            $taskid = (int) $input["taskid"];
        }

        if( $randomid == "" ){
            $randomid = (int) $input["randomid"];

            if( $randomid == "" ){
                $randomid = rand(100000, 1000000000);

                exec("mkdir /dockerresults/" . $randomid . "/");

                $scanIPS = "/dockerresults/" . $randomid . "/inputips.txt";

                file_put_contents($scanIPS, $input["ips"] );
            }
        }

        $scanIPS = "/dockerresults/" . $randomid . "/inputips.txt";
        $nmapoutputxml = "/dockerresults/" . $randomid . "/nmap.xml";
        $nmapoutputhtml = "/dockerresults/" . $randomid . "/nmap.html";

        $scripts = " --script=http-brute --script-args http-wordpress-brute.threads=1,brute.threads=1,brute.delay=2,unpwdb.timelimit=0,brute.firstonly=1 --script http-wordpress-brute --script http-open-proxy --script ftp-* --script rsync-list-modules --script mysql-* --script smb-os-discovery --script nfs-ls --script redis-brute".
                    " --script-args http-default-accounts.fingerprintfile=/configs/nmap-fingerprints.lua --script ms-sql-brute --script pgsql-brute --script smb-protocols -sC";
            
        exec("sudo docker run --cpu-shares 1024 --rm -v configs:/configs/ -v dockerresults:/dockerresults instrumentisto/nmap --privileged -g 53 -sS -sU -T4 --randomize-hosts -Pn -v -sV"
        ." -p T:1-65000,U:53,111,137,161,162,500,1434,5060,11211,67-69,123,135,138,139,445,514,520,631,1434,1900,4500,5353,49152 -A -R --min-hostgroup 500 --script-timeout 1500m --max-scan-delay 30s --max-retries 10 -oX "
            . $nmapoutputxml . " --stylesheet /configs/nmap.xsl -R " . $scripts . " -iL " . $scanIPS );

        exec("sudo /usr/bin/xsltproc -o " . $nmapoutputhtml . " /configs/nmap.xsl " . $nmapoutputxml . "");

        if (file_exists($nmapoutputhtml)) {
            $output = file_get_contents($nmapoutputhtml);
        } else $output = "";

        $taskid = nmap::saveToDB($taskid, $output); //if no taskid supplied by user create task and return its taskid

        return aquatone::aquatone($taskid, $nmapoutputxml, $input["queueid"]);
    }





    public function scanhost($input)
    {
        $url = dirscan::ParseHostname($input["url"]);
        $taskid = (int) $input["taskid"];

        $randomid = $taskid;

        //sudo nohup sudo nmap -sS -T3 -p- --open -iL sort.txt -oG - > /root/outputnmap.txt &

        //sudo nohup sudo masscan -p79-33000 --max-rate 10000 --open -iL input.txt -oX /root/outputmass.xml -oG /root/outputmass.txt -oL /root/outputmass.list &


        //brute default passwords -> default-http-login-hunter.sh urls.txt https://github.com/InfosecMatter/default-http-login-hunter

        //--script mysql-brute -p3306  --script-args userdb=users.txt, passdb=passwords.txt


#nmap -Pn -oG - 192.168.1.1 | awk '/open/{ s = $2; for (i = 5; i <= NF-4; i++) s = substr($i,1,length($i)-4) "\n"; split(s, a, "/"); print $2 ":" a[1]}'


#nmap -sT -p 80 --open 192.168.0.0/24 -oG - | awk '$4=="Ports:"{print $2}' > output.txt

        //sudo nohup sudo nmap -sS -T3 -p- -A --host-timeout 4000m --source-port 2002 --script-timeout 1500m -sC -oA /root/scan2 --stylesheet /root/project/docker/conf/configs/nmap.xsl --script=http-brute --script-args http-wordpress-brute.threads=1,brute.threads=1,brute.delay=2,unpwdb.timelimit=0,brute.firstonly=1 --script http-wordpress-brute --script http-open-proxy --script ftp-* --script rsync-list-modules --script mysql-* --script smb-os-discovery --script nfs-ls --script redis-brute --script-args http-default-accounts.fingerprintfile=/root/project/docker/conf/configs/nmap-fingerprints.lua --script-args http-default-accounts.fingerprintfile=/configs/nmap-fingerprints.lua --script ms-sql-brute --script pgsql-brute --script smb-protocols -sC > /root/nmap2.txt & 

        //-g 22 and --source-port
        
         
        //sudo /usr/bin/xsltproc -o /root/scan3.html /root/project/docker/conf/configs/nmap.xsl /root/scan3.xml
                
        $scripts = " --script=http-brute --script-args http-wordpress-brute.threads=1,brute.threads=1,brute.delay=2,unpwdb.timelimit=0,brute.firstonly=1 --script http-wordpress-brute --script http-open-proxy --script ftp-* --script rsync-list-modules --script mysql-* --script smb-os-discovery --script nfs-ls --script redis-brute".
                    "--script-args http-default-accounts.fingerprintfile=/configs/nmap-fingerprints.lua --script ms-sql-brute --script pgsql-brute --script smb-protocols -sC";


        //" . escapeshellarg($url) . " Gives Failed to resolve " $url ". , don't know how to fix, left it as is.
            
        exec("sudo docker run --cpu-shares 512 --rm -v configs:/configs/ -v dockerresults:/dockerresults instrumentisto/nmap -sS -T3 -p- -A --host-timeout 4000m --source-port 2002 --script-timeout 1500m -sC --max-rtt-timeout 1500ms -g 2002 -oX /dockerresults/scan" . $randomid . ".xml --stylesheet /configs/nmap.xsl -R " . $scripts . $url );

        exec("sudo /usr/bin/xsltproc -o /dockerresults/nmap/" . $randomid . ".html /configs/nmap.xsl /dockerresults/scan" . $randomid . ".xml ");

        if (file_exists("/dockerresults/nmap/" . $randomid . ".html")) {
            $output = file_get_contents("/dockerresults/nmap/" . $randomid . ".html");
        } else $output = "None.";

        $date_end = date("Y-m-d H-i-s");

        $task = Tasks::find()
            ->where(['taskid' => $taskid])
            ->limit(1)
            ->one();

        $task->nmap_status = "Done.";
        $task->nmap = $output;
        $task->date = $date_end;

        $task->save();

        exec("sudo rm /dockerresults/scan" . $randomid . ".xml && sudo rm /dockerresults/nmap/" . $randomid . ".html");

        return 1;
    }

}
