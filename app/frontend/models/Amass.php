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

                $amass_returncode = 1;
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

                $command = "sudo docker run --cpu-shares=2048 --rm -v configs:/root/configs/ -v dockerresults:/dockerresults caffix/amass enum -w /wordlists/all.txt -d  " . escapeshellarg($url) . " -json /dockerresults/amass" . $randomid .
                    ".json -active -brute -ip -config /root/configs/amass.ini";

                system($command, $amass_returncode);

                if (file_exists("/dockerresults/amass" . $randomid . ".json")) {
                    $fileamass = file_get_contents("/dockerresults/amass" . $randomid . ".json");
                }

                $fileamass = str_replace("}
{\"Timestamp\"", "},{\"Timestamp\"", $fileamass);

                $fileamass = str_replace("} {", "},{", $fileamass);

                $amassoutput = '[' . $fileamass . ']';
                $fileamass = str_replace("}
{", "},{", $fileamass);

                $command = "cat /dockerresults/amass" . $randomid . ".json | sudo docker run -v screenshots:/screenshots --rm -i 5631/aquatone -http-timeout 20000 -threads 4 -ports large -scan-timeout 5000 -screenshot-timeout 3000 -chrome-path /usr/bin/chromium-browser -out /screenshots/" . $randomid . " -save-body false > /dev/null";

                system($command, $aquatone_returncode);

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

                    $fileaquatone = str_replace('<footer>
            <p>AQUATONE v1.4.2 &middot; made with <span style="color:red;font-weight:bold">&#65533;</span> by <a href="https://michenriksen.com" target="_blank">Michael Henriksen</a></p>
            <p><a href="https://www.buymeacoffee.com/michenriksen" target="_blank"><img src=" data:image/png;base64,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" alt="Buy Me A Coffee" style="height: auto !important;width: auto !important;"></a></p>
        </footer>', ' ', $fileaquatone);

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

                $clearthemess = "sudo chmod -R 755 /screenshots/" . $randomid . "/screenshots && cp -R --remove-destination /screenshots/" . $randomid . "/screenshots / && rm -r /screenshots/" . $randomid . "/ ";

                system($clearthemess);




                /*$command = "/bin/cat /var/www/output/amass/" . $randomid . ".json | /usr/bin/jq --raw-output '.name' > /var/www/output/amass/jq" . $randomid .
                    ".json && /usr/local/bin/subjack -w /var/www/output/amass/jq" . $randomid .
                    ".json -t 10 -timeout 30 -a -o /var/www/output/subtakeover" . $randomid . ".txt -ssl -c /var/www/soft/subjack/fingerprints.json ";

                system($command);

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
                exec('curl --insecure  -H \'Authorization: Basic bmdpbng6U25pcGVydWx0cmEx\' --data "taskid=' . $amass->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/vhostscan > /dev/null 2>/dev/null &');

    }

}


















