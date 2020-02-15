<?php

namespace frontend\models\passive;

use frontend\models\PassiveScan;
use yii\db\ActiveRecord;

class Amass extends ActiveRecord
{
    public static function tableName()
    {
        return 'passive_scan';
    }

    public static function amassscan($input)
    {
        $amass_returncode = 1;
        $url = $input["url"];
        $scanid = $input["scanid"];

        $url = ltrim($url, ' ');
        $url = rtrim($url, '/');
        $url = rtrim($url, ' ');

        $url = strtolower($url);
        $url = str_replace("http://", "", $url);
        $url = str_replace("https://", "", $url);
        $url = str_replace("www.", "", $url);
        $url = str_replace(" ", ",", $url);
        $url = str_replace(",", ",", $url);
        $url = str_replace("\r", ",", $url);
        $url = str_replace("\n", ",", $url);
        $url = str_replace("|", " ", $url);
        $url = str_replace("&", " ", $url);
        $url = str_replace("&&", " ", $url);
        $url = str_replace(">", " ", $url);

        $url = rtrim($url, '/');

        $randomid = rand(1, 1000000);
        htmlspecialchars($url);

        $command = "sudo /snap/bin/amass -d  " . escapeshellarg($url) . " -json /root/" . $randomid . ".json -active -brute -ip -min-for-recursive 1 -dir /tmp/ " . $randomid . " -config /var/www/soft/amassconfig.ini > /dev/null && sudo mv /root/" . $randomid . ".json /var/www/output/amass/" . $randomid . ".json";

        $escaped_command = ($command);

        system($escaped_command, $amass_returncode);

        if (file_exists("/var/www/output/amass/" . $randomid . ".json")) {
            $fileamass = file_get_contents("/var/www/output/amass/" . $randomid . ".json");
        } else $fileamass="Error";

        $fileamass = str_replace("}
{\"Timestamp\"", "},{\"Timestamp\"", $fileamass);

        $amassoutput = '[' . $fileamass . ']';

        $amass = PassiveScan::find()
            ->where(['scanid' => $scanid])
            ->limit(1)
            ->one();

        if ($amass->amass_new == "") {
            $amass->amass_new = $amassoutput;

            $amass->save();

            return 0; // no diffs

        } elseif ($amass->amass_new != "") {

            $amass->amass_previous = $amass->amass_new;
            $amass->amass_new = $amassoutput;

            $amass->save();


            if ($amass->amass_previous == $amass->amass_new) {
                return 0; // no diffs
            } else {
                return 1; // check diffs
            }
        }
    }


}





