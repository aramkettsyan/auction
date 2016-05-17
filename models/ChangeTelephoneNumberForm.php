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
class changeTelephoneNumberForm extends Model
{
    public $telephone;
    public $password;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['telephone'], 'required'],
        ];
    }

}
