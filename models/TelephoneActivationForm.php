<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * TelephoneActivationForm is the model behind the Telephone Activation form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class TelephoneActivationForm extends Model
{
    public $telephone_activation_session;
    public $telephone_activation_token;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['telephone_activation_token', 'telephone_activation_session'], 'required'],
        ];
    }

}
