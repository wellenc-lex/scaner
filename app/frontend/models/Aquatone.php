<?php

namespace frontend\models;

use Yii;
use frontend\models\Queue;
use yii\db\ActiveRecord;
use frontend\models\Dirscan;

require_once 'Dirscan.php';

ini_set('max_execution_time', 0);

class Aquatone extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public static function removeEmoji($string){
        return preg_replace('/[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0077}\x{E006C}\x{E0073}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0073}\x{E0063}\x{E0074}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0065}\x{E006E}\x{E0067}\x{E007F})|[\x{1F3F4}](?:\x{200D}\x{2620}\x{FE0F})|[\x{1F3F3}](?:\x{FE0F}\x{200D}\x{1F308})|[\x{0023}\x{002A}\x{0030}\x{0031}\x{0032}\x{0033}\x{0034}\x{0035}\x{0036}\x{0037}\x{0038}\x{0039}](?:\x{FE0F}\x{20E3})|[\x{1F415}](?:\x{200D}\x{1F9BA})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F468})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F468})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9BD})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9AF})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2640}\x{FE0F})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2642}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2695}\x{FE0F})|[\x{1F471}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F9CF}\x{1F647}\x{1F926}\x{1F937}\x{1F46E}\x{1F482}\x{1F477}\x{1F473}\x{1F9B8}\x{1F9B9}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F486}\x{1F487}\x{1F6B6}\x{1F9CD}\x{1F9CE}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}\x{1F9D8}](?:\x{200D}\x{2640}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B0})|[\x{1F471}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F9CF}\x{1F647}\x{1F926}\x{1F937}\x{1F46E}\x{1F482}\x{1F477}\x{1F473}\x{1F9B8}\x{1F9B9}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F486}\x{1F487}\x{1F6B6}\x{1F9CD}\x{1F9CE}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}\x{1F9D8}](?:\x{200D}\x{2642}\x{FE0F})|[\x{1F441}](?:\x{FE0F}\x{200D}\x{1F5E8}\x{FE0F})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FA}](?:\x{1F1FF})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1FA}](?:\x{1F1FE})|[\x{1F1E6}\x{1F1E8}\x{1F1F2}\x{1F1F8}](?:\x{1F1FD})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F9}\x{1F1FF}](?:\x{1F1FC})|[\x{1F1E7}\x{1F1E8}\x{1F1F1}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1FB})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1FB}](?:\x{1F1FA})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FE}](?:\x{1F1F9})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FA}\x{1F1FC}](?:\x{1F1F8})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F7})|[\x{1F1E6}\x{1F1E7}\x{1F1EC}\x{1F1EE}\x{1F1F2}](?:\x{1F1F6})|[\x{1F1E8}\x{1F1EC}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}](?:\x{1F1F5})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EE}\x{1F1EF}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1F8}\x{1F1F9}](?:\x{1F1F4})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1F3})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F4}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FF}](?:\x{1F1F2})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F1})|[\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FD}](?:\x{1F1F0})|[\x{1F1E7}\x{1F1E9}\x{1F1EB}\x{1F1F8}\x{1F1F9}](?:\x{1F1EF})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EB}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F3}\x{1F1F8}\x{1F1FB}](?:\x{1F1EE})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1ED})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1EC})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F9}\x{1F1FC}](?:\x{1F1EB})|[\x{1F1E6}\x{1F1E7}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FB}\x{1F1FE}](?:\x{1F1EA})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1E9})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FB}](?:\x{1F1E8})|[\x{1F1E7}\x{1F1EC}\x{1F1F1}\x{1F1F8}](?:\x{1F1E7})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F6}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}\x{1F1FF}](?:\x{1F1E6})|[\x{00A9}\x{00AE}\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{2328}\x{23CF}\x{23E9}-\x{23F3}\x{23F8}-\x{23FA}\x{24C2}\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}-\x{2615}\x{2618}\x{261D}\x{2620}\x{2622}-\x{2623}\x{2626}\x{262A}\x{262E}-\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{265F}-\x{2660}\x{2663}\x{2665}-\x{2666}\x{2668}\x{267B}\x{267E}-\x{267F}\x{2692}-\x{2697}\x{2699}\x{269B}-\x{269C}\x{26A0}-\x{26A1}\x{26AA}-\x{26AB}\x{26B0}-\x{26B1}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26C8}\x{26CE}-\x{26CF}\x{26D1}\x{26D3}-\x{26D4}\x{26E9}-\x{26EA}\x{26F0}-\x{26F5}\x{26F7}-\x{26FA}\x{26FD}\x{2702}\x{2705}\x{2708}-\x{270D}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2728}\x{2733}-\x{2734}\x{2744}\x{2747}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2763}-\x{2764}\x{2795}-\x{2797}\x{27A1}\x{27B0}\x{27BF}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F0CF}\x{1F170}-\x{1F171}\x{1F17E}-\x{1F17F}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}-\x{1F202}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F23A}\x{1F250}-\x{1F251}\x{1F300}-\x{1F321}\x{1F324}-\x{1F393}\x{1F396}-\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}-\x{1F3F0}\x{1F3F3}-\x{1F3F5}\x{1F3F7}-\x{1F3FA}\x{1F400}-\x{1F4FD}\x{1F4FF}-\x{1F53D}\x{1F549}-\x{1F54E}\x{1F550}-\x{1F567}\x{1F56F}-\x{1F570}\x{1F573}-\x{1F57A}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F590}\x{1F595}-\x{1F596}\x{1F5A4}-\x{1F5A5}\x{1F5A8}\x{1F5B1}-\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}-\x{1F64F}\x{1F680}-\x{1F6C5}\x{1F6CB}-\x{1F6D2}\x{1F6D5}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6EB}-\x{1F6EC}\x{1F6F0}\x{1F6F3}-\x{1F6FA}\x{1F7E0}-\x{1F7EB}\x{1F90D}-\x{1F93A}\x{1F93C}-\x{1F945}\x{1F947}-\x{1F971}\x{1F973}-\x{1F976}\x{1F97A}-\x{1F9A2}\x{1F9A5}-\x{1F9AA}\x{1F9AE}-\x{1F9CA}\x{1F9CD}-\x{1F9FF}\x{1FA70}-\x{1FA73}\x{1FA78}-\x{1FA7A}\x{1FA80}-\x{1FA82}\x{1FA90}-\x{1FA95}]/u', '', $string);
    }

    public static function saveToDB($taskid, $aquatoneoutput)
    {
        if($aquatoneoutput != "[]" && $aquatoneoutput != 'No screenshots'){

            try{
                Yii::$app->db->open();

                $task = new Tasks();
                
                $task->aquatone = $aquatoneoutput;
                $task->aquatone_status = 'Done.';
                $task->nmap_status = "Done.";
                $task->notify_instrument = "1";
                $task->status = 'Done.';
                $task->date = date("Y-m-d H-i-s");

                $task->save(); 

                $taskid = $task->taskid;

            } catch (\yii\db\Exception $exception) {
                var_dump($exception);
                sleep(360);

                aquatone::saveToDB($taskid, $aquatoneoutput);
            }

            return Yii::$app->db->close();
        }
    }

    public static function readaquatone($taskid)
    {

        //html range parameter in template + default page similar

        if (file_exists("/screenshots/" . $taskid . "/aquatone_report.html")) {
            $fileaquatone = file_get_contents("/screenshots/" . $taskid . "/aquatone_report.html");

            $fileaquatone = str_replace('screenshotPath":"screenshots/', 'screenshotPath":"../../screenshots/'.$taskid.'/', $fileaquatone);

            $fileaquatone = str_replace('<img src="screenshots', '<img src="../../screenshots/'.$taskid.'/', $fileaquatone);

            $fileaquatone = str_replace('clustersToShow: 15', 'clustersToShow: 1000', $fileaquatone);

            $fileaquatone = str_replace('clustersToShow += 15', 'clustersToShow += 1000', $fileaquatone);

            $fileaquatone = str_replace('<a href="screenshots', '<a href="../../screenshots/'.$taskid.'/', $fileaquatone);

            $fileaquatone = str_replace('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
    integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">', '<link rel="stylesheet" href="https://bootswatch.com/4/darkly/bootstrap.min.css" crossorigin="anonymous">', $fileaquatone);

            $fileaquatone = str_replace('</html>>', '</html>', $fileaquatone);

            $fileaquatone = str_replace('</ol>d', '</ol>', $fileaquatone);

            $fileaquatone = str_replace('.carousel-item {
      color: #383d41;
      text-align: center;
    }', 

    '.carousel-item {
        color: white !important;
        text-align: center;
        font-size: initial;
    }

    .btn {
        color: #00b5ff !important;
    }', $fileaquatone);

            $fileaquatone = str_replace('.btn-outline-secondary {
    color: #444;
    border-color: #444;', 
    
    '.btn-outline-secondary {
    color: white;
    border-color: white;', $fileaquatone);

            $fileaquatone = str_replace('.carousel-indicators li {
      background-color: #6c757d;
    }', 

    '.carousel-indicators li {
      background-color: white;
    }

    .pre {
        display: block;
        font-size: 87.5%;
        color: black;
    }

    .cluster {
        border-bottom: 1px solid rgb(68, 68, 68);
        padding: 30px 20px 20px 20px;
        overflow-x: auto;
        white-space: nowrap !important;
    }

    .cluster:nth-child(even) {
        background-color: rgba(0, 0, 0, 0.075);
        box-shadow: inset 0px 6px 8px rgb(24, 24, 24);
    }', $fileaquatone);

            $fileaquatone = str_replace('<div class="card-footer">', '<div class="card-footer">
            <label style="text-align: right; float: right; margin-right: 4%">
                <input type="checkbox" name="dirscan"> <b>Dirscan</b>
            </label>

            <label style="text-align: right; float: right; margin-right: 2%">
                <input type="checkbox" name="nmap"> <b>Nmap</b>
            </label>', $fileaquatone);

            $fileaquatone = str_replace('<td>', '<td style="word-wrap: break-word; max-width: 100px;">', $fileaquatone);

            $fileaquatone = str_replace('<a href="" target="_blank" class="btn btn-primary view-raw-response-button">View Raw Response</a>', '', $fileaquatone);

            $fileaquatone = str_replace('<a href="" target="_blank" class="btn btn-primary view-raw-headers-button">View Raw Headers</a>', '', $fileaquatone);

            $fileaquatone = str_replace('tabindex="-1" role="dialog" aria-hidden="true"', '', $fileaquatone);

            $fileaquatone = str_replace('modal fade in', 'modal', $fileaquatone);

            $fileaquatone = str_replace('modal fade', 'modal', $fileaquatone);

            $fileaquatone = str_replace('<div class="page-screenshot-container" style="transform: scale(1);', '<div class="page-screenshot-container" style="transform: scale(1); overflow: hidden;', $fileaquatone);

            $fileaquatone = str_replace('return _.chunk(this.pages, 2);', 'return _.chunk(this.pages, 200);', $fileaquatone);

            $fileaquatone = str_replace('<a class="navbar-brand" href="#">AQUATONE</a>', '<a class="navbar-brand" href="#/pages/by-similarity">AQUATONE</a>', $fileaquatone);

            $fileaquatone = preg_replace('~<script type="text\/x\-template" id="pageCarouselTemplate">.+?<\/script>~ism', '
                <script type="text/x-template" id="pageCarouselTemplate">
                  <div class="page-similarity-cluster carousel slide" :id="\'carousel_\' + id" data-interval="false">
                        <div class="cluster">
                          <div class="carousel-item" v-for="(pageGroup, index) in pagesInGroups" :class="{ \'active\': index === 0 }">
                            <page-card v-for="page in pageGroup" v-bind:page="page" v-bind:key="page.uuid"></page-card>
                          </div>
                        </div>
                    </div>
                </script>', $fileaquatone);

            $fileaquatone = str_replace('overflow: hidden;', 'overflow: auto;', $fileaquatone);

            $fileaquatone = str_replace('alias: \'/pages/single\', component: Vue.component(\'SinglePagesPage\'), props: { pages: data.pages } },', 'alias: \'/pages/by-similarity\', component: Vue.component(\'PagesBySimilarityPage\'), props: { pageSimilarityClusters: data.pageSimilarityClusters } },', $fileaquatone);

            /** Link the screenshots from the volume to folder in order to be accessible from nginx **/

            $movescreenshots = "ln -s /screenshots/" . $taskid . "/screenshots /var/www/app/frontend/web/screenshots/" . $taskid . " && sudo chmod -R 777 /screenshots/" . $taskid . "/* ";

            //&& sudo chmod -R 777 /var/www/app/frontend/web/screenshots/" . $taskid . "

            $fileaquatone = preg_replace('~<footer.*footer>~im', '', $fileaquatone); //~ims

            

            //$fileaquatone = preg_replace('/\<nav.*nav\>/', '', $fileaquatone, -1);

            $fileaquatone = aquatone::removeEmoji($fileaquatone);

            exec($movescreenshots);

        } else $fileaquatone="No screenshots";

        return $fileaquatone;
    }

    public static function aquatone($taskid, $filename, $queues)
    {
        //exec("mkdir /screenshots/" . $taskid . "/");

        //for amass results we need to scan other ports
        if ( preg_match("/(\w\d\_\-)*\.json/i", $filename) !== 0 ) {
            $command = "cat ". $filename ." | sudo docker run --cpu-shares 256 -v screenshots:/screenshots -v dockerresults:/dockerresults --rm -i 5631/aquatone2 -http-timeout 280000 -threads 55 -scan-timeout 10000 -ports xlarge -screenshot-timeout 90000 -follow-redirect -out /screenshots/" . $taskid . " -save-body true -similarity 0.85 -screenshot-delay 5000 ";
        }

//-chrome-path /usr/bin/chromium-browser

        //for nmap results
        if ( preg_match("/(\w\d\_\-)*\.xml/i", $filename) !== 0 ) {

            sleep(5);

            $command = "/configs/nmap/nmap-parse-output " . $filename . " http-ports | sort -u > " . $filename . ".proccessed && cat " . $filename . ".proccessed | sudo docker run --cpu-shares 256 -v screenshots:/screenshots -v dockerresults:/dockerresults --rm -i 5631/aquatone2 -http-timeout 280000 -threads 55 -scan-timeout 10000 -screenshot-timeout 110000 -follow-redirect -out /screenshots/" . $taskid . " -save-body true -similarity 0.85 -screenshot-delay 12000 ";
        }

        if ( preg_match("/(\w\d\_\-)*\.txt/i", $filename) !== 0 ) {
            $command = "cat ". $filename ." | sudo docker run --cpu-shares 256 -v screenshots:/screenshots -v dockerresults:/dockerresults --rm -i 5631/aquatone2 -http-timeout 290000 -threads 55 -screenshot-timeout 250000 -follow-redirect -out /screenshots/" . $taskid . " -save-body true -similarity 0.9 -screenshot-delay 10000 ";
        }

        exec($command);

        exec("sudo mkdir /var/www/app/frontend/web/screenshots &");

        $aquatoneoutput = aquatone::readaquatone($taskid);

        aquatone::saveToDB($taskid, $aquatoneoutput);

        if( $queues != "" ) {

            $queues = explode(PHP_EOL, $input["queues"]); 

            foreach($queues as $queue){
                dirscan::queuedone($queue);
            }
        }

        //exec("sudo rm -r /dockerresults/" . $randomid . "nmap*");

        return 1;

        //eyewitness -t 10 -x " . $filename . " -d /screenshots/" . $taskid . " –createtargets /screenshots/targets.txt --no-dns  --all-protocols --no-prompt --timeout 20 --max-retries 3 --jitter 1 --results 50 --no-prompt --prepend-https
        //gowitness --db-path /screenshots/gowitness.sqlite3 --screenshot-path /screenshots/" . $taskid . " --timeout 20 
    }

    //for JSA
    public static function aquatonetext($input)
    {
        $randomid = $input["randomid"];

        $taskid = $randomid;

        $filename = "/dockerresults/" . $randomid . "aquatoneinput.txt";

        if ( preg_match("/(\w\d\_\-)*\.txt/i", $filename) !== 0 ) {
            $command = "cat ". $filename ." | sudo docker run --cpu-shares 256 -v screenshots:/screenshots -v dockerresults:/dockerresults --rm -i 5631/aquatone2 -http-timeout 290000 -threads 15 -screenshot-timeout 250000 -follow-redirect -out /screenshots/" . $taskid . " -save-body true -similarity 0.9 -screenshot-delay 10000 ";
        }

        exec($command);

        exec("sudo mkdir /var/www/app/frontend/web/screenshots &");

        $aquatoneoutput = aquatone::readaquatone($taskid);

        aquatone::saveToDB($taskid, $aquatoneoutput);

        //exec("sudo rm -r /dockerresults/" . $randomid . "nmap*");

        return 1;

        //eyewitness -t 10 -x " . $filename . " -d /screenshots/" . $taskid . " –createtargets /screenshots/targets.txt --no-dns  --all-protocols --no-prompt --timeout 20 --max-retries 3 --jitter 1 --results 50 --no-prompt --prepend-https
        //gowitness --db-path /screenshots/gowitness.sqlite3 --screenshot-path /screenshots/" . $taskid . " --timeout 20 
    }

}





















