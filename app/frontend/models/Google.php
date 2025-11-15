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
        /*
                scaner 009056623878989539741:hh8t7ync-hu

                *.* 009056623878989539741:2xnl_qm7mzc

                AIzaSyAmgZgfumBFJdFTJuRBi8c4zF3-SynkaqM
                AIzaSyBt21mp51qmIt1ZcerDbm9tVzZDqCEaXPw
                AIzaSyB4OZ1lPlm4c2Z_jJgVqgyW4tJ4bMkRH_A
                AIzaSyA_BKkAVx4dA3rC15mhalNFk__v7U4OH0g
                AIzaSyACgMeoRFa8DFd4rAIezbVyDakkEpHzp1s
                AIzaSyBqnSzAaprsJNandD3CBY9orf0HNLBVt7M
                AIzaSyCjD0kPGLCeuXkkplr2nSWLveoX4tt63Pc
                AIzaSyCjTFyF-ctYaX8t29-Cenx_15jxQjobAAY
                AIzaSyBPqoKv69cJzN0SligKTaNbobr2GIiDguc
                AIzaSyCU6dHcECm3s-epe4jTZB-QpBd-A2lCEfE
                AIzaSyC9-QayQxfJ8xH-wwWQ8oO6ry_NKAiRBV8
                AIzaSyA7lmKXYrDhWU_zEbsukC-qBmzydUVdGvw
                AIzaSyAC3JTsAAXnTaQCGv-mQkwfAn2aEg0hEPs
                AIzaSyC6f9IVSwDhx7lwe3bz_8VXhTx0o3rkz2g
                AIzaSyCboPULES5OsLm9w622wA-MHjKYcziN7hY
                AIzaSyAynRpZCdKtmK869ZUoyCim6cqP3Dq1IDc
                AIzaSyBfJE2Wfzh7KXhBrL-pc0FYsTn-W14Xl3M
                AIzaSyAITFF_ItgPyiX0oykGsm7GJ98iZ5CtGbA
                AIzaSyCuTaM-yFJbzxHtzWvHPymXYTQ45ATys8c
                AIzaSyAD0hXKqkvQQX6J4Add5xoSUUs0kioOpL4
                AIzaSyAnPTptBkEsXjYnpUxznJM10x0lLMT5JgY
                AIzaSyC4pe7GRml_IBcLfFLnToaDFiKTPCL4_y8
                AIzaSyCgy5gkq-nI5unT5Fz4x_bIVIOfjd7HVpM
                AIzaSyDRCz_SLVarBs7UGZFw0QTY5X2nnvFg8SY
                AIzaSyBRrZuQhdTHGkA32DGQJg80vkMSKA7iCMI

                python dork-cli.py -e 009056623878989539741:2xnl_qm7mzc -k AIzaSyA_BKkAVx4dA3rC15mhalNFk__v7U4OH0g -s 4 site:apple.com inurl:id -m 3

        */
        global $a;
        $a = "";
        while ($a != "Done") {
            $countnmap = "pgrep -c nmap";

            exec($countnmap, $countnmap_returncode);

            if ($countnmap_returncode[0] < 8) {

                $url = $input["url"];
                $taskid = $input["taskid"];

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

                $command = "sudo /usr/bin/nmap -sS -T4 -A -p- -max-scan-delay 4s -host-timeout 900m -script-timeout 900m -sC -oA /var/www/output/nmap/scan" . $randomid . " --stylesheet /var/www/output/nmap.xsl  --script smb-brute --script smb-os-discovery  " . $url . "  2>/var/www/output/nmap/scan" . $randomid . ".err";

                $command1 = "sudo /usr/bin/xsltproc -o /var/www/output/nmap/scan" . $randomid . ".html /var/www/nmap.xsl /var/www/output/nmap/scan" . $randomid . ".xml  2>/var/www/output/nmap/scan" . $randomid . ".xml.err  ";

                $command2 = "sudo /usr/bin/find /var/www/output/nmap/ -name 'scan$randomid.*' -delete &";

                $escaped_command = ($command);

                system($escaped_command, $nmap_returncode);

                system($command1, $xslt_returncode);

                $output = base64_encode(file_get_contents("/var/www/output/nmap/scan" . $randomid . ".html"));

                $date_end = date("Y-m-d H-i-s");

                $nmap = Tasks::find()
                    ->where(['taskid' => $taskid])
                    ->limit(1)
                    ->one();

                $nmap->nmap_status = "Done.";
                $nmap->nmap = $output;
                $nmap->date = $date_end;

                system($command2);

                $a = "Done";
                $nmap->save();

                return 1;

            } else sleep(20);
        }
        return 2;
    }


}


