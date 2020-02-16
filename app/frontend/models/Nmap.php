<?php

namespace frontend\models;

use yii\db\ActiveRecord;

class Nmap extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public static function scanhost($input)
    {

                $url = $input["url"];
                $taskid = $input["taskid"];

                $url = strtolower($url);
                $url = str_replace("http://", " ", $url);
                $url = str_replace("https://", " ", $url);
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

                $randomid = rand(1, 1000000);

                // sudo nohup sudo /usr/bin/nmap -sS -T3 -p- -A --host-timeout 4000m --source-port 22 --script-timeout 1500m -sC -oA /var/www/output/nmap/scan1 --stylesheet /var/www/soft/nmap.xsl --script smb-brute --script ftp-anon --script mysql-empty-password --script smb-os-discovery &

                //sudo /usr/bin/xsltproc -o /var/www/output/nmap/scan1.html /var/www/soft/nmap.xsl /var/www/output/nmap/scan.xml
                
                $scripts = " --script=ftp-anon --script=mysql-empty-password --script=smb-os-discovery --script=mysql-empty-password ".
                    "--script=nfs-ls --script-args='brute.delay=3,brute.firstonly=1' ";


                //" . escapeshellarg($url) . " Gives Failed to resolve " $url ". , don't know how to fix, left it as is.
            
                system("sudo docker run --rm -v configs:/root/configs/ -v dockerresults:/dockerresults instrumentisto/nmap -sS -T4 -p- -A --host-timeout 4000m --source-port 22 --script-timeout 1500m -sC --max-rtt-timeout 1500ms -oX /dockerresults/scan" .
                    $randomid . ".xml --stylesheet /root/configs/nmap.xsl -R " . $scripts . $url );

                system("sudo /usr/bin/xsltproc -o /dockerresults/nmap/" . $randomid . ".html /root/configs/nmap.xsl /dockerresults/scan" . $randomid . ".xml ");

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

                system("rm /dockerresults/scan" . $randomid . ".xml && rm /dockerresults/nmap/" . $randomid . ".html");

                $a = "Done";
                $nmap->save();

                return 1;

    }

}


