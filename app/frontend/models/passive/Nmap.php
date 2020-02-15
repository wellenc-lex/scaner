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

    /**
     * 0 == no diffs required
     * 1 == previous != new information, needs diff.
     */

    public static function scanhost($input)
    {

        $url = $input["url"];
        $scanid = $input["scanid"];

        $url = strtolower($url);
        $url = str_replace("http://", " ", $url);
        $url = str_replace("https://", " ", $url);
        $url = str_replace(",", "  ", $url);
        $url = str_replace("\r", " ", $url);
        $url = str_replace("\n", " ", $url);
        $url = str_replace("|", " ", $url);
        $url = str_replace("&", " ", $url);
        $url = str_replace("&&", " ", $url);
        $url = str_replace(">", " ", $url);
        $url = str_replace("/", " ", $url);

        $randomid = rand(1, 1000000);

        htmlspecialchars($url);

        $command = "sudo /usr/bin/nmap -sS -T3 -A -p- --host-timeout 2000m --source-port 22 --script-timeout 900m -sC -oA /var/www/output/nmap/scan" . $randomid . " --stylesheet /var/www/soft/nmap.xsl  --script smb-brute --script smb-os-discovery  " . $url . "  2>/var/www/output/nmap/scan" . $randomid . ".err";
//-p-
        $command1 = "sudo /usr/bin/xsltproc -o /var/www/output/nmap/scan" . $randomid . ".html /var/www/soft/nmap.xsl /var/www/output/nmap/scan" . $randomid . ".xml  2>/var/www/output/nmap/scan" . $randomid . ".xml.err  ";

        $command2 = "sudo /usr/bin/find /var/www/output/nmap/ -name 'scan$randomid.*' -delete &";

        $escaped_command = ($command);

        system($escaped_command, $nmap_returncode);

        system($command1);

        if (file_exists("/var/www/output/nmap/scan" . $randomid . ".html")) {
            $output = file_get_contents("/var/www/output/nmap/scan" . $randomid . ".html");
        } else $output = "None.";

        $nmap = PassiveScan::find()
            ->where(['scanid' => $scanid])
            ->limit(1)
            ->one();

        system($command2);

        if ($nmap->nmap_new == "") {
            $nmap->nmap_new = $output;

            $nmap->save();

            return 0; // no diffs

        } elseif ($nmap->nmap_new != "") {

            $nmap->nmap_previous = $nmap->nmap_new;
            $nmap->nmap_new = $output;

            $nmap->save();

            if ($nmap->nmap_previous == $nmap->nmap_new) {
                return 0; // no diffs
            } else {
                return 1; // check diffs
            }

        }


    }


}





