<?php

use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\Html;
use yii\web\JqueryAsset;
use yii\web\View;

$this->title = 'Scan your host';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(Yii::$app->request->baseUrl . '/js/newscan.js', ['yii\web\JqueryAsset']);
$this->registerCssFile(Yii::$app->request->baseUrl . '/css/buttons.css', [
    'depends' => [JqueryAsset::className()]
]);

$script = <<< JS
    $('.switch').on('click', function() {
        clickedcheckbox = document.getElementById(this.id+"box").checked;
        if (clickedcheckbox === true && this.id === "activescan"){
            $( "#passivescanbox" ).prop( "disabled", true );
            document.getElementById("hiddenactivescan").value = 1;
        }
        if (clickedcheckbox === false && this.id === "activescan"){
            $( "#passivescanbox" ).prop( "disabled", false );
            document.getElementById("hiddenactivescan").value = 0;
        }
        if (clickedcheckbox === true && this.id === "passivescan"){
            $( "#activescanbox" ).prop( "disabled", true );
            document.getElementById("hiddenpassivescan").value = 1;
        }
        if (clickedcheckbox === false && this.id === "passivescan"){
            $( "#activescanbox" ).prop( "disabled", false );
            document.getElementById("hiddenpassivescan").value = 0;
        }
        if (clickedcheckbox === true)
            $("."+this.id+"class").toggle();
    });
JS;
$this->registerJs($script, View::POS_READY);
?>

<!---
//TOS LINK!!!!
--->

