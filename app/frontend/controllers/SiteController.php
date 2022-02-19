<?php

namespace frontend\controllers;

use common\models\LoginForm;
use common\models\User;
use frontend\models\ContactForm;
use frontend\models\Newscan;
use frontend\models\PassiveScan;
use frontend\models\PasswordResetRequestForm;
use frontend\models\Profile;
use frontend\models\Queue;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\Tasks;
use frontend\models\Amassintel;
use frontend\models\Dirscan;
use Yii;
use yii\base\InvalidParamException;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use frontend\models\Aquatone;

ini_set('max_execution_time', 0);

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true, //change to false if you don't wanna allow registration
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'frontend\models\CaptchaAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $this->layout = 'fluid';
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {

        $model = new ContactForm();

            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                    Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
                } else {
                    Yii::$app->session->setFlash('error', 'There was an error sending your message.');
                }

                return $this->refresh();
            } else {
                return $this->render('contact', [
                    'model' => $model,
                ]);
            }
    }

    /**
     * Profile - scans, running tasks.
     */

    public function actionProfile()
    {

        if (!Yii::$app->user->isGuest) {
            
            $model = new Profile();

            $done = Tasks::find()
                ->select(['tasks.taskid','tasks.status', 'tasks.host'])
                ->andWhere(['userid' => Yii::$app->user->id])
                ->andWhere(['status' => "Done."])
                ->andWhere(['hidden' => "0"])
                ->orderBy(['notify_instrument' => SORT_DESC, 'taskid' => SORT_ASC]);

            $tasks = Tasks::find()
                ->select(['tasks.taskid','tasks.status', 'tasks.host'])
                ->andWhere(['hidden' => "0"])
                ->andWhere(['userid' => Yii::$app->user->id]);

            $hidden = Tasks::find()
                ->select(['tasks.taskid','tasks.status', 'tasks.host'])
                ->andWhere(['userid' => Yii::$app->user->id])
                ->andWhere(['hidden' => "1"]);

            $passive = PassiveScan::find()
                ->andWhere(['userid' => Yii::$app->user->id]);

            $donepages = new Pagination([
                'defaultPageSize' => 50,
                'totalCount' => $done->count(),
            ]);

            $doneresult = $done->offset($donepages->offset)
                ->limit($donepages->limit)
                ->all();

            $taskspages = new Pagination([
                'defaultPageSize' => 10,
                'totalCount' => $tasks->count(),
            ]);

            $tasksresult = $tasks->orderBy('taskid')
                ->offset($taskspages->offset)
                ->limit($taskspages->limit)
                ->andWhere(['!=', 'status', "Done."])
                ->all();

            $passivepages = new Pagination([
                'defaultPageSize' => 10,
                'totalCount' => $passive->count(),
            ]);

            $passiveresult = $passive->orderBy(['is_active' => SORT_DESC, 'PassiveScanid' => SORT_ASC])
                ->offset($passivepages->offset)
                ->limit($passivepages->limit)
                ->all();

            $hiddenpages = new Pagination([
                'defaultPageSize' => 10,
                'totalCount' => $hidden->count(),
            ]);

            $hiddenresult = $hidden->orderBy('taskid')
                ->offset($hiddenpages->offset)
                ->limit($hiddenpages->limit)
                ->all();

            /*$GitscanPassive = GitscanPassive::find()
                ->andWhere(['userid' => Yii::$app->user->id])
                ->andWhere(['is_active' => "1"])
                ->andWhere(['needs_to_notify' => "1"])
                ->andWhere(['viewed' => "0"])
                ->count();*/

            $amassIntel = Tasks::find()
                ->select(['tasks.taskid','tasks.status', 'tasks.host'])
                ->andWhere(['userid' => Yii::$app->user->id])
                ->andWhere(['!=', 'amass_intel', ""]);

            $amassIntelpages = new Pagination([
                'defaultPageSize' => 50,
                'totalCount' => $amassIntel->count(),
            ]);

            $amassIntelresult = $amassIntel->offset($amassIntelpages->offset)
                ->limit($amassIntelpages->limit)
                ->all();

            $PassiveNotify = PassiveScan::find()
                ->andWhere(['userid' => Yii::$app->user->id])
                ->andWhere(['needs_to_notify' => "1"])
                ->count();

            if ($PassiveNotify > 0) $needstonotify = 1;

            else $needstonotify = 0;

            return $this->render('profile', [
                'done' => $doneresult,
                'donepages' => $donepages,
                'running' => $tasksresult,
                'passive' => $passiveresult,
                'hidden' => $hiddenresult,
                'passivepages' => $passivepages,
                'amassIntel' => $amassIntelresult,
                'amassIntelpages' => $amassIntelpages,
                'runningpages' => $taskspages,
                'hiddenpages' => $hiddenpages,
                'notify' => $needstonotify,
                'model' => $model,
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'Only registered users can visit this page.');
            return $this->redirect(['/site/login']);
        }
    }

    /**
     * Executes new scan in model function, starts scan's controllers.
     * curl --insecure  for not waiting the response.
     * 1800 == 30 minutes.
     */

    public function actionNewscan()
    {

        $model = new Newscan();

        if (!Yii::$app->user->isGuest) {

            $model->notify = 1;

            if ($model->load(Yii::$app->request->post()) && $model->validate()) {

                $user = User::find()
                    ->where(['id' => Yii::$app->user->id])
                    ->limit(1)
                    ->one();

                $url = Yii::$app->request->post('Newscan');

                if ($url["activescan"] == 1) {

                    if ($user->scans_counter > 150000) {
                        if ($user->updated_at < (time() - 1800)) {
                            $user->scans_counter = 0;
                        } else {
                            $user->scan_timeout = (time() + 1800);
                            $user->updated_at = time();
                            $user->scans_counter = 0;
                        }
                    }

                    if ($user->scan_timeout > time()) {

                        $user->save();

                        if (Yii::$app->request->isAjax) {
                            return Yii::$app->response->statusCode = 403;
                        }

                        Yii::$app->session->setFlash('error', 'You had exceed your 15 scans per 30mins limit. Please wait until limitation ends.');
                        return $this->redirect(['/site/profile']);
                    }

                    global $nmap;
                    global $amass;
                    global $dirscan;
                    global $gitscan;
                    global $ips;
                    global $vhost;
                    global $race;
                    global $reverseip;
                    global $whatweb;

                    $nmap = 0;
                    $amass = 0;
                    $dirscan = 0;
                    $gitscan = 0;
                    $ips = 0;
                    $vhost = 0;
                    $race = 0;
                    $reverseip = 0;
                    $whatweb = 0;

                    $auth = getenv('Authorization', 'Basic bmdpbng6QWRtaW4=');
                    $secret = getenv('api_secret', 'secretkeyzzzzcbv55');
                    //checks if at least 1 instrument exists

                    if (isset($url["nmapDomain"]) && $url["nmapDomain"] != "") {

                        $tasks = new Tasks();
                    
                        $nmap = 1;
                        $tasks->host = rtrim($url["nmapDomain"], ',');
                        $tasks->nmap_status = "Working";
                        $tasks->notify_instrument = $tasks->notify_instrument . "1"; //1==nmap

                        $queue = new Queue();
                        $queue->nmap = $url["nmapDomain"];
                        $queue->taskid = $tasks->taskid;
                        $queue->instrument = 1;
                        $queue->save();

                        $tasks->userid = Yii::$app->user->id;
                        $tasks->notification_enabled = $url["notify"];
                        $tasks->save();
                    }

                    if (isset($url["amassDomain"]) && $url["amassDomain"] != "") {

                        $urls = explode(PHP_EOL, $url["amassDomain"]); 

                        foreach ($urls as $currenturl){

                            preg_match_all("/(https?:\/\/)?([\w\-\_\d\.][^\/\:\&\[\r\n\?]+)/i", $currenturl, $domain);

                            $currentdomain = $domain[2][0];
                            
                            $DomainsAlreadyinDB = Tasks::find()
                                ->andWhere(['userid' => Yii::$app->user->id])
                                ->andWhere(['=', 'host', $currentdomain])
                                ->exists(); 

                            if($DomainsAlreadyinDB == 0 && !is_null($currentdomain) ){

                                $tasks = new Tasks();

                                $tasks->host = $currentdomain;
                                $tasks->amass_status = "Working";
                                $tasks->notify_instrument = "2";
                                $amass = 1;

                                $queue = new Queue();
                                $queue->amassdomain = $currentdomain;
                                $queue->taskid = $tasks->taskid;
                                $queue->instrument = 2;
                                $queue->save();

                                //adds the domain to scan it later continiously
                                if ($url["passive"] == 1) {
                                    $passive = new PassiveScan();
                                    $passive->userid = Yii::$app->user->id;
                                    $passive->notifications_enabled = 1;
                                    $passive->amassDomain = $currentdomain;
                                    $passive->scanday = rand(1, 30);
                                    $passive->save();
                                }

                                $tasks->userid = Yii::$app->user->id;
                                $tasks->notification_enabled = $url["notify"];
                                $tasks->save();
                            }
                        }
                    }

                    if (isset($url["dirscanUrl"]) && $url["dirscanUrl"] != "") {
                        
                        $hostnames = array();

                        $urls = explode(PHP_EOL, $url["dirscanUrl"]);

                        $urls = array_unique($urls); 

                        rsort($urls);

                        foreach ($urls as $currenturl){

                            if ($currenturl != "") { //if isnt empty
                                $currenthost = dirscan::ParseHostname($currenturl).dirscan::ParsePort($currenturl);

                                $scheme = dirscan::ParseScheme($currenturl);
                                $port = dirscan::ParsePort($currenturl);

                                if( ($scheme==="http://" && $port===":443") || ($scheme==="https://" && $port===":80") ){
                                    continue; //scanning https port with http scheme is pointless so we get to the next host
                                }

                                if( dirscan::bannedsubdomains($currenthost) !== 0 ){
                                    continue; //no need to ffuf subdomain like docs.smth.com - low chance of juicy fruits here
                                }

                                if( !in_array($currenthost, $hostnames ) ){

                                    $tasks = new Tasks();

                                    $tasks->host = dirscan::ParseScheme($currenturl).$currenthost;

                                    $queue = new Queue();
                                    
                                    $queue->taskid = $tasks->taskid;
                                    $queue->instrument = 3;

                                    if (isset($url["dirscanIp"]) && $url["dirscanIp"] != "") {
                                        $queue->dirscanIP = dirscan::ParseIP($url["dirscanIp"]);
                                    }

                                    $queue->dirscanUrl = dirscan::ParseScheme($currenturl).$currenthost; //slice #? and other stuff
                                    $queue->save();

                                    $tasks->dirscan_status = "Working";
                                    $tasks->notify_instrument = $tasks->notify_instrument . "3";

                                    $hostnames[] = $currenthost;

                                    $tasks->userid = Yii::$app->user->id;
                                    $tasks->save();
                                }
                            }
                        }

                        $dirscan = 1;
                    }

                    if (isset($url["gitUrl"]) && $url["gitUrl"] != "") {
                        $tasks = new Tasks();

                        $tasks->gitscan_status = "Working";
                        $tasks->notify_instrument = $tasks->notify_instrument . "4";
                        $gitscan = 1;
                        //exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "url=' . $url["gitUrl"] . ' & taskid=' . $tasks->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/gitscan > /dev/null 2>/dev/null &');

                        $tasks->userid = Yii::$app->user->id;
                        $tasks->save();
                    }

                    if (isset($url["reverseip"]) && $url["reverseip"] != "") {
                        $tasks = new Tasks();

                        $tasks->host = $url["reverseip"];
                        $tasks->reverseip_status = "Working";
                        $tasks->notify_instrument = $tasks->notify_instrument . "5";
                        $reverseip = 1;
                        //exec('curl --insecure -H \'Authorization: ' . $auth . '\'  --data "url=' . $url["reverseip"] . ' & taskid=' . $tasks->taskid . ' & secret=' . $secret . '" https://dev.localhost.soft/scan/reverseipscan > /dev/null 2>/dev/null &');

                        $tasks->userid = Yii::$app->user->id;
                        $tasks->save();
                    }

                    if (isset($url["ips"]) && $url["ips"] != "") {
                        $tasks = new Tasks();

                        $tasks->host = $url["ips"];
                        $tasks->ips_status = "Working";
                        $tasks->notify_instrument = $tasks->notify_instrument . "6";
                        
                        $ips = 1;

                        $queue = new Queue();
                        $queue->taskid = $tasks->taskid;
                        $queue->instrument = 6;
                        $queue->ipscan = $url["ips"];
                        $queue->save();

                        $tasks->userid = Yii::$app->user->id;
                        $tasks->save();
                    }

                    if (isset($url["whatwebUrl"]) && $url["whatwebUrl"] != "") {
                        
                        $hostnames = array();

                        $urls = explode(PHP_EOL, $url["whatwebUrl"]);

                        $urls = array_unique($urls); 

                        rsort($urls);

                        foreach ($urls as $currenturl){

                            if ($currenturl != "") { //if isnt empty
                                $currenthost = dirscan::ParseHostname($currenturl).dirscan::ParsePort($currenturl);

                                $scheme = dirscan::ParseScheme($currenturl);
                                $port = dirscan::ParsePort($currenturl);

                                if( ($scheme==="http://" && $port===":443") || ($scheme==="https://" && $port===":80") ){
                                    continue; //scanning https port with http scheme is pointless so we get to the next host
                                }

                                if( !in_array($currenthost, $hostnames ) ){

                                    $queue = new Queue();
                                    
                                    $queue->instrument = 5;

                                    $queue->dirscanUrl = dirscan::ParseScheme($currenturl).$currenthost; //slice #? and other stuff
                                    
                                    $queue->save();

                                    $hostnames[] = $currenthost;
                                }
                            }
                        }

                        $whatweb = 1;
                    }

                    if ((isset($url["vhostDomain"]) && $url["vhostDomain"] != "") && (isset($url["vhostIp"]) && $url["vhostIp"] != "")) {
                        $tasks = new Tasks();

                        $tasks->vhost_status = "Working";
                        $tasks->notify_instrument = $tasks->notify_instrument . "7";
                        $vhost = 1;

                        $queue = new Queue();
                        $queue->taskid = $tasks->taskid;
                        $queue->instrument = 7;


                        if ((isset($url["vhostPort"]) && $url["vhostPort"] != "")) {

                            if (isset($url["vhostSsl"]) && $url["vhostSsl"] === 1) {

                                $queue->vhostdomain = $url["vhostDomain"];
                                $queue->vhostip = $url["vhostIp"];
                                $queue->vhostport = $url["vhostPort"];
                                $queue->vhostssl = 1;
                                $queue->save();

                            } else {

                                $queue->vhostdomain = $url["vhostDomain"];
                                $queue->vhostip = $url["vhostIp"];
                                $queue->vhostport = $url["vhostPort"];
                                $queue->vhostssl = 0;
                                $queue->save();
                            }

                        } else {

                            $queue->vhostdomain = $url["vhostDomain"];
                            $queue->vhostip = $url["vhostIp"];
                            $queue->vhostport = "80";
                            $queue->vhostssl = 0;
                            $queue->save();
                        }

                        $tasks->userid = Yii::$app->user->id;
                        $tasks->save();

                    }

                    if ((isset($url["nucleiDomain"]) && $url["nucleiDomain"] != "") ) {
                        $tasks = new Tasks();

                        $tasks->dirscan_status = "Working";
                        $tasks->notify_instrument = $tasks->notify_instrument . "8";
                        $nuclei = 1;

                        $queue = new Queue();
                        $queue->taskid = $tasks->taskid;
                        $queue->instrument = 8;
                        $queue->dirscanUrl = $url["nucleiDomain"];
                        $queue->save();

                        $tasks->userid = Yii::$app->user->id;
                        $tasks->save();
                    }

                    if ((isset($url["jsaDomain"]) && $url["jsaDomain"] != "") ) {
                        $tasks = new Tasks();

                        $tasks->js_status = "Working";
                        $tasks->notify_instrument = $tasks->notify_instrument . "9";
                        $nuclei = 1;

                        $queue = new Queue();
                        $queue->taskid = $tasks->taskid;
                        $queue->instrument = 9;
                        $queue->dirscanUrl = $url["jsaDomain"];
                        $queue->save();

                        $tasks->userid = Yii::$app->user->id;
                        $tasks->save();
                    }

                    /*if (isset($url["raceUrl"]) && $url["raceUrl"] != "") {
                        $race = 1;

                        $cookies = $url["raceCookies"];
                        $cookies = str_replace(",", '","', $cookies);
                        $cookies = str_replace(";", '","', $cookies);

                        $body = $url["raceBody"];
                        $body = str_replace(",", '&', $body);
                        $body = str_replace(";", '&', $body);

                        if (isset($url["raceHeaders"]) && $url["raceHeaders"] != "")
                            $headers = $url["raceHeaders"];
                        else
                            $headers = "";

                        $headers = str_replace(",", '","', $headers);
                        $headers = str_replace(";", '","', $headers);

                        $requests = '"requests": [
                                {
                                    "method": "POST",
                                    "url": "' . $url["raceUrl"] . '",
                                    "cookies": ["' . $cookies . '"],
                                    "Headers": ["' . $headers . '"],
                                    "Body": "' . $body . '",
                                    "Redirects": true
                                }
                            ]';

                        exec('curl --insecure  -d \'{"count":100,"verbose":false,' . $requests . '}\' -H "Content-Type: application/json" -X POST http://127.0.0.1:8000/set/config');

                        exec("curl --insecure  -X POST http://127.0.0.1:8000/start > /dev/null 2>/dev/null &");

                        if ($nmap == 0 && $amass == 0 && $dirscan == 0 && $gitscan == 0 && $ips == 0 && $vhost == 0 && $reverseip == 0 && $race == 1) {
                            Yii::$app->session->setFlash('success', 'Your scan should start shortly, you can check its result at profile tab.');
                            $user->updateCounters(['scans_counter' => 1]);
                            $tasks->delete();
                            return $this->redirect(['/site/profile']);
                        }
                    }*/

                    if ($nmap == 0 && $amass == 0 && $dirscan == 0 && $gitscan == 0 && $ips == 0 && $vhost == 0 && $race == 0 && $reverseip == 0 && $nuclei == 0 && $jsa == 0 && $whatweb == 0) {

                        Yii::$app->session->setFlash('failure', 'You provided empty instrument\'s parameters. Please try again.');
                        





                        //return $this->redirect(['/site/newscan']);

                    }

                    $user->updated_at = time();
                    $user->save();
                    $user->updateCounters(['scans_counter' => 1]);

                    if (Yii::$app->request->isAjax) {
                        return Yii::$app->response->statusCode = 200;
                    }

                    Yii::$app->session->setFlash('success', 'Your scan should start shortly, you can check its result at profile tab.');

                    return $this->redirect(['/site/newscan']);

                } elseif ($url["passivescan"] == 1) {

                    $count = PassiveScan::find()
                        ->where(['userid' => Yii::$app->user->id])
                        ->andWhere(['is_active' => 1])
                        ->count();

                    if ($count < 300) {

                        $passive = new PassiveScan();

                        $nmap = 0;
                        $amass = 0;
                        $dirscan = 0;

                        $passive->userid = Yii::$app->user->id;

                        if ($url["notify"] == 1)
                            $passive->notifications_enabled = 1;
                        else
                            $passive->notifications_enabled = 0;

                        if (isset($url["nmapDomain"]) && $url["nmapDomain"] != "") {
                            $passive->nmapDomain = $url["nmapDomain"];
                            $passive->scanday = rand(1, 28);
                            $nmap = 1;
                        }

                        if (isset($url["amassDomain"]) && $url["amassDomain"] != "") {
                            $passive->amassDomain = $url["amassDomain"];
                            $passive->scanday = rand(1, 28);
                            $amass = 1;
                        }

                        if (isset($url["dirscanUrl"]) && $url["dirscanUrl"] != "") {
                            $passive->dirscanUrl = $url["dirscanUrl"];
                            $passive->scanday = rand(1, 28);
                            $dirscan = 1;
                        }

                        if ($nmap == 0 && $amass == 0 && $dirscan == 0) {
                            Yii::$app->session->setFlash('failure', 'You provided empty instrument\'s parameters. Please try again.');
                            return $this->redirect(['/site/newscan']);
                        }

                        $passive->save();

                        Yii::$app->session->setFlash('success', 'Your scan should start shortly, you can check its result at profile tab.');

                        return $this->redirect(['/site/newscan']);
                    } else
                        Yii::$app->session->setFlash('failure', 'You\'re allowed to create only 3 passive scans. Please remove unneeded in profile');
                    return $this->redirect(['/site/profile']);
                }

                if (Yii::$app->request->isAjax) {
                    return Yii::$app->response->statusCode = 403;
                }

                Yii::$app->session->setFlash('error', 'You had exceed your 15 scans per 30mins limit. Please wait until limitation ends.');
                return $this->redirect(['/site/profile']);
            } else {
                return $this->render('newscan', [
                    'model' => $model,
                ]);
            }
        } else {
            Yii::$app->session->setFlash('error', 'Please login first.');
            return $this->redirect(['/site/login']);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        $xmls = [
"/dockerresults/325889636nmap.xml",
"/dockerresults/55059194nmap.xml",
"/dockerresults/943649441nmap.xml",
"/dockerresults/532515659nmap.xml",
"/dockerresults/714636115nmap.xml",
"/dockerresults/990972258nmap.xml",
"/dockerresults/848424741nmap.xml",
"/dockerresults/583940136nmap.xml",
"/dockerresults/17720961nmap.xml",
"/dockerresults/831447997nmap.xml",
"/dockerresults/675489331nmap.xml",
"/dockerresults/826758581/nmap.xml",
"/dockerresults/729863895nmap.xml",
"/dockerresults/570363524nmap.xml",
"/dockerresults/436083824nmap.xml",
"/dockerresults/976708667nmap.xml",
"/dockerresults/23144140nmap.xml",
"/dockerresults/677151486nmap.xml",
"/dockerresults/828744889nmap.xml",
"/dockerresults/544767188nmap.xml",
"/dockerresults/40874782nmap.xml",
"/dockerresults/886867016nmap.xml",
"/dockerresults/401023704/nmap.xml",
"/dockerresults/214347597nmap.xml",
"/dockerresults/281863483nmap.xml",
"/dockerresults/3911445nmap.xml",
"/dockerresults/22545348nmap.xml",
"/dockerresults/779768412/nmap.xml",
"/dockerresults/792226773nmap.xml",
"/dockerresults/579345472nmap.xml",
"/dockerresults/780606999nmap.xml",
"/dockerresults/932423379/nmap.xml",
"/dockerresults/777142532nmap.xml",
"/dockerresults/916774169nmap.xml",
"/dockerresults/471608859/nmap.xml",
"/dockerresults/720688585nmap.xml",
"/dockerresults/338415528/nmap.xml",
"/dockerresults/174063214nmap.xml",
"/dockerresults/130602686nmap.xml",
"/dockerresults/793194480nmap.xml",
"/dockerresults/40282151nmap.xml",
"/dockerresults/812628349nmap.xml",
"/dockerresults/798222689nmap.xml",
"/dockerresults/217281187nmap.xml",
"/dockerresults/707455652nmap.xml",
"/dockerresults/993432225nmap.xml",
"/dockerresults/142742365nmap.xml",
"/dockerresults/140403237nmap.xml",
"/dockerresults/892710276nmap.xml",
"/dockerresults/416149449/nmap.xml",
"/dockerresults/213555214nmap.xml",
"/dockerresults/6007165nmap.xml",
"/dockerresults/465419306nmap.xml",
"/dockerresults/396565297nmap.xml",
"/dockerresults/606041694nmap.xml",
"/dockerresults/554037488nmap.xml",
"/dockerresults/36430483nmap.xml",
"/dockerresults/668719709nmap.xml",
"/dockerresults/662751154nmap.xml",
"/dockerresults/707945843nmap.xml",
"/dockerresults/809996977nmap.xml",
"/dockerresults/857464584/nmap.xml",
"/dockerresults/496637844nmap.xml",
"/dockerresults/569848514/nmap.xml",
"/dockerresults/15312580/nmap.xml",
"/dockerresults/528342521nmap.xml",
"/dockerresults/510254396/nmap.xml",
"/dockerresults/939607957nmap.xml",
"/dockerresults/468401968nmap.xml",
"/dockerresults/64978268nmap.xml",
"/dockerresults/46790723nmap.xml",
"/dockerresults/274254322nmap.xml",
"/dockerresults/864643905nmap.xml",
"/dockerresults/516577016nmap.xml",
"/dockerresults/26057007/nmap.xml",
"/dockerresults/557340033nmap.xml",
"/dockerresults/545396200nmap.xml",
"/dockerresults/717258275nmap.xml",
"/dockerresults/229739638nmap.xml",
"/dockerresults/537202865nmap.xml",
"/dockerresults/969014679nmap.xml",
"/dockerresults/660970978nmap.xml",
"/dockerresults/911250491nmap.xml",
"/dockerresults/189751352nmap.xml",
"/dockerresults/425205965nmap.xml",
"/dockerresults/159963230nmap.xml",
"/dockerresults/775509494nmap.xml",
"/dockerresults/27134661nmap.xml",
"/dockerresults/668278171nmap.xml",
"/dockerresults/474324479nmap.xml",
"/dockerresults/708209007nmap.xml",
"/dockerresults/69104971nmap.xml",
"/dockerresults/571703513nmap.xml",
"/dockerresults/420199496nmap.xml",
"/dockerresults/340879923nmap.xml",
"/dockerresults/915680373nmap.xml",
"/dockerresults/436350813/nmap.xml",
"/dockerresults/167531210nmap.xml",
"/dockerresults/647595191/nmap.xml",
"/dockerresults/96734954nmap.xml",
"/dockerresults/127133609nmap.xml",
"/dockerresults/965799514nmap.xml",
"/dockerresults/897092158nmap.xml",
"/dockerresults/489534327/nmap.xml",
"/dockerresults/564875761/nmap.xml",
"/dockerresults/489371813nmap.xml",
"/dockerresults/257972141nmap.xml",
"/dockerresults/336549619nmap.xml",
"/dockerresults/237098928nmap.xml",
"/dockerresults/184724787nmap.xml",
"/dockerresults/690846117/nmap.xml",
"/dockerresults/532473154nmap.xml",
"/dockerresults/497283144nmap.xml",
"/dockerresults/803252359nmap.xml",
"/dockerresults/336693153nmap.xml",
"/dockerresults/217028047nmap.xml",
"/dockerresults/52373786nmap.xml",
"/dockerresults/735408538/nmap.xml",
"/dockerresults/524726201nmap.xml",
"/dockerresults/470340043nmap.xml",
"/dockerresults/654564109nmap.xml"];
$id=1;
foreach ($xmls as $xml) {
    aquatone::aquatone($id, $xml, 1);

    $id++;
    return 3;
}

        //sudo docker run --cpu-shares 1024 --rm -v dockerresults:/dockerresults projectdiscovery/httpx -exclude-cdn -ports 80,443,8080,8443,8000,3000,8083,8088,8888,8880,9999,10000,4443,6443,10250 -rate-limit 5 -timeout 15 -retries 5 -store-response -srd /dockerresults/2 -silent -o /dockerresults/2whatwebhttpx.txt -l /dockerresults/2whatwebhosts.txt
        /*$randomid = 2;

        $wordlist = "/dockerresults/" . $randomid . "whatwebhosts.txt";
        $output = "/dockerresults/" . $randomid . "whatwebhttpx.txt";

        $allresults = Tasks::find()
            ->select(['tasks.taskid','tasks.amass', ])
            ->andWhere(['not', ['tasks.amass' => null]])
            ->all();

        Yii::$app->db->close();

        $urls = array();

        foreach ($allresults as $results) {

            $amassoutput = json_decode($results->amass, true);

            if(!empty($amassoutput)) {

                foreach ($amassoutput as $amass) {

                    $urls[] = $amass["name"];
                }
            }
        }

        $urls = array_unique($urls);
        file_put_contents($wordlist, implode( PHP_EOL, $urls) );

        $httpx = "sudo docker run --cpu-shares 1024 --rm -v dockerresults:/dockerresults projectdiscovery/httpx -exclude-cdn -ports 80,443,8080,8443,8000,3000,8083,8088,8888,8880,9999,10000,4443,6443,10250 -rate-limit 5 -timeout 15 -retries 5 -store-response -srd /dockerresults/2 -silent -o ". $output ." -l ". $wordlist ."";
            
        exec($httpx);*/

        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
}
