<?php

namespace frontend\models;

use yii\db\ActiveRecord;

class Nmap extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public function ParseHostname($url)
    {
        $url = strtolower($url);

        preg_match_all("/(https?:\/\/)*([\w\:\.]*)/i", $url, $domains); 

        foreach ($domains[2] as $domain) {
            if ($domain != "") $hostname = $hostname." ".$domain;
        }
        
        return $hostname;
    }

    public static function scanhost($input)
    {
        $url = nmap::ParseHostname($input["url"]);
        $taskid = (int) $input["taskid"];

        $randomid = $taskid;



        // sudo nohup sudo nmap -sS -T4 -p- -A --host-timeout 4000m --source-port 22 --script-timeout 1500m -sC -oA /root/scan1 --stylesheet /root/project/docker/conf/configs/nmap.xsl --script=ftp-anon --script=mysql-empty-password --script=smb-os-discovery --script=mysql-empty-password --script=nfs-ls --script-args='brute.delay=2,brute.firstonly=1' --script smb-enum* --script smb-ls --script smb-os-discovery --script smb-s* --script smb-vuln* --script http-default-accounts --script-args http-default-accounts.fingerprintfile=project/docker/conf/configs/nmap-fingerprints.lua > /root/nmap.txt &

        // sudo xsltproc -o /root/scanresult1.html /root/project/docker/conf/configs/nmap.xsl /root/scan1.xml
                
        $scripts = " --script=ftp-anon --script=mysql-empty-password --script=smb-os-discovery --script=mysql-empty-password ".
                    "--script=nfs-ls --script-args='brute.delay=2,brute.firstonly=1' --script http-default-accounts --script-args http-default-accounts.fingerprintfile=/configs/nmap-fingerprints.lua";


        //" . escapeshellarg($url) . " Gives Failed to resolve " $url ". , don't know how to fix, left it as is.
            
        exec("sudo docker run --rm -v configs:/configs/ -v dockerresults:/dockerresults instrumentisto/nmap -sS -T4 -sU -p- -A --host-timeout 4000m --source-port 22 --script-timeout 1500m -sC --max-rtt-timeout 1500ms -oX /dockerresults/scan" . $randomid . ".xml --stylesheet /configs/nmap.xsl -R " . $scripts . $url );

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
