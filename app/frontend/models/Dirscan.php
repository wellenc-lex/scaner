<?php

namespace frontend\models;

use yii\db\ActiveRecord;

class Dirscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public static function dirscan($input)
    {

                $url = $input["url"];

                $url = rtrim($url, '/');
                $url = rtrim($url, '/');

                $url = ltrim($url, ' ');
                $url = rtrim($url, ' ');

                $url = str_replace(",", " ", $url);
                $url = str_replace("\r", " ", $url);
                $url = str_replace("\n", " ", $url);
                $url = str_replace("|", " ", $url);
                $url = str_replace("&", " ", $url);
                $url = str_replace("&&", " ", $url);
                $url = str_replace(">", " ", $url);
                $url = str_replace("<", " ", $url);
                
                $url = str_replace("'", " ", $url);
                $url = str_replace("\"", " ", $url);
                $url = str_replace("\\", " ", $url);

                $url = strtolower($url);

                $taskid = $input["taskid"];

                $randomid = rand(1, 1000000);

                if (!isset($input["ip"])) {

                    /*$start_jsscan = "/usr/bin/python /var/www/soft/linkfinder/linkfinder.py -i " . escapeshellarg($url) .
                        " -d -o=/var/www/output/jsscan/scan" . $randomid . ".html  >/dev/null";*/
                        ////Turned off because burp's is better.

                    $start_dirscan = "sudo docker run -v dockerresults:/dockerresults --rm 5631/dirsearch -u " . escapeshellarg($url) . " -e php,asp --json-report=/dockerresults/dirscan" . $randomid . ".json --random-agents -b --suppress-empty -F -t 2 -s 2 >/dev/null ";

                }

                if (isset($input["ip"])) {

                    /*$start_jsscan = "/usr/bin/python /var/www/soft/linkfinder/linkfinder.py -i " . escapeshellarg($url) .
                        " -d -o=/var/www/output/jsscan/scan" . $randomid . ".html  >/dev/null";*/

                        //Turned off because burp's is better.

                    $start_dirscan = "sudo docker run -v dockerresults:/dockerresults --rm 5631/dirsearch -u " . escapeshellarg($url) . " -e php,asp --json-report=/dockerresults/dirscan" . $randomid . ".json --random-agents --suppress-empty -F -t 1 -s 3 --ip=" . escapeshellarg($input["ip"]) . " >/dev/null ";

                }

                $start_wayback = "python2 /var/www/soft/wayback/wayback.py pull --host " . escapeshellarg($url) .
                    " | python2 /var/www/soft/wayback/wayback.py check --outputfile /var/www/output/wayback/" . $randomid . ".json >/dev/null";

                //system($start_jsscan);
                system($start_dirscan);
                //system($start_wayback);

                /*if (file_exists("/var/www/output/jsscan/scan" . $randomid . ".html")) {
                    $outputjs = file_get_contents("/var/www/output/jsscan/scan" . $randomid . ".html");
                } else */
                $outputjs = "None.";

                if (file_exists("/dockerresults/dirscan" . $randomid . ".json")) {
                    $output = file_get_contents("/dockerresults/dirscan" . $randomid . ".json");
                } else $output = "None.";

                if (file_exists("/var/www/output/wayback/" . $randomid . ".json")) {
                    $output_wayback = file_get_contents("/var/www/output/wayback/" . $randomid . ".json");
                    $output_wayback = str_replace("}{", "},{", $output_wayback);
                    $output_wayback = '[' . $output_wayback . ']';
                } else $output_wayback = "None.";

                $date_end = date("Y-m-d H-i-s");

                $dirscan = Tasks::find()
                    ->where(['taskid' => $taskid])
                    ->limit(1)
                    ->one();

                $dirscan->dirscan_status = "Done.";
                $dirscan->dirscan = $output;
                $dirscan->wayback = $output_wayback;
                $dirscan->date = $date_end;
                $dirscan->js = $outputjs;
                //system("sudo /usr/bin/find /var/www/output/jsscan -name 'scan$randomid.html' -delete");

                $a = "Done";
                $dirscan->save();

                system("rm /dockerresults/dirscan" . $randomid . ".json");
                return 1;

    }

}


