<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * SellerRegistrationForm is the model behind the Seller Registration form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class SellerRegistrationForm extends Model
{
    public $username;
    public $email;
    public $telephone;
    public $first_name;
    public $last_name;
    public $password;
    public $repeat_password;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username', 'email', 'telephone', 'first_name', 'last_name', 'password', 'repeat_password'], 'required'],
            [['username', 'email', 'telephone', 'first_name', 'last_name', 'password', 'repeat_password'], 'trim'],
            [['username', 'first_name','password','repeat_password', 'last_name'], 'string', 'max' => 64],
            [['telephone'], 'integer'],
            ['password', 'validatePassword'],
            ['telephone', 'validateTelephone'],
            ['email', 'email'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if ($this->password !== $this->repeat_password) {
            $this->addError($attribute, '');
            $this->addError('repeat_password', 'Passwords are differ');
        }
    }

    public function validateTelephone($attribute, $params)
    {
        if (strlen($this->telephone)!==8) {
            $this->addError($attribute, 'Invalid telephone number. Please check your number and send again.');
        }
    }


    public function Registration()
    {
        if ($this->validate()) {
            return true;
        }
        return false;
    }
}
