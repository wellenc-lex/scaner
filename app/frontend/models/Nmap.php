<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;
use frontend\models\Aquatone;


ini_set('max_execution_time', 0);

class Nmap extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public static function saveToDB($taskid, $nmapoutput)
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
                    $task->host = "Nmap.";
                    $task->notify_instrument = $task->notify_instrument."1";
                    $task->date = date("Y-m-d H-i-s");

                    $task->save(); 

                } else {
                    $task = new Tasks();
                    
                    $task->taskid = $taskid;
                    $task->nmap_status = 'Done.';
                    $task->host = "Nmap.";
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

    public static function nmapips($input)
    {
        //if function is being called from outside instead of curl
        if ( isset( $input["taskid"]) && $taskid!="" ) $taskid = (int) $input["taskid"]; else $taskid = "";
        
        $randomid = (int) $input["randomid"];
        
        $scanIPS = "/dockerresults/" . $randomid . "nmapinputips.txt";

        $nmapoutputdir = "/dockerresults/" . $randomid . "/";
        $nmapoutputxml = "/dockerresults/" . $randomid . "nmap.xml";
        $nmapoutputhtml = "/dockerresults/" . $randomid . "nmap.html";

        $outputtxt = "/dockerresults/" . $randomid . "naabu.txt";

        exec("sudo mkdir " . $nmapoutputdir . " && sudo chmod -R 777 " . $nmapoutputdir . " ");

        /*$scripts = " -A --script '\"(ajp-brute)\"' --script-args '\"(ajp-brute.timeout=8h,brute.firstonly=1)\"' --script '\"(ftp-brute)\"' --script-args '\"(ftp-brute.timeout=6h)\"' --script '\"(*vnc*)\"' --script-args '\"(vnc-brute.timeout=8h,brute.firstonly=1)\"' --script '\"(mongo* and default)\"' --script '\"(dns-zone-transfer)\"' --script '\"('dns-nsec-enum')\"' --script '\"('rmi-*')\"' --script '\"(memcached-info)\"' --script '\"('docker-*')\"'  "
        ." --script '\"(http-open-proxy)\"' --script '\"(ftp-*)\"' --script '\"(rsync-list-modules)\"' --script '\"(mysql-brute)\"' --script-args '\"(mysql-brute.timeout=8h,brute.firstonly=1)\"' --script '\"(mysql-empty-password)\"' "
        ." --script '\"(smb-os-discovery)\"' --script '\"(redis-brute)\"' --script '\"(amqp-info)\"' --script '\"(nfs-ls)\"' --script '\"(svn-brute)\"' --script-args '\"(svn-brute.timeout=8h,brute.firstonly=1)\"' "
        ." --script '\"(smb-brute)\"' --script-args '\"(smb-brute.timeout=8h,brute.firstonly=1)\"' --script '\"(ms-sql-brute)\"' --script-args '\"(ms-sql-brute.timeout=8h,brute.firstonly=1)\"' "
        ." --script '\"(http-default-accounts)\"' --script-args '\"(http-default-accounts.fingerprintfile=/configs/nmap/nmap-fingerprints.lua,http-default-accounts.timeout=24h)\"'  "
        ." --script '\"(pgsql-brute)\"' --script-args '\"(pgsql-brute.timeout=8h,brute.firstonly=1)\"' --script '\"(smb-protocols)\"' --script '\"('smb-enum-shares')\"' --script '\"('fcrdns')\"' -sC";*/
    
        //try -f --badsum to bypass IDS

        /*$scripts = " -A --script '\"ajp-brute\"' --script-args '\"ajp-brute.timeout=8h,brute.firstonly=1\"' --script '\"ftp-brute\"' --script-args '\"ftp-brute.timeout=6h\"' --script '\"*vnc*\"' --script-args '\"vnc-brute.timeout=8h,brute.firstonly=1\"' --script '\"mongo* and default\"' --script '\"dns-zone-transfer\"' --script '\"'dns-nsec-enum'\"' --script '\"'rmi-*'\"' --script '\"memcached-info\"' --script '\"'docker-*'\"'  "
        ." --script '\"http-open-proxy\"' --script '\"ftp-*\"' --script '\"rsync-list-modules\"' --script '\"mysql-brute\"' --script-args '\"mysql-brute.timeout=8h,brute.firstonly=1\"' --script '\"mysql-empty-password\"' "
        ." --script '\"smb-os-discovery\"' --script '\"redis-brute\"' --script '\"amqp-info\"' --script '\"nfs-ls\"' --script '\"svn-brute\"' --script-args '\"svn-brute.timeout=8h,brute.firstonly=1\"' "
        ." --script '\"smb-brute\"' --script-args '\"smb-brute.timeout=8h,brute.firstonly=1\"' --script '\"ms-sql-brute\"' --script-args '\"ms-sql-brute.timeout=8h,brute.firstonly=1\"' "
        ." --script '\"http-default-accounts\"' --script-args '\"http-default-accounts.fingerprintfile=/configs/nmap/nmap-fingerprints.lua,http-default-accounts.timeout=24h\"'  "
        ." --script '\"pgsql-brute\"' --script-args '\"pgsql-brute.timeout=8h,brute.firstonly=1\"' --script '\"smb-protocols\"' --script '\"'fcrdns'\"' -sC"; */


        /*$scripts = " -A --script '(ajp-brute)' --script-args '(ajp-brute.timeout=8h,brute.firstonly=1)' --script '(ftp-brute)' --script-args '(ftp-brute.timeout=6h)' --script '(*vnc*)' --script-args '(vnc-brute.timeout=8h,brute.firstonly=1)' --script '(mongo* and default)' --script '(dns-zone-transfer)' --script '('dns-nsec-enum')' --script '('rmi-*')' --script '(memcached-info)' --script '('docker-*')'  "
        ." --script '(http-open-proxy)' --script '(ftp-*)' --script '(rsync-list-modules)' --script '(mysql-brute)' --script-args '(mysql-brute.timeout=8h,brute.firstonly=1)' --script '(mysql-empty-password)' "
        ." --script '(smb-os-discovery)' --script '(redis-brute)' --script '(amqp-info)' --script '(nfs-ls)' --script '(svn-brute)' --script-args '(svn-brute.timeout=8h,brute.firstonly=1)' "
        ." --script '(smb-brute)' --script-args '(smb-brute.timeout=8h,brute.firstonly=1)' --script '(ms-sql-brute)' --script-args '(ms-sql-brute.timeout=8h,brute.firstonly=1)' "
        ." --script '(http-default-accounts)' --script-args '(http-default-accounts.fingerprintfile=/configs/nmap/nmap-fingerprints.lua,http-default-accounts.timeout=24h)'  "
        ." --script '(pgsql-brute)' --script-args '(pgsql-brute.timeout=8h,brute.firstonly=1)' --script '(smb-protocols)' --script '('fcrdns')' -sC"; */

        /*$scripts = " -A --script mongo* --script default --script dns-zone-transfer --script dns-nsec-enum --script rmi-* --script memcached-info --script docker-*  "
        ." --script http-open-proxy --script ftp-* --script rsync-list-modules --script mysql-empty-password --script smb-enum-shares "
        ." --script smb-os-discovery --script amqp-info --script nfs-ls "
        ." --script http-default-accounts --script-args http-default-accounts.fingerprintfile=/configs/nmap/nmap-fingerprints.lua,http-default-accounts.timeout=24h "
        ." --script smb-protocols --script fcrdns -sC";*/

        /*$scripts = " -A --script ajp-brute --script-args ajp-brute.timeout=8h,brute.firstonly=1 --script ftp-brute --script-args ftp-brute.timeout=6h --script *vnc* --script-args vnc-brute.timeout=8h,brute.firstonly=1 --script mongo* --script default --script dns-zone-transfer --script dns-nsec-enum --script rmi-* --script memcached-info --script docker-*  --script smb-enum-shares "
        ." --script http-open-proxy --script ftp-* --script rsync-list-modules --script mysql-brute --script-args mysql-brute.timeout=8h,brute.firstonly=1 --script mysql-empty-password "
        ." --script smb-os-discovery --script redis-brute --script amqp-info --script nfs-ls --script svn-brute --script-args svn-brute.timeout=8h,brute.firstonly=1 "
        ." --script smb-brute --script-args smb-brute.timeout=8h,brute.firstonly=1 --script ms-sql-brute --script-args ms-sql-brute.timeout=8h,brute.firstonly=1 "
        ." --script http-default-accounts --script-args http-default-accounts.fingerprintfile=/configs/nmap/nmap-fingerprints.lua,http-default-accounts.timeout=24h  "
        ." --script pgsql-brute --script-args pgsql-brute.timeout=8h,brute.firstonly=1 --script smb-protocols --script fcrdns -sC";*/

        $scripts = " -A --script default --script dns-zone-transfer --script dns-nsec-enum --script memcached-info --script docker-* --script mysql-audit --script-args \"mysql-audit.username='root',mysql-audit.password='root'\" "
        ."  --script ftp-anon --script rsync-list-modules --script mysql-empty-password --script smb-enum-shares --script=nfs-ls.nse,nfs-showmount.nse,nfs-statfs.nse"
        ."  --script amqp-info --script nfs-ls --script-args http-default-accounts.fingerprintfile=/configs/nmap/nmap-fingerprints.lua,http-default-accounts.timeout=160h "
        ."  --script fcrdns -sC";
// --net=container:vpn1 --expose=53 -p 53:53 я их все равно не смотрю --script http-default-accounts --script http-open-proxy --script smb-protocols
        

    //--script http-default-accounts --script-args http-default-accounts.fingerprintfile=/configs/nmap/nmap-fingerprints.lua,http-default-accounts.timeout=24h --script *vnc* --script-args vnc-brute.timeout=8h,brute.firstonly=1 
        //--script rsync-brute --script-args userdb=/configs/passwords/users,passdb=/configs/passwords/passwords --script ajp-brute


// --min-hostgroup 4000
        exec("sudo docker run --cpu-shares 512 --rm --privileged=true -v configs:/configs/ -v dockerresults:/dockerresults instrumentisto/nmap --privileged"
            ." -g 80 -T3 -v -sV --version-all --randomize-hosts -n -sS  "
            ." -p T:1-34000 --min-hostgroup 1024 --max-hostgroup 4096 "
            ." --script-timeout 10000m --host-timeout 50000m --max-scan-delay 10s --max-retries 3 --open -oX "
            . $nmapoutputxml . " -oA /dockerresults/" . $randomid . "nmap --stylesheet /configs/nmap/nmap.xsl -R " . $scripts . " -iL " . $scanIPS . " >> /dockerresults/nmapout.txt 2>&1 " );
        //-sU ,U:11211 слишком много фолзов


        /*exec("sudo docker run --rm --cpu-shares 512 --privileged=true --ulimit nofile=1048576:1048576 --network host -v configs:/configs/ -v dockerresults:/dockerresults --expose=53 -p 53:53 projectdiscovery/naabu -exclude-cdn -c 35 -rate 1000 -timeout 10000 -p 1-32000 -list " 
            . $scanIPS . " -output " . $outputtxt ." -nmap-cli 'nmap -Pn -v -sV --min-hostgroup 500 --script-timeout 8000m --host-timeout 40000m --max-scan-delay 20s --max-retries 3 --open -oX "
            . $nmapoutputdir . "{{ip}}.xml --stylesheet /configs/nmap/nmap.xsl -R " . $scripts . "'  >> /dockerresults/out.txt 2>&1 ");*/
    
        /*exec("sudo /root/bin/bin/naabu -exclude-cdn -c 200 -rate 500 -timeout 15000 -p 1-32000 -list " 
            . $scanIPS . " -output " . $outputtxt ." -nmap-cli 'nmap -g 53 -T4 -Pn -v -sV --randomize-hosts --send-eth -sS --min-hostgroup 1000 --script-timeout 8000m --host-timeout 40000m --max-scan-delay 20s --max-retries 3 --open -oX "
            . $nmapoutputxml . " --stylesheet /configs/nmap/nmap.xsl -R " . $scripts . "'  >> /dockerresults/out.txt 2>&1 ");*/

        exec("echo " . $scanIPS . " >> /dockerresults/out.txt ");

        //exec("python3 /configs/nmap/nmapmerger.py -d ". $nmapoutputdir ." -o ". $nmapoutputxml);

        exec("sudo /usr/bin/xsltproc -o " . $nmapoutputhtml . " /configs/nmap/nmap.xsl " . $nmapoutputxml . "");

        if (file_exists($nmapoutputhtml)) {
            $output = file_get_contents($nmapoutputhtml);

            $taskid = nmap::saveToDB($taskid, $output); //if no taskid supplied by user create task and return its taskid

            aquatone::aquatone($taskid, $nmapoutputxml, $input["queueid"]);

        } else return 2;

        return 1;//exec("sudo rm -r /dockerresults/" . $randomid . "nmap*");
    }





        //sudo nohup sudo nmap -sS -T3 -p- -A --host-timeout 4000m --source-port 2002 --script-timeout 1500m -sC -oA /root/scan2 --stylesheet /root/project/docker/conf/configs/nmap.xsl --script=http-brute --script-args http-wordpress-brute.threads=1,brute.threads=1,brute.delay=2,unpwdb.timelimit=0,brute.firstonly=1 --script http-wordpress-brute --script http-open-proxy --script ftp-* --script rsync-list-modules --script mysql-* --script smb-os-discovery --script nfs-ls --script redis-brute --script-args http-default-accounts.fingerprintfile=/root/project/docker/conf/configs/nmap-fingerprints.lua --script-args http-default-accounts.fingerprintfile=/configs/nmap-fingerprints.lua --script ms-sql-brute --script pgsql-brute --script smb-protocols -sC > /root/nmap2.txt & 


        //sudo /usr/bin/xsltproc -o /root/scan3.html /root/project/docker/conf/configs/nmap.xsl /root/scan3.xml

//docker run --cpu-shares 256 --rm --net=host --privileged=true --expose=53 -p=53 -v configs:/configs/ -v dockerresults:/dockerresults instrumentisto/nmap --privileged -sS -g 53 -sU -T4 --randomize-hosts -Pn -sV  -p T:1-65000,U:53,U:111,U:137,U:161,U:162,U:500,U:1434,U:5060,U:11211,U:67-69,U:123,U:135,U:138,U:139,U:445,U:514,U:520,U:631,U:1434,U:1900,U:4500,U:5353,U:49152 -A -R --min-hostgroup 5000 --script-timeout 2000m --max-scan-delay 10s --max-retries 5 --open --host-timeout 2500m --stylesheet /configs/nmap.xsl -R --script=http-brute --script=ajp-brute --script=ftp-brute --script='vnc-info,realvnc-auth-bypass,vnc-title,vnc-brute' --script=svn-brute --script=smb-brute --script-args http-wordpress-brute.threads=1,ajp-brute.timeout=2h,ftp-brute.timeout=2h,vnc-brute.timeout=2h,svn-brute.timeout=2h,smb-brute.timeout=2h,ms-sql-brute.timeout=2h,pgsql-brute.timeout=2h,mysql-brute.timeout=2h,http-brute.timeout=10h,brute.delay=1,unpwdb.timelimit=2h,brute.firstonly=1 --script amqp-info  --script 'mongo* and default' --script 'dns*' --script http-open-proxy --script 'ftp-*' --script rsync-list-modules --script mysql-brute --script mysql-empty-password --script smb-os-discovery --script nfs-ls --script redis-brute  --script http-default-accounts --script-args http-default-accounts.fingerprintfile=/configs/nmap-fingerprints.lua,http-default-accounts.timeout=10h --script ms-sql-brute  --script pgsql-brute --script smb-protocols --script 'rmi-*' --script memcached-info --script 'docker-*' --script amqp-info -sC 
//docker run --cpu-shares 512 --rm --net=host --privileged=true --expose=53 -p=53 -v configs:/configs/ -v dockerresults:/dockerresults instrumentisto/nmap --privileged -sS -g 53 -sU -T4 --randomize-hosts -Pn -sV  -p T:1-65000,U:53,U:111,U:137,U:161,U:162,U:500,U:1434,U:5060,U:11211,U:67-69,U:123,U:135,U:138,U:139,U:445,U:514,U:520,U:631,U:1434,U:1900,U:4500,U:5353,U:49152 -A -R --min-hostgroup 5000 --script-timeout 2000m --max-scan-delay 10s --max-retries 5 --open --host-timeout 2500m --stylesheet /configs/nmap.xsl -R --script=http-brute --script=ajp-brute --script=ftp-brute --script='vnc-info,realvnc-auth-bypass,vnc-title,vnc-brute' --script=svn-brute --script=smb-brute --script-args http-wordpress-brute.threads=1,ajp-brute.timeout=2h,ftp-brute.timeout=2h,vnc-brute.timeout=2h,svn-brute.timeout=2h,smb-brute.timeout=2h,ms-sql-brute.timeout=2h,pgsql-brute.timeout=2h,mysql-brute.timeout=2h,http-brute.timeout=10h,brute.delay=1,unpwdb.timelimit=2h,brute.firstonly=1 --script amqp-info  --script 'mongo* and default' --script 'dns*' --script http-open-proxy --script 'ftp-*' --script rsync-list-modules --script mysql-brute --script mysql-empty-password --script smb-os-discovery --script nfs-ls --script redis-brute  --script http-default-accounts --script-args http-default-accounts.fingerprintfile=/configs/nmap-fingerprints.lua,http-default-accounts.timeout=10h --script ms-sql-brute  --script pgsql-brute --script smb-protocols --script 'rmi-*' --script memcached-info --script 'docker-*' --script amqp-info -sC 

}
