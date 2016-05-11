<?php

namespace app\controllers;

use app\models\Users;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\SellerRegistrationForm;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
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

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionRegistration()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new SellerRegistrationForm();
        if ($model->load(Yii::$app->request->post()) && $model->Registration()) {
            $users_model = new Users();
            $users_model->email = Yii::$app->request->post()['SellerRegistrationForm']['email'];
            $users_model->username = Yii::$app->request->post()['SellerRegistrationForm']['username'];
            $users_model->password = Yii::$app->request->post()['SellerRegistrationForm']['password'];
            $users_model->telephone = Yii::$app->request->post()['SellerRegistrationForm']['telephone'];
            $users_model->first_name = Yii::$app->request->post()['SellerRegistrationForm']['first_name'];
            $users_model->last_name = Yii::$app->request->post()['SellerRegistrationForm']['last_name'];

            if($users_model->save()){
                return $this->redirect('login');
            }else{
                foreach ($users_model->getErrors() as $key=>$value){
                    if(isset($value[0])){
                        $model->addError($key,$value[0]);
                    }
                }
            }
        }
        return $this->render('registration', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionAbout()
    {
        return $this->render('about');
    }
}
