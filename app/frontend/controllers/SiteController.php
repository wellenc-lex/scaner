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
use frontend\models\Dirscan;
use Yii;
use yii\base\InvalidParamException;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

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
                ->orderBy(['notify_instrument ' => SORT_DESC]);

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

            $doneresult = $done->orderBy('taskid')
                ->offset($donepages->offset)
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

            $PassiveNotify = PassiveScan::find()
                ->andWhere(['userid' => Yii::$app->user->id])
                ->andWhere(['needs_to_notify' => "1"])
                ->count();

            if ($PassiveNotify > 0) $needstonotify = 1;

            else $needstonotify = 0;

            return $this->render('profile', [
                'done' => $doneresult,
                'running' => $tasksresult,
                'passive' => $passiveresult,
                'hidden' => $hiddenresult,
                'passivepages' => $passivepages,
                'donepages' => $donepages,
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

                    $nmap = 0;
                    $amass = 0;
                    $dirscan = 0;
                    $gitscan = 0;
                    $ips = 0;
                    $vhost = 0;
                    $race = 0;
                    $reverseip = 0;

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

                            $tasks = new Tasks();

                            preg_match_all("/(https?:\/\/)?([\w\-\_\d\.][^\/\:\&]+)/i", $url["amassDomain"], $domain);

                            $url["amassDomain"] = $domain[2][0];
                            
                            $DomainsAlreadyinDB = Tasks::find()
                                ->andWhere(['userid' => Yii::$app->user->id])
                                ->andWhere(['=', 'host', $url["amassDomain"]])
                                ->exists(); 

                            if($DomainsAlreadyinDB == 0){

                                $tasks->host = $url["amassDomain"];
                                $tasks->amass_status = "Working";
                                $tasks->notify_instrument = $tasks->notify_instrument . "2";
                                $amass = 1;

                                $queue = new Queue();
                                $queue->amassdomain = $url["amassDomain"];
                                $queue->taskid = $tasks->taskid;
                                $queue->instrument = 2;
                                $queue->save();

                                //adds the domain to scan it later continiously
                                if ($url["passive"] == 1) {
                                    $passive = new PassiveScan();
                                    $passive->userid = Yii::$app->user->id;
                                    $passive->notifications_enabled = 1;
                                    $passive->amassDomain = $url["amassDomain"];
                                    $passive->scanday = rand(1, 30);
                                    $passive->save();
                                }
                            }

                            $tasks->userid = Yii::$app->user->id;
                            $tasks->notification_enabled = $url["notify"];
                            $tasks->save();
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

                    if ($nmap == 0 && $amass == 0 && $dirscan == 0 && $gitscan == 0 && $ips == 0 && $vhost == 0 && $race == 0 && $reverseip == 0 && $nuclei == 0 && $jsa == 0) {

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
