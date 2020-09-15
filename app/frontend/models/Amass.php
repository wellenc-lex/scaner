<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

class Amass extends ActiveRecord
{
    public static function tableName()
    {
        return 'tasks';
    }

    public static function amassscan($input)
    {

                $url = $input["url"];
                $taskid = $input["taskid"];

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

                //$command = "sudo docker run --cpu-shares=2048 --rm -v configs:/configs/ -v dockerresults:/dockerresults caffix/amass enum -w /wordlists/all.txt -d  " . escapeshellarg($url) . " -json /dockerresults/amass" . $randomid . ".json -ip -noalts -norecursive"; //turned off brute for testing purposes

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

                $command = "cat /dockerresults/amass" . $randomid . ".json | sudo docker run -v screenshots:/screenshots --rm -i 5631/aquatone -http-timeout 20000 -threads 4 -ports large -scan-timeout 5000 -screenshot-timeout 3000 -chrome-path /usr/bin/chromium-browser -out /screenshots/" . $randomid . " -save-body false > /dev/null";

                exec($command);

                if (file_exists("/screenshots/" . $randomid . "/aquatone_report.html")) {
                    $fileaquatone = file_get_contents("/screenshots/" . $randomid . "/aquatone_report.html");
                }

                $fileaquatone = str_replace('<img src="screenshots', '<img src="../../screenshots', $fileaquatone);

                $fileaquatone = str_replace('<a href="screenshots', '<a href="../../screenshots', $fileaquatone);

                $fileaquatone = str_replace('<link rel="stylesheet" href="https://bootswatch.com/4/darkly/bootstrap.min.css" integrity="sha384-RVGPQcy+W2jAbpqAb6ccq2OfPpkoXhrYRMFFD3JPdu3MDyeRvKPII9C82K13lxn4" crossorigin="anonymous">', '<link rel="stylesheet" href="https://bootswatch.com/3/darkly/bootstrap.min.css">', $fileaquatone);

                $fileaquatone = str_replace('</html>>', '</html>', $fileaquatone);

                $fileaquatone = str_replace('.cluster {
                    border-bottom: 1px solid rgb(68, 68, 68);
                    padding: 30px 20px 20px 20px;
                    overflow-x: auto;
                    white-space: nowrap;
                }', '.cluster {
                    border-bottom: 1px solid rgb(68, 68, 68);
                    box-shadow: inset 0px 6px 8px rgb(24, 24, 24);
                    padding: 30px 20px 20px 20px;
                    overflow-x: auto;
                    white-space: nowrap;
                }', $fileaquatone);

                                $fileaquatone = str_replace('.cluster:nth-child(even) {
                    background-color: rgba(0, 0, 0, 0.075);
                    box-shadow: inset 0px 6px 8px rgb(24, 24, 24);
                }', '.cluster:nth-child(even) {
                    border-bottom: 1px solid rgb(68, 68, 68);
                    padding: 30px 20px 20px 20px;
                    overflow-x: auto;
                    white-space: nowrap;
                    box-shadow: inset 0px 6px 8px rgb(24, 24, 24);
                }', $fileaquatone);

                preg_replace("/<footer>.*<\/footer>/s", "", $fileaquatone);

                $fileaquatone = str_replace('<div class="card-footer text-muted">', '<div class="card-footer text-muted">
                <label style="text-align: right; float: right; margin-right: 4%">
                    <input type="checkbox" name="dirscan"> <b>Dirscan</b>
                </label>

                <label style="text-align: right; float: right; margin-right: 2%">
                    <input type="checkbox" name="nmap"> <b>Nmap</b>
                </label>', $fileaquatone);

                $fileaquatone = str_replace('<td>', '<td style="word-wrap: break-word; max-width: 100px;">', $fileaquatone);

                $aquatoneoutput = $fileaquatone;

                /** Copy the screenshots from the folder to volume root in order to be accessible all the time**/

                $clearthemess = "sudo chmod -R 755 /screenshots/" . $randomid . "/screenshots && cp -R --remove-destination /screenshots/" . $randomid . "/screenshots /var/www/app/frontend/web/ && sudo rm -r /screenshots/" . $randomid . "/ && sudo chmod -R 755 /var/www/app/frontend/web/screenshots/ ";

                exec($clearthemess);

                /*$command = "/bin/cat /var/www/output/amass/" . $randomid . ".json | /usr/bin/jq --raw-output '.name' > /var/www/output/amass/jq" . $randomid .
                    ".json && /usr/local/bin/subjack -w /var/www/output/amass/jq" . $randomid .
                    ".json -t 10 -timeout 30 -a -o /var/www/output/subtakeover" . $randomid . ".txt -ssl -c /var/www/soft/subjack/fingerprints.json ";

                exec($command);

                if (file_exists("/var/www/output/subtakeover" . $randomid . ".txt")) {
                    $subtakeover = file_get_contents("/var/www/output/subtakeover" . $randomid . ".txt");
                }

                else */$subtakeover = 0;

                $date_end = date("Y-m-d H-i-s");

                $amass = Tasks::find()
                    ->where(['taskid' => $taskid])
                    ->limit(1)
                    ->one();

                $amass->amass_status = 'Done.';
                $amass->amass = $amassoutput;
                $amass->aquatone = $aquatoneoutput;
                $amass->subtakeover = $subtakeover;
                $amass->date = $date_end;

                $amass->save(); 

                $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
                exec('curl --insecure  -H \'Authorization: Basic bmdpbng6U25pcGVydWx0cmEx\' --data "taskid=' . $amass->taskid . ' & secret=' . $secret . '" http://dev.localhost.soft/scan/vhostscan > /dev/null 2>/dev/null &');
                
                $decrement = ToolsAmount::find()
                    ->where(['id' => 1])
                    ->one();

                $value = $decrement->amass;
                
                if ($value <= 1) {
                    $value=0;
                } else $value = $value-1;

                $decrement->amass=$value;
                $decrement->save();

    }

}


