<?php if (!Yii::$app->user->isGuest): ?>
    <?php $form = ActiveForm::begin(['id' => 'urlform']); ?>

    <?= $form->field($model, 'activescan')->hiddenInput(['id' => 'hiddenactivescan', 'value' => 0])->label(false); ?>
    <?= $form->field($model, 'passivescan')->hiddenInput(['id' => 'hiddenpassivescan', 'value' => 0])->label(false); ?>

    <style type="text/css">
        .input-group-addon {
            min-width: 78px;
            text-align: center;
        }
    </style>

    <ul class="nav nav-tabs nav-justified">
        <li class="active" id="lihome"><a data-toggle="tab" href="#home">New scan</a></li>
        <li id="limenu1"><a data-toggle="tab" href="#menu1">Scan type</a></li>
        <li id="limenu2"><a data-toggle="tab" href="#menu2">Scan instruments</a></li>
        <li id="limenu3"><a data-toggle="tab" href="#menu3">Intstrument's options</a></li>
    </ul>

    <div class="tab-content">
        <div id="home" class="tab-pane fade in active">
            <h3 align="center">Create scan</h3>


            <p align="center">In next steps you will be able to scan your infrastructure.</p>


            <div class="col-lg-12">
                <div class="col-lg-5" style="margin-left:0.7%;"></div>
                <div class="col-lg-6">
                    <div class="btn btn-success" id="buttonnext" onclick="changeactive(limenu1,menu1);"
                         style="Width: 10em">
                        <b>Next</b>
                    </div>
                </div>
            </div>
        </div>

        <div id="menu1" class="tab-pane fade">
            <h3 align="center">Scan type</h3>

            <h4 align="center">Specify what type of scan you would like to create.</h4>

            <div>
                <div class="col-lg-12">

                    <div class="col-xs-2"></div>
                    <div class="col-xs-6">

                        <a data-toggle="modal" data-target="#activescanModal" href="#" style="margin-left:4.2%;">Active
                            scan</a>

                        <div id="activescanModal" class="modal fade" role="dialog">
                            <div class="modal-dialog">

                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">What is Active scan?</h4>
                                    </div>

                                    <div class="modal-body">
                                        <p>Active scan is type of scan when scanning process creates immediately.</p>
                                        <p>You will get your scan results in 10 - 60 minutes from scan start.</p>
                                        <p>You are able to create 15 any scans per 30 minutes.</p>
                                        <p>Also you will be emailed when the scan process will end.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="col-xs-4">

                        <a data-toggle="modal" data-target="#passivescanModal" href="#" style="margin-left:10%;">Passive
                            scan</a>

                        <div id="passivescanModal" class="modal fade" role="dialog">
                            <div class="modal-dialog">

                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">What is Passive scan?</h4>
                                    </div>

                                    <div class="modal-body">
                                        <p>Passive scan is type of scan when scanning will be done each week for
                                            checking any new ports/domains.</p>
                                        <p>You will get an email if new scan results will differ with previous.</p>
                                        <p>Scanning will be done until you will turn it off in your settings.</p>
                                        <p>You are able to create only 3 passive scans.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-12">

                    <div class="col-xs-2"></div>
                    <div class="col-xs-6">
                        <label class="switch" style="margin-left:0.8%;" id="activescan">
                            <input type="checkbox" id="activescanbox">
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <div class="col-xs-4">
                        <label class="switch" style="margin-left:5.5%;" id="passivescan">
                            <input type="checkbox" id="passivescanbox">
                            <span class="slider round"></span>
                        </label>
                    </div>

                </div>

                <div class="col-lg-2"></div>
                <div class="col-lg-6">
                    <div class="btn btn-success" id="buttonprevious" onclick="changeactive(lihome,home);"
                         style="Width: 10em">
                        <b>Previous</b>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="btn btn-success" id="buttonnext" onclick="changeactive(limenu2,menu2);"
                         style="Width: 10em">
                        <b>Next</b>
                    </div>
                </div>
                <div class="col-lg-2"></div>

            </div>
        </div>

        <div id="menu2" class="tab-pane fade">
            <h3 align="center">Scan options</h3>
            <h4 align="center">Specify which instruments will be used for scanning.</h4>

            <div class="defaultclass" style="display:none">
                <div>
                    <div class="col-lg-12">

                        <div class="col-xs-2"></div>
                        <div class="col-xs-6">

                            <a data-toggle="modal" data-target="#nmapModal" href="#" style="margin-left:7.4%;">Nmap</a>

                            <div id="nmapModal" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">What is Nmap?</h4>
                                        </div>

                                        <div class="modal-body">
                                            <p>Nmap is the most powerful port scanner in the world</p>
                                            <p>It scans your server for possible ports and gives you its information in
                                                comfortable to view format</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-xs-4">

                            <a data-toggle="modal" data-target="#amassModal" href="#"
                               style="margin-left:14.5%;">Amass</a>

                            <div id="amassModal" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">What is amass?</h4>
                                        </div>

                                        <div class="modal-body">
                                            <p>Amass is powerful tool created for finding your Hostname's
                                                subdomains.</p>
                                            <p>Amass uses Google, HackerTarget, Censys, Netcraft, Shodan, Threat
                                                Crowd, VirusTotal, PublicWWW, PassiveTotal, CertificateSearch and big
                                                subdomain names dictionary for finding your subdomains!</p>
                                            <p>If its possible to find your subdomain - amass will find it.</p>
                                            <p>You will get your ouput in HOST:IP format.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-12">

                        <div class="col-xs-2"></div>
                        <div class="col-xs-6">

                            <label class="switch" style="margin-left:0.8%;" id="nmap">
                                <input type="checkbox" id="nmapbox">
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="col-xs-4">
                            <label class="switch" style="margin-left:5.5%;" id="amass">
                                <input type="checkbox" id="amassbox">
                                <span class="slider round"></span>
                            </label>
                        </div>

                    </div>

                    <div class="col-lg-12">

                        <div class="col-xs-2"></div>
                        <div class="col-xs-6">

                            <a data-toggle="modal" data-target="#dirscanModal" href="#"
                               style="margin-left:7%;">Dirscan</a>
                            <!-- Modal -->
                            <div id="dirscanModal" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">What is Dirscan?</h4>
                                        </div>

                                        <div class="modal-body">
                                            <p>Dirscan is a tool created for scanning your hosts for secret/configuration
                                                files, which could lead to risks.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                        <div class="passivescanclass">
                            <div class="col-xs-4">

                                <a data-toggle="modal" data-target="#vhostscanModal" href="#"
                                   style="margin-left:11.7%;">Vhost scan</a>

                                <div id="vhostscanModal" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;
                                                </button>
                                                <h4 class="modal-title">What is Vhost scan?</h4>
                                            </div>

                                            <div class="modal-body">
                                                <p>Vhost scan is a tool created for scanning your servers for virtual
                                                    hostnames like "admin.example.com, db.example.com", which could lead
                                                    to big problems.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                                    Close
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">

                        <div class="col-xs-2"></div>
                        <div class="col-xs-6">
                            <label class="switch" style="margin-left:0.8%;" id="dirscan">
                                <input type="checkbox" id="dirscanbox">
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="col-xs-4">
                                <label class="switch" style="margin-left:5.5%;" id="vhostscan">
                                    <input type="checkbox" id="vhostscanbox">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                    </div>

                    <div class="col-lg-12">

                        <div class="col-xs-2"></div>

                        <div class="col-xs-6">
                            <div class="passivescanclass">
                                <a data-toggle="modal" data-target="#nucleiModal" href="#" style="margin-left:7.5%;">Nuclei</a>

                                <div id="nucleiModal" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;
                                                </button>
                                                <h4 class="modal-title">What is nuclei?</h4>
                                            </div>

                                            <div class="modal-body">
                                                <p>nuclei is a tool created for searching misconfigurations / vulnerable software with predefined requests rules</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                    <div class="passivescanclass">
                            <div class="col-xs-4">

                                <a data-toggle="modal" data-target="#findipsModal" href="#" style="margin-left:13%;">Find
                                    IP's</a>

                                <div id="findipsModal" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;
                                                </button>
                                                <h4 class="modal-title">What is Find IP'S?</h4>
                                            </div>

                                            <div class="modal-body">
                                                <p>Find IP's is a tool created for searching all possible IP addresses
                                                    for your server even if its behind Cloudflare/Cloudstorm/etc.</p>
                                                <p>If IP will be found, any 1 could connect to your server with IP and
                                                    bypass all Cloud protection mechanisms.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                                    Close
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                    </div>     

                    <div class="col-lg-12">

                        <div class="col-xs-2"></div>

                        <div class="passivescanclass">
                            <div class="col-xs-6">
                                <label class="switch" style="margin-left:-0.5%;" id="nuclei">
                                    <input type="checkbox" id="nucleibox">
                                    <span class="slider round"></span>
                                </label>
                            </div>

                            <div class="passivescanclass">
                                <div class="col-xs-4">
                                    <label class="switch" style="margin-left:7.2%;" id="findips">
                                        <input type="checkbox" id="findipsbox">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                    <!-- <div class="col-lg-12">

                        <div class="col-xs-2"></div>

                        <div class="col-xs-6">
                            <div class="passivescanclass">
                                <a data-toggle="modal" data-target="#gitscanModal" href="#" style="margin-left:7%;">Gitscan</a>

                                <div id="gitscanModal" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;
                                                </button>
                                                <h4 class="modal-title">What is Gitscan?</h4>
                                            </div>

                                            <div class="modal-body">
                                                <p>Gitscan is a tool created for scanning your git repositories for
                                                    valuable information like Passwords/AWS Keys/etc.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> 

                        <div class="passivescanclass">
                            <div class="col-xs-4">

                                <a data-toggle="modal" data-target="#awsscanModal" href="#" style="margin-left:13%;">AWS scan</a>

                                <div id="awsscanModal" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;
                                                </button>
                                                <h4 class="modal-title">What is Awsscan?</h4>
                                            </div>

                                            <div class="modal-body">
                                                <p>Awsscan is a tool created for searching the buckets that belong to the company by bruteforcing bucket names.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                                    Close
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                

                                               

                    <div class="col-lg-12">

                        <div class="col-xs-2"></div>

                        <div class="passivescanclass">
                            <div class="col-xs-6">
                                <label class="switch" style="margin-left:0.8%;" id="gitscan">
                                    <input type="checkbox" id="gitscanbox">
                                    <span class="slider round"></span>
                                </label>
                            </div>

                            <div class="passivescanclass">
                                <div class="col-xs-4">
                                    <label class="switch" style="margin-left:5.5%;" id="awsscan">
                                        <input type="checkbox" id="awsscanbox">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>-->

                    <!--<div class="col-lg-12">

                        <div class="passivescanclass">
                            <div class="col-xs-2"></div>
                            <div class="col-xs-6">

                                <a data-toggle="modal" data-target="#raceModal" href="#" style="margin-left:2.6%;">RaceCondition</a>

                                <div id="raceModal" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;
                                                </button>
                                                <h4 class="modal-title">What is Race Condition Tester?</h4>
                                            </div>

                                            <div class="modal-body">
                                                <p>Race Condition Tester - tool, created for testing race conditions on
                                                    your server</p>
                                                <p>It makes huge amount of requests in 1-2 seconds and tries to make
                                                    race condition game</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>


                            <div class="col-xs-4">

                                <a data-toggle="modal" data-target="#reverseipModal" href="#"
                                   style="margin-left:13.5%;">ReverseIP</a>

                                <div id="reverseipModal" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                <h4 class="modal-title">What is ReverseIP scan?</h4>
                                            </div>

                                            <div class="modal-body">
                                                <p>ReverseIP scan is type of scan created for finding hostnames that are
                                                    hosted on the same IP address.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="passivescanclass">
                            <div class="col-xs-2"></div>
                            <div class="col-xs-6">

                                <label class="switch" style="margin-left:0.8%;" id="race">
                                    <input type="checkbox" id="racebox">
                                    <span class="slider round"></span>
                                </label>
                            </div>


                            <div class="col-xs-4">
                                <label class="switch" style="margin-left:5.5%;" id="reverseip">
                                    <input type="checkbox" id="reverseipbox">
                                    <span class="slider round"></span>
                                </label>
                            </div>

                        </div>
                    </div> -->

                    <!--<div class="col-lg-12">
                        <div class="passivescanclass">
                            <div class="col-xs-2"></div>
                            <div class="col-xs-6">

                                <label class="switch" style="margin-left:0.8%;" id="reverseip">
                                    <input type="checkbox" id="reverseipbox">
                                    <span class="slider round"></span>
                                </label>
                            </div>


                            <div class="col-xs-4">
                                <label class="switch" style="margin-left:5.5%;" id="amass">
                                    <input type="checkbox" id="amassbox">
                                    <span class="slider round"></span>
                                </label>
                            </div>

                        </div>
                    </div>-->

                </div>
            </div>
            <div class="col-lg-2"></div>
            <div class="col-lg-6">
                <div class="btn btn-success" id="buttonprevious" onclick="changeactive(limenu1,menu1);"
                     style="Width: 10em">
                    <b>Previous</b>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="btn btn-success" id="buttonnext" onclick="changeactive(limenu3,menu3);" style="Width: 10em">
                    <b>Next</b>
                </div>
            </div>
            <div class="col-lg-2"></div>
        </div>

        <div id="menu3" class="tab-pane fade">
            <h3 align="center">Instrument's options</h3>
            <h4 align="center">Specify options for scanning.</h4>

            <div class="nmapclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="nmap">
                            <p align="center">Domain for scanning with Nmap:</p>
                            <?= $form->field($model, 'nmapDomain', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Domain</span>{input}</div>'])->textInput(['autofocus' => true, 'placeholder' => "127.0.0.1 or example.com"])->label(false) ?>
                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>

            <div class="amassclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="amass">
                            <p align="center">Domain for scanning with amass:</p>
                            <?= $form->field($model, 'amassDomain', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Domain</span>{input}</div>'])->textArea(['autofocus' => true, 'placeholder' => "example.com"])->label(false) ?>
                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>

            <div class="dirscanclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="dirscan">
                            <p align="center">URL for scanning with Dirscan:</p>
                            <?= $form->field($model, 'dirscanUrl', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">URL</span>{input}</div>'])->textArea(['autofocus' => true, 'placeholder' => "https://example.com"])->label(false) ?>    

                            <?= $form->field($model, 'dirscanIp', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">IP</span>{input}</div>'])->textInput(['required' => false, 'placeholder' => "IP. Example: 8.8.8.8"])->label(false) ?>

                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>

            <div class="nucleiclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="nuclei">

                            <p align="center">Enter URL or domains separated by newline:</p>

                            <?= $form->field($model, 'nucleiDomain', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Domain</span>{input}</div>'])->textArea(['autofocus' => true, 'placeholder' => "example.com"])->label(false) ?>

                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>

            <div class="jsaclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="jsa">
                            <p align="center">URL for searching secrets in javascript files at URL:</p>
                            <?= $form->field($model, 'jsaDomain', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">URL</span>{input}</div>'])->textArea(['autofocus' => true, 'placeholder' => "https://example.com"])->label(false) ?>    
                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>

            <div class="gitscanclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="gitscan">

                            <p align="center">Enter github repo URL for scanning:</p>
                            <?= $form->field($model, 'gitUrl', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Repo URL</span>{input}</div>'])->textInput(['autofocus' => true, 'placeholder' => "https://github.com/dxa4481/truffleHog"])->label(false) ?>

                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>

            <div class="passivegitscanclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="gitscan">

                            <p align="center">Enter github repo URL or company url for continuous scanning:</p>
                            <?= $form->field($model, 'gitPassiveUrl', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Repo URL</span>{input}</div>'])->textInput(['autofocus' => true, 'placeholder' => "https://github.com/dxa4481/truffleHog"])->label(false) ?>
                            <?= $form->field($model, 'gitCompany', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Company URL</span>{input}</div>'])->textInput(['autofocus' => true, 'placeholder' => "https://github.com/qiwi"])->label(false) ?>

                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>


            <div class="findipsclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="findips">

                            <p align="center">Enter Domain name for searching its IP's:</p>

                            <?= $form->field($model, 'ips', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Domain</span>{input}</div>'])->textArea(['autofocus' => true, 'placeholder' => "example.com"])->label(false) ?>

                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>

            <div class="findipsclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="findips">

                            <p align="center">Enter Domain name for searching its IP's:</p>

                            <?= $form->field($model, 'whatwebUrl', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Domain</span>{input}</div>'])->textArea(['autofocus' => true, 'placeholder' => "example.com"])->label(false) ?>

                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>

            <div class="vhostscanclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="vhostscan">

                            <p align="center">Enter options for checking virtual hosts:</p>

                            <?= $form->field($model, 'vhostDomain', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Domain</span>{input}</div>'])->textArea(['placeholder' => "Domain. Example: scaner.pw"])->label(false) ?>

                            <?= $form->field($model, 'vhostIp', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">IP</span>{input}</div>'])->textArea(['placeholder' => "Domain's IP. Example: 8.8.8.8"])->label(false) ?>

                            <?= $form->field($model, 'vhostPort', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Port</span>{input}</div>'])->textArea(['required' => false, 'placeholder' => "Port. Example: 80"])->label(false) ?>
                            <?= $form->field($model, 'vhostSsl', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">SSL</span>{input}</div>'])->textInput(['required' => false, 'placeholder' => "Should scanner use SSL? 0/1"])->label(false) ?>

                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>

            <div class="raceclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="race">

                            <p align="center">Enter options for checking race conditions:</p>

                            <?= $form->field($model, 'raceUrl', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">URL</span>{input}</div>'])->textInput(['placeholder' => "Full url to resource. Example: https://google.com/"])->label(false) ?>

                            <?= $form->field($model, 'raceCookies', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Cookies</span>{input}</div>'])->textInput(['placeholder' => "Request's cookies. Example: sessionId=123; cid=123"])->label(false) ?>

                            <?= $form->field($model, 'raceBody', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Body</span>{input}</div>'])->textInput(['placeholder' => "Request's body. Example: amount=1&from=123&to=324"])->label(false) ?>

                            <?= $form->field($model, 'raceHeaders', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Headers</span>{input}</div>'])->textInput(['placeholder' => "Request's Headers. Example: \"X-Originating-IP: 127.0.0.1\", 'Authorization: 123'"])->label(false) ?>

                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>

            <div class="reverseipclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="reverseip">

                            <p align="center">Enter IP for scanning:</p>

                            <?= $form->field($model, 'reverseip', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">IP</span>{input}</div>'])->textInput(['placeholder' => "IP. Example: 8.8.8.8"])->label(false) ?>

                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>

            <div class="newtoolclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="newtool">

                            <p align="center">Enter URL or domains separated by newline:</p>

                            <?= $form->field($model, 'gitUrl', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Domain</span>{input}</div>'])->textArea(['autofocus' => true, 'placeholder' => "example.com"])->label(false) ?>

                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>

            <div class="awsscanclass" style="display: none; margin-top= 2em;">
                <div class="col-lg-12">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <div class="findaws">

                            <p align="center">Enter Domain name for searching its AWS buckets:</p>

                            <?= $form->field($model, 'raceUrl', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon">Domain</span>{input}</div>'])->textInput(['autofocus' => true, 'placeholder' => "example.com"])->label(false) ?>

                        </div>
                    </div>
                    <div class="col-lg-2"></div>
                </div>
            </div>

            <div class="col-lg-12">
                <?= $form->field($model, 'verifyCode')->widget(Captcha::className(), [
                    'template' => '<div class="row"><div class="col-lg-2">{image}</div><div class="col-lg-2">{input}</div></div>',
                ]) ?>
            </div>

            <div class="col-lg-6">
                <?= $form->field($model, 'passive')->checkbox(['class' => 'agree', 'checked' => true, 'value' => 1])->label('Scan continiously') ?>
                <?= $form->field($model, 'agreed')->checkbox(['class' => 'agree', 'checked' => true, 'required' => true])->label('Agreed with TOS') ?>
                <?= $form->field($model, 'notify')->checkbox(['class' => 'agree', 'checked' => false, 'value' => 0])->label('Notify with email when scan ends') ?>
                <?= $form->field($model, 'manual')->hiddenInput(['value'=> 1])->label(false); ?>

            </div>

            <div class="submitbutton">
                <div class="col-xs-12">
                    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary btn-block btn-success', 'name' => 'submit-button']) ?>
                </div>
            </div>

        </div>
    </div>

    <?php ActiveForm::end(); ?>


<?php endif; ?>
