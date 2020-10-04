<?php

namespace frontend\models\passive;

use Yii;
use frontend\models\PassiveScan;
use yii\db\ActiveRecord;

class Amass extends ActiveRecord
{
    public static function tableName()
    {
        return 'passive_scan';
    }

    /**
     * 0 == no diffs required
     * 1 == previous != new information, needs diff.
     */

    public static function amassscan($input)
    {
        $changes = 0;

        $amass_returncode = 1;        
        $url = $input["url"];        
        $taskid = $input["scanid"];

        $url = ltrim($url, ' ');
        $url = rtrim($url, '/');
        $url = rtrim($url, ' ');

        $url = strtolower($url);
        $url = str_replace("http://", "", $url);
        $url = str_replace("https://", "", $url);
        $url = str_replace("www.", "", $url);
        $url = str_replace(" ", ",", $url);
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

        $url = rtrim($url, '/');

        $randomid = rand(1, 1000000);;
        htmlspecialchars($url);

        $command = "sudo docker run --rm -v configs:/configs/ -v dockerresults:/dockerresults caffix/amass enum -w /wordlists/all.txt -d  " . escapeshellarg($url) . " -json /dockerresults/amass" . $randomid . ".json -active -brute -ip -config /configs/amass.ini";

        exec($command);

        if (file_exists("/dockerresults/amass" . $randomid . ".json")) {
            $fileamass = file_get_contents("/dockerresults/amass" . $randomid . ".json");
        }

        $fileamass = str_replace("}
{\"Timestamp\"", "},{\"Timestamp\"", $fileamass);

        $fileamass = str_replace("} {", "},{", $fileamass);

        $fileamass = str_replace("}
{", "},{", $fileamass);

        $fileamass = str_replace("}
{\"name\"", "},{\"name\"", $fileamass);

        $amassoutput = '[' . $fileamass . ']';

        $amass = PassiveScan::find()
            ->where(['PassiveScanid' => $scanid])
            ->limit(1)
            ->one();

        if ($amass->amass_new == "") {
            $amass->amass_new = $amassoutput;

            $amass->save();

            return 0; // no diffs

        } elseif ($amass->amass_new != "") {

            if ($amassoutput === $amass->amass_new) {
                $changes =  0; // no diffs
            } else {
                $changes =  1; // check diffs
            }

            $amass->amass_previous = $amass->amass_new;
            $amass->amass_new = $amassoutput;

            $amass->save();
        }

        return $changes;
    }

}