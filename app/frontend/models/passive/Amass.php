<?php

namespace frontend\models\passive;

use Yii;
use frontend\models\Queue;
use frontend\models\PassiveScan;
use yii\db\ActiveRecord;

class Amass extends ActiveRecord
{
    public static function tableName()
    {
        return 'passive_scan';
    }

    /**
     * 0 == no changes between scans
     * 1 == previous != new information, needs diff.
    */

    public static function amassscan($input)
    {
        $changes = 0;

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

        $randomid = rand(10000, 1000000);
        htmlspecialchars($url);

        $command = "sudo docker run --cpu-shares 256 --rm -v configs:/configs/ -v dockerresults:/dockerresults caffix/amass enum -w /wordlists/all.txt -d  " . escapeshellarg($url) . " -json /dockerresults/" . $randomid . "amass.json -active -brute -ip -timeout 1200 -config /configs/amass.ini";

        exec($command);

        if (file_exists("/dockerresults/" . $randomid . "amass.json")) {
            $fileamass = file_get_contents("/dockerresults/" . $randomid . "amass.json");
        } else {
            sleep(180);
            exec($command);
            $fileamass = file_get_contents("/dockerresults/" . $randomid . "amass.json");
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

            return 0; // no changes between scans

        } elseif ($amass->amass_new != "") { //latest scan info in DB

            if ($amassoutput === $amass->amass_new) {
                $changes =  0; // no changes between scans
            } else {
                $changes =  1; // check changes between scans

                $queue = new Queue();
                $queue->taskid = $scanid;
                $queue->instrument = 4; //gitscan
                $queue->passivescan = 1;
                $queue->save();
            }

            $amass->amass_previous = $amass->amass_new;
            $amass->amass_new = $amassoutput;
            $amass->save();
        }

        return $changes;
    }

}