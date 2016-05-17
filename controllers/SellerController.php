<?php

namespace app\controllers;

use app\models\TelephoneActivationForm;
use app\models\ChangeTelephoneNumberForm;
use app\models\Users;
use Yii;
use yii\db\Connection;
use app\models\SellerRegistrationForm;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class SellerController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['activation'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
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

    public function actionActivation()
    {
        $request = Yii::$app->request->get();
        if (isset($request['session_id']) && is_string($request['session_id']) && $request['session_id']) {
            $user = Users::findOne(['telephone_activation_session' => $request['session_id']]);
            if (!$user) {
                throw new NotFoundHttpException();
            }
        }
        $model = new TelephoneActivationForm();

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->Post();
            if ((int)$post['TelephoneActivationForm']['telephone_activation_token'] === $user->telephone_activation_token) {
                $user->active = 1;
                $user->telephone_activation_token = NULL;
                $user->telephone_activation_session = NULL;
                $user->save();
                $this->redirect('/site/login');
            } else {
                $model->addError('telephone_activation_token', 'Invalid activation token.');
            }
        }

        return $this->render('activation', [
            'model' => $model,
            'telephone' => str_replace('+374', '0', $user->telephone),
            'session_id' => $user->telephone_activation_session
        ]);
    }

    public function actionChangeTelephoneNumber()
    {
        $request = Yii::$app->request->get();

        $model = new ChangeTelephoneNumberForm();

        if (isset($request['session_id']) && is_string($request['session_id']) && $request['session_id'] && isset($request['telephone'])) {
            $user = Users::findOne(['telephone_activation_session' => $request['session_id'], 'telephone' => '+374' . (int)$request['telephone']]);
            if (!$user) {
                throw new NotFoundHttpException();
            }
        }

        $post = Yii::$app->request->Post();
        if ($model->load($post)) {
            if (isset($post['changeTelephoneNumberForm']) && isset($post['changeTelephoneNumberForm']['password']) && $post['changeTelephoneNumberForm']['password'] && Yii::$app->getSecurity()->validatePassword($post['changeTelephoneNumberForm']['password'], $user->password)) {
                if (isset($post['changeTelephoneNumberForm']['telephone']) && strlen($post['changeTelephoneNumberForm']['telephone']) == 8) {
                    $connection = Yii::$app->getDb();
                    $transaction = $connection->beginTransaction();
                    $country_code = '+374';
                    $old_telephone = $user->telephone;
                    $user->telephone = $country_code.$post['changeTelephoneNumberForm']['telephone'];

                    $user->telephone_activation_token = rand(100000, 999999);

                    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $charactersLength = strlen($characters);
                    $randomString = '';
                    for ($i = 0; $i < 255; $i++) {
                        $randomString .= $characters[rand(0, $charactersLength - 1)];
                    }

                    $user->telephone_activation_session = $randomString;
                    if ($user->save()) {

                        $AccountSid = "AC2ca4af6056334e3fda4dab1e7a9a2af0"; // Your Account SID from www.twilio.com/console
                        $AuthToken = "919da45bf34778700cdc9d14af278289";   // Your Auth Token from www.twilio.com/console

                        $client = new \Services_Twilio($AccountSid, $AuthToken);
                        $twilio_success = true;
                        try {
                            $message = $client->account->messages->create(array(
                                "From" => "+15005550006", // From a valid Twilio number
                                "To" => $user->telephone,   // Text this number
                                "Body" => "Your activation code is " . $user->telephone_activation_token,
                            ));
                        } catch (\Services_Twilio_RestException $e) {
                            $twilio_success = false;
                        }
                        if ($twilio_success) {
                            $transaction->commit();
                            return $this->redirect('/seller/activation?session_id=' . $user->telephone_activation_session);
                        } else {
                            $transaction->rollBack();
                            $user->telephone = $old_telephone;
                            $model->addError('telephone', 'Your telephone number is invalid or something wrong in system, please try again later.');
                        }
                    }
                } else {
                    $model->addError('telephone', 'Your telephone number is invalid.');
                }
            } else {
                $model->addError('password', 'Incorrect password.');
            }
        }

        return $this->render('change-telephone-number', [
            'model' => $model,
            'telephone' => str_replace('+374', '0', $user->telephone)
        ]);
    }

}
