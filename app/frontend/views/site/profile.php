<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;


$this->title = 'Profile';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(Yii::$app->request->baseUrl . '/js/profile.js', [
    'depends' => [
        JqueryAsset::className()
    ]
]);

?>


<div class="profile">
    <h1 style="text-align: center"><?= Html::encode($this->title) ?></h1>

    <style>

        ::-webkit-scrollbar {
            height: 7px;
            background-color: rgba(255, 255, 255, 0);
            margin-top: 4px;
        }

        ::-webkit-scrollbar-track,
        ::-webkit-scrollbar-thumb {
            border: 4px solid rgba(255, 255, 255, 0);
            background-clip: padding-box;
        }

        ::-webkit-scrollbar-track {
            background-color: #ccc;
        }

        ::-webkit-scrollbar-thumb {
            background-color: #212121;
        }

        ::-webkit-scrollbar-thumb:hover {
            border: 3px solid rgba(255, 255, 255, 0);
        }


    </style>

    <?php if (!Yii::$app->user->isGuest): ?>

        <?php $form = ActiveForm::begin(['id' => 'profile']); ?>

        <div id="messagesuccess" class="alert alert-success alert-dismissible" role="alert"
             style="top: 80%; right: 1%; position: fixed; width: 250px; text-align: center; display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span></button>
            <b>Action was successfully done!</b>
        </div>

        <div id="messagefailure" class="alert alert-danger alert-dismissible" role="alert"
             style="top: 80%; right: 1%; position: fixed; width: 250px; text-align: center; display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span></button>
            <b>Failure, contact us!</b>
        </div>

        <div class="container">
            <ul class="nav nav-pills nav-justified">
                <li class="active"><a data-toggle="pill" href="#activescan">Active scan results</a></li>

                <?php if ($notify === 1): ?>
                    <li><a data-toggle="pill" href="#passivescan">Passive scan results <span class="label label-danger">New changes</span></a>
                    </li>
                <?php endif; ?>

                <?php if ($notify === 0): ?>
                    <li><a data-toggle="pill" href="#passivescan">Passive scan results</a></li>
                <?php endif; ?>

                <li><a data-toggle="pill" href="#hiddenscans">Hidden scan results</a></li>
            </ul>

            <div class="tab-content">
                <div id="activescan" class="tab-pane fade in active">

                    <div class="profile">

                        <div class="done" style="width:55%; float: left;">

                            <h1 style="text-align:center; margin-left: -40%;">Results</h1>

                            <table class="table table-bordered" style=" margin-left: -15%; margin-top: 20px; text-align: center">
                                <thead>
                                <tr>
                                    <th style="text-align: center; max-width: 10%; min-width: 10%; width: 10%">ID</th>
                                    <th style="text-align: center; max-width: 50%; min-width: 50%; width: 50%">Host</th>
                                    <th style="text-align: center; max-width: 25%; min-width: 25%; width: 25%">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($done as $scan): ?>

                                    <tr>
                                        <td style="text-align: center; max-width: 10%; min-width: 10%; width: 10%">
                                            <?= "<b style='vertical-align: middle; '>$scan->taskid</b>" ?>
                                        </td>

                                        <td style="text-align: center; height: 50px; min-height: 50px; max-width: 450px; min-width: 450px; width: 450px;">
                                            <div style="text-align: center; overflow:auto;  white-space:nowrap; resize: none;   ">
                                                <?= "<b style='vertical-align: middle; overflow: hidden'>$scan->host</b>" ?>
                                            </div>
                                        </td>

                                        <td style="text-align: center; min-width: 200px; width: 200px">
                                            <a class="btn btn-success btn-sm"
                                               href="<?= Url::toRoute(['/scan/scanresult', 'id' => $scan->taskid]) ?>">
                                                Scan results</a>

                                            <div class="btn btn-success btn-xs" id="hidebutton"
                                                 onclick="hide(0, <?php echo $scan->taskid ?>);">Hide
                                            </div>

                                            <div class="btn btn-success btn-xs" id="deletebutton"
                                                 onclick="deletefunc(<?php echo $scan->taskid ?>);">Delete
                                            </div>
                                        </td>
                                    </tr>


                                <?php endforeach; ?>

                                </tbody>
                            </table>

                            <div class="btn btn-success btn-xs" id="deletedonebutton" style=" margin-left: -15%;" onclick="
                                    <?php foreach ($done as $task): ?>

                                            <?php echo('deletefunc(' . $task->taskid . ');'); ?>

                                    <?php endforeach; ?>
                                "> Delete all done on page
                            </div>


                        </div>

                        <div class="running" style="width:35%; float: right;">
                            <h1 style="text-align:center; margin-left: -20%;">Running tasks</h1>

                            <table class="table table-bordered" style=" margin-left: -25%; margin-top: 20px;">
                                <thead>
                                <tr>
                                    <th style="text-align: center; max-width: 20%; min-width: 20%; width: 20%">ID</th>
                                    <th style="text-align: center; max-width: 50%; min-width: 50%; width: 50%">Host</th>
                                    <th style="text-align: center; max-width: 25%; min-width: 25%; width: 25%">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($running as $task): ?>

                                    <tr>
                                        <td style="text-align: center;">
                                            <?= "<b style='vertical-align: middle;'>$task->taskid</b>" ?>
                                        </td>

                                        <td style="text-align: center; height: 50px; min-height: 50px; max-width: 400px; min-width: 400px; width: 400px;">
                                            <div style="text-align: center; overflow:auto;  white-space:nowrap; resize: none; ">
                                                
                                                <a style="vertical-align: middle;"href="<?php 

                                                    if (stripos($task->host, 'http') !== false) { echo $task->host; }
                                                    else { echo 'http://'.$task->host; } ?>" rel="noreferrer"><?php echo $task->host; ?>
                                                    
                                                </a>

                                            </div>
                                        </td>

                                        <td style="text-align: center; min-width: 260px; width: 260px">
                                            <a class="btn btn-success btn-sm"
                                               href="<?= Url::toRoute(['/scan/scanresult', 'id' => $task->taskid]) ?>">
                                                Scan results</a>

                                            <div class="btn btn-success btn-xs" id="hidebutton"
                                                onclick="hide(0, <?php echo $task->taskid ?>);">Hide
                                            </div>

                                            <div class="btn btn-success btn-xs" id="deletebutton"
                                                onclick="deletefunc(<?php echo $task->taskid ?>);">Delete
                                            </div>

                                            <div class="btn btn-success btn-xs" id="<?php echo $task->host ?>"
                                                onclick="senddirscan(id);"> Dirscan
                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                        <div style="left: 45%; bottom: 10%; position: fixed; margin: 0px 0 0 0px;"><?= LinkPager::widget(['pagination' => $runningpages]) ?></div>
                        

                    </div>


                </div>

                <div id="passivescan" class="tab-pane fade">

                    <div class="container">

                        <ul class="nav nav-pills nav-stacked"
                            style="width: 15%; text-align: center; margin-top:2%; margin-left: -20%">
                            <li class="active"><a data-toggle="pill" href="#passive">Passive scans</a></li>
                            <!-- <li><a data-toggle="pill" href="#passivegit">Git scans</a></li> -->
                        </ul>

                        <div class="tab-content">
                            <div id="passive" class="tab-pane fade in active" style="margin-top: -6.5%">
                                
                                <table class="table table-bordered" style="margin-top: 3%">
                                    <thead>
                                    <tr style="text-align: center">
                                        <th>ID</th>
                                        <th>Nmap Domain</th>
                                        <th>Amass Domain</th>
                                        <th>Dirscan Url</th>
                                        <th>Active</th>
                                        <th>Notifications</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($passive as $scan): ?>

                                        <tr style="text-align: center">
                                            <td>
                                                <?= "<b style='vertical-align: middle;'>$scan->PassiveScanid</b>" ?>
                                            </td>

                                            <td>
                                                <?= "<b style='vertical-align: middle;'>$scan->nmapDomain</b>" ?>
                                            </td>
                                            <td>
                                                <?= "<b style='vertical-align: middle;'>$scan->amassDomain</b>" ?>
                                            </td>
                                            <td>
                                                <?= "<b style='vertical-align: middle;'>$scan->dirscanUrl</b>" ?>
                                            </td>

                                            <td>
                                                <?= "<b style='vertical-align: middle;'>$scan->is_active</b>" ?>
                                            </td>

                                            <td>
                                                <?= "<b style='vertical-align: middle;'>$scan->notifications_enabled</b>" ?>
                                            </td>

                                            <td style="text-align: center">
                                                <a class="btn btn-success btn-sm"
                                                   href="<?= Url::toRoute(['/scan/passivescanresult', 'id' => $scan->PassiveScanid]) ?>">
                                                    Scan results</a>


                                                <?php if ($scan->notifications_enabled === 1): ?>
                                                    <div class="btn btn-success btn-sm" id="onbutton"
                                                         onclick="sendnotifications(0, <?php echo $scan->PassiveScanid ?>);">
                                                        Turn
                                                        notifications off
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($scan->notifications_enabled === 0): ?>
                                                    <div class="btn btn-success btn-sm" id="offbutton"
                                                         onclick="sendnotifications(1, <?php echo $scan->PassiveScanid ?>);">
                                                        Turn
                                                        notifications on
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($scan->is_active === 1): ?>
                                                    <div class="btn btn-success btn-sm" id="onbutton"
                                                         onclick="sendactive(0, <?php echo $scan->PassiveScanid ?>);">Turn scan
                                                        off
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($scan->is_active === 0): ?>
                                                    <div class="btn btn-success btn-sm" id="offbutton"
                                                         onclick="sendactive(1, <?php echo $scan->PassiveScanid ?>);">Turn scan
                                                        on
                                                    </div>
                                                <?php endif; ?>

                                            </td>
                                        </tr>

                                    <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <div style="left: 45%; bottom: 10%; position: fixed; margin: 0px 0 0 0px;"><?= LinkPager::widget(['pagination' => $passivepages]) ?></div>

                            </div>


                            <!-- <div id="passivegit" class="tab-pane fade" style="margin-top: -9.5%">

                                <h3>Menu 1</h3>
                                <p>Some content in menu 1.</p>

                                <?php foreach ($passive as $scan): ?>

                                    <a class="btn btn-success btn-sm"
                                       href="<?= Url::toRoute(['/scan/gitpassivescanresult', 'id' => $scan->PassiveScanid]) ?>">
                                        Scan results</a>

                                <?php endforeach; ?>

                            </div> -->

                        </div>


                    </div>
                </div>

                <div id="hiddenscans" class="tab-pane fade">

                    <div class="container">

                        <table class="table table-bordered" style="width: 100%; margin-top: 5%">
                            <thead>
                            <tr>
                                <th style="text-align: center">ID</th>
                                <th style="text-align: center">Host</th>
                                <th style="text-align: center">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($hidden as $scan): ?>

                                <tr>
                                    <td style="text-align: center; height: 40px; min-height: 40px;">
                                        <?= "<b style='vertical-align: middle; '>$scan->taskid</b>" ?>
                                    </td>

                                    <td style="text-align: center; max-width: 400px; height: 50px; min-height: 50px;">
                                        <div style="text-align: center; overflow:auto; text-overflow: ellipsis; white-space:nowrap">
                                            <?= "<b style='vertical-align: middle; overflow: hidden'>$scan->host</b>" ?>
                                        </div>
                                    </td>

                                    <td style="text-align: center; height: 40px; min-height: 40px;">

                                        <a class="btn btn-success btn-sm"
                                           href="<?= Url::toRoute(['/scan/scanresult', 'id' => $scan->taskid]) ?>">
                                            Scan results</a>

                                        <div class="btn btn-success btn-xs" id="hidebutton"
                                             onclick="hide(1, <?php echo $scan->taskid ?>);">Unhide
                                        </div>

                                    </td>
                                </tr>


                            <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div style="left: 45%; bottom: 10%; position: fixed; margin: 0px 0 0 0px;"><?= LinkPager::widget(['pagination' => $hiddenpages]) ?></div>

                    </div>

                </div>

            </div>


            <?php ActiveForm::end(); ?>


        </div>


    <?php endif; ?>

</div>


