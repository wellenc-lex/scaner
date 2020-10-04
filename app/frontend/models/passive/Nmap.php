<?php

namespace frontend\models\passive;

use frontend\models\PassiveScan;
use yii\db\ActiveRecord;

class Nmap extends ActiveRecord
{
    public static function tableName()
    {
        return 'passive_scan';
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
        $taskid = (int) $input["scanid"];

        $randomid = $taskid;

        //sudo nohup sudo nmap -sS -T4 -p- -A --host-timeout 4000m --source-port 22 --script-timeout 1500m -sC -oA /root/scan20 --stylesheet /root/project/docker/conf/configs/nmap.xsl --script=ftp-anon --script=mysql-empty-password --script=smb-os-discovery --script=mysql-empty-password --script=nfs-ls --script-args='brute.delay=2,brute.firstonly=1' --script smb-enum* --script smb-ls --script smb-os-discovery --script smb-s* --script smb-vuln* --script http-default-accounts --script-args http-default-accounts.fingerprintfile=project/docker/conf/configs/nmap-fingerprints.lua > /root/nmap1.txt &

        //sudo /usr/bin/xsltproc -o /root/scan1.html /var/www/soft/nmap.xsl /root/scan1.xml

        //sudo /usr/bin/xsltproc -o /root/scan1.html /root/project/docker/conf/configs/nmap.xsl /root/scan1.xml
                
        $scripts = " --script=ftp-anon --script=mysql-empty-password --script=smb-os-discovery --script=mysql-empty-password ".
                    "--script=nfs-ls --script-args='brute.delay=2,brute.firstonly=1' --script http-default-accounts --script-args http-default-accounts.fingerprintfile=/configs/nmap-fingerprints.lua";


        //" . escapeshellarg($url) . " Gives Failed to resolve " $url ". , don't know how to fix, left it as is.
            
        exec("sudo docker run --rm -v configs:/configs/ -v dockerresults:/dockerresults instrumentisto/nmap -sS -T4 -p- -A --host-timeout 4000m --source-port 3550 --script-timeout 1500m -sC --max-rtt-timeout 1500ms -oX /dockerresults/scan" . $randomid . ".xml --stylesheet /configs/nmap.xsl -R " . $scripts . $url );

        exec("sudo /usr/bin/xsltproc -o /dockerresults/nmap/" . $randomid . ".html /configs/nmap.xsl /dockerresults/scan" . $randomid . ".xml ");

        if (file_exists("/dockerresults/nmap/" . $randomid . ".html")) {
            $output = file_get_contents("/dockerresults/nmap/" . $randomid . ".html");
        } else $output = "None.";

        $nmap = PassiveScan::find()
            ->where(['PassiveScanid' => $scanid])
            ->limit(1)
            ->one();

        if ($nmap->nmap_new == "") {
            $nmap->nmap_new = $output;

            $nmap->save();

            return 0; // no diffs

        } elseif ($nmap->nmap_new != "") {

            $nmap->nmap_previous = $nmap->nmap_new;
            $nmap->nmap_new = $output;

            $nmap->save();

            /**
            * 0 == no diffs required
            * 1 == previous != new information, needs diff.
            */
            if ($nmap->nmap_previous == $nmap->nmap_new) {
                return 0; // no diffs
            } else {
                return 1; // check diffs
            }

        }

        exec("sudo rm /dockerresults/scan" . $randomid . ".xml && sudo rm /dockerresults/nmap/" . $randomid . ".html");

        return 1;
    }
}