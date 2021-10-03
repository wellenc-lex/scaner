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


        //brute default passwords -> default-http-login-hunter.sh urls.txt https://github.com/InfosecMatter/default-http-login-hunter

        //--script mysql-brute -p3306  --script-args userdb=users.txt, passdb=passwords.txt



        //sudo nohup sudo nmap -sS -T3 -p- -A --host-timeout 4000m --source-port 22 --script-timeout 1500m -sC -oA /root/scan2 --stylesheet /root/project/docker/conf/configs/nmap.xsl --script http-open-proxy --script ftp-* --script rsync-list-modules --script mysql-* --script smb-os-discovery --script nfs-ls --script-args='brute.delay=2,brute.firstonly=1' --script smb-enum* --script smb-ls --script smb-os-discovery  --script smb-s* --script smb-vuln* --script ms-sql-brute --script redis-brute --script pgsql-brute --script smb-protocols -sC --script-args http-default-accounts.fingerprintfile=/root/project/docker/conf/configs/nmap-fingerprints.lua > /root/nmap2.txt & 

        //-g 22
        
        //sudo /usr/bin/xsltproc -o /root/scan1.html /root/project/docker/conf/configs/nmap.xsl /root/scan1.xml
                
        $scripts = " --script http-open-proxy --script ftp-* --script rsync-list-modules --script mysql-* --script smb-os-discovery --script nfs-ls --script redis-brute".
                    "--script-args='brute.delay=2,brute.firstonly=1' --script-args http-default-accounts.fingerprintfile=/configs/nmap-fingerprints.lua --script ms-sql-brute --script pgsql-brute --script smb-protocols -sC";


        //" . escapeshellarg($url) . " Gives Failed to resolve " $url ". , don't know how to fix, left it as is.
            
        exec("sudo docker run --cpu-shares 512 --rm -v configs:/configs/ -v dockerresults:/dockerresults instrumentisto/nmap -sX -T3 -p- -A --host-timeout 4000m --source-port 22 --script-timeout 1500m -sC --max-rtt-timeout 1500ms -g 22 -oX /dockerresults/scan" . $randomid . ".xml --stylesheet /configs/nmap.xsl -R " . $scripts . $url );

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
