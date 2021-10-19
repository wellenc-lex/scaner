<?php

namespace frontend\models;

use yii\db\ActiveRecord;
use frontend\models\Dirscan;
require_once 'Dirscan.php';

class Nmap extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public static function scanhost($input)
    {
        $url = dirscan::ParseHostname($input["url"]);
        $taskid = (int) $input["taskid"];

        $randomid = $taskid;

        //sudo nohup sudo nmap -sS -T3 -p- --open -iL sort.txt -oG - > /root/outputnmap.txt &


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
            
        exec("sudo docker run --cpu-shares 512 --rm -v configs:/configs/ -v dockerresults:/dockerresults instrumentisto/nmap -sX -T3 -p- -A --host-timeout 4000m --source-port 2002 --script-timeout 1500m -sC --max-rtt-timeout 1500ms -g 2002 -oX /dockerresults/scan" . $randomid . ".xml --stylesheet /configs/nmap.xsl -R " . $scripts . $url );

        exec("sudo /usr/bin/xsltproc -o /dockerresults/nmap/" . $randomid . ".html /configs/nmap.xsl /dockerresults/scan" . $randomid . ".xml ");

        if (file_exists("/dockerresults/nmap/" . $randomid . ".html")) {
            $output = file_get_contents("/dockerresults/nmap/" . $randomid . ".html");
        } else $output = "None.";

        $date_end = date("Y-m-d H-i-s");

        $nmap = Tasks::find()
            ->where(['taskid' => $taskid])
            ->limit(1)
            ->one();

        $nmap->nmap_status = "Done.";
        $nmap->nmap = $output;
        $nmap->date = $date_end;

        $a = "Done";
        $nmap->save();

        exec("sudo rm /dockerresults/scan" . $randomid . ".xml && sudo rm /dockerresults/nmap/" . $randomid . ".html");

        return 1;
    }

}
