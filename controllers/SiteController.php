<?php

namespace app\controllers;

use app\models\Users;
use Http\Adapter\Guzzle6\Client;
use Yii;
use yii\db\Connection;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\SellerRegistrationForm;
use app\models\UserRegistrationForm;
use twilio;
use Mailgun\Mailgun;
use yii\web\NotFoundHttpException;

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

    public function actionSellerRegistration()
    {

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $connection = Yii::$app->getDb();

        $transaction = $connection->beginTransaction();
        $model = new SellerRegistrationForm();
        if ($model->load(Yii::$app->request->post()) && $model->Registration()) {
            $country_code = '+374';
            $users_model = new Users();
            $users_model->role = 'seller';
            $users_model->email = Yii::$app->request->post()['SellerRegistrationForm']['email'];
            $users_model->username = Yii::$app->request->post()['SellerRegistrationForm']['username'];
            $users_model->password = Yii::$app->request->post()['SellerRegistrationForm']['password'];
            $users_model->telephone = $country_code . Yii::$app->request->post()['SellerRegistrationForm']['telephone'];
            $users_model->first_name = Yii::$app->request->post()['SellerRegistrationForm']['first_name'];
            $users_model->last_name = Yii::$app->request->post()['SellerRegistrationForm']['last_name'];
            $users_model->telephone_activation_token = rand(100000, 999999);

            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 255; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $users_model->telephone_activation_session = $randomString;

            if ($users_model->save()) {

                $AccountSid = "AC2ca4af6056334e3fda4dab1e7a9a2af0"; // Your Account SID from www.twilio.com/console
                $AuthToken = "919da45bf34778700cdc9d14af278289";   // Your Auth Token from www.twilio.com/console

                $client = new \Services_Twilio($AccountSid, $AuthToken);
                $twilio_success = true;
                try {
                    $message = $client->account->messages->create(array(
                        "From" => "+15005550006", // From a valid Twilio number
                        "To" => $users_model->telephone,   // Text this number
                        "Body" => "Your activation code is " . $users_model->telephone_activation_token,
                    ));
                } catch (\Services_Twilio_RestException $e) {
                    $twilio_success = false;
                }
                if ($twilio_success) {
                    $transaction->commit();
                    return $this->redirect('/seller/activation?session_id=' . $users_model->telephone_activation_session);
                } else {
                    $transaction->rollBack();
                    $model->addError('telephone', 'Your telephone number is invalid or something wrong in system, please try again later.');
                }
            } else {
                foreach ($users_model->getErrors() as $key => $value) {
                    if (isset($value[0])) {
                        $model->addError($key, $value[0]);
                    }
                }
            }
        }
        return $this->render('seller-registration', [
            'model' => $model,
        ]);
    }


    public function actionUserRegistration()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new UserRegistrationForm();
        if ($model->load(Yii::$app->request->post()) && $model->Registration()) {
            $users_model = new Users();
            $users_model->role = 'user';
            $users_model->email = Yii::$app->request->post()['UserRegistrationForm']['email'];
            $users_model->username = Yii::$app->request->post()['UserRegistrationForm']['username'];
            $users_model->password = Yii::$app->request->post()['UserRegistrationForm']['password'];
            $users_model->telephone = Yii::$app->request->post()['UserRegistrationForm']['telephone'];
            $users_model->first_name = Yii::$app->request->post()['UserRegistrationForm']['first_name'];
            $users_model->last_name = Yii::$app->request->post()['UserRegistrationForm']['last_name'];

            if ($users_model->save()) {
                # Instantiate the client.
                $client = new Client();
                $mgClient = new Mailgun('key-27c4998ebfaa9df9c7ae7e579ce4d1c0',$client);
                $domain = "sandboxc77e200865644d49ab6a821de29fec62.mailgun.org";

                # Make the call to the client.
                $result = $mgClient->sendMessage($domain, array(
                    'from' => 'aramkettsyan@gmail.com',
                    'to' => 'test@test.com',
                    'subject' => 'Hello',
                    'text' => 'Testing some Mailgun awesomness!'
                ));
                Yii::$app->session->writeSession('activation_email', $users_model->email);
                return $this->redirect('activation-email');
            } else {
                foreach ($users_model->getErrors() as $key => $value) {
                    if (isset($value[0])) {
                        $model->addError($key, $value[0]);
                    }
                }
            }
        }
        return $this->render('user-registration', [
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

    public function actionEmailSent()
    {
        if (Yii::$app->session->hasSessionId('activation_email')) {
            return $this->render('email-sent', ['email' => Yii::$app->session->readSession('activation_email')]);
        }
        throw new NotFoundHttpException();
    }

    public function actionAbout()
    {
        return $this->render('about');
    }
}
