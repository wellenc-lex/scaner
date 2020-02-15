<?php


namespace frontend\models\passive;

use frontend\models\PassiveScan;
use yii\db\ActiveRecord;

class Dirscan extends ActiveRecord
{
    public static function tableName()
    {
        return 'passive_scan';
    }

    /**
     * 0 == no diffs required
     * 1 == previous != new information, needs diff.
     */

    public static function dirscan($input)
    {
        $url = $input["url"];

        $url = rtrim($url, '/');
        $url = rtrim($url, '/');

        $url = ltrim($url, ' ');
        $url = rtrim($url, ' ');

        $url = str_replace(",", "", $url);
        $url = str_replace("\r", "", $url);
        $url = str_replace("\n", "", $url);
        $url = str_replace("|", "", $url);
        $url = str_replace("&", "", $url);
        $url = str_replace("&&", "", $url);
        $url = str_replace(">", "", $url);

        htmlspecialchars($url);

        $url = strtolower($url);

        $scanid = $input["scanid"];

        $randomid = rand(1, 1000000);

        $command = "/usr/bin/python3 /var/www/soft/dirsearch/dirsearch.py -u " . escapeshellarg($url) . " -e php --json-report=/var/www/output/dirscan/scan" . $randomid . ".json --random-agents -F -t 2 -b >/dev/null ";

        $command2= "sudo /usr/bin/find /var/www/output/dirscan -name 'scan$randomid.json' -delete";

        $escaped_command = ($command);

        system($escaped_command, $dirscan_returncode);

        if (file_exists("/var/www/output/dirscan/scan" . $randomid . ".json")) {
            $output = file_get_contents("/var/www/output/dirscan/scan" . $randomid . ".json");
        } else $output = "None.";

        $dirscan = PassiveScan::find()
            ->where(['scanid' => $scanid])
            ->limit(1)
            ->one();

        if ($dirscan->dirscan_new == "") {
            $dirscan->dirscan_new = $output;

            system($command2);

            $dirscan->save();

            return 0; // no diffs

        } elseif ($dirscan->dirscan_new != "") {

            $dirscan->dirscan_previous = $dirscan->dirscan_new;
            $dirscan->dirscan_new = $output;

            $dirscan->save();

            system($command2);

            if ($dirscan->dirscan_previous == $dirscan->dirscan_new) {
                return 0; // no diffs
            } else {
                return 1; // check diffs
            }

        }


    }


}
