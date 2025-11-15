<?php

namespace frontend\modules\admin\controllers;

use frontend\modules\admin\models\PassiveScan;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * PassiveController implements the CRUD actions for PassiveScan model.
 */
class PassiveController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all PassiveScan models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest) {
            if (Yii::$app->user->identity->rights == 1) {

                $dataProvider = new ActiveDataProvider([
                    'query' => PassiveScan::find(),
                ]);

                return $this->render('index', [
                    'dataProvider' => $dataProvider,
                ]);
            }
        }
    }

    /**
     * Displays a single PassiveScan model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        if (!Yii::$app->user->isGuest) {
            if (Yii::$app->user->identity->rights == 1) {

                return $this->render('view', [
                    'model' => $this->findModel($id),
                ]);
            }
        }
    }

    /**
     * Creates a new PassiveScan model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->isGuest) {
            if (Yii::$app->user->identity->rights == 1) {

                $model = new PassiveScan();

                if ($model->load(Yii::$app->request->post()) && $model->save()) {
                    return $this->redirect(['view', 'id' => $model->scanid]);
                }

                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Updates an existing PassiveScan model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        if (!Yii::$app->user->isGuest) {
            if (Yii::$app->user->identity->rights == 1) {

                $model = $this->findModel($id);

                if ($model->load(Yii::$app->request->post()) && $model->save()) {
                    return $this->redirect(['view', 'id' => $model->scanid]);
                }

                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    public function actionDelete($id)
    {
        if (!Yii::$app->user->isGuest) {
            if (Yii::$app->user->identity->rights == 1) {

                $this->findModel($id)->delete();

                return $this->redirect(['index']);
            }
        }
    }

    /**
     * Finds the PassiveScan model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PassiveScan the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PassiveScan::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
