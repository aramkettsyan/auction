<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * UserRegistrationForm is the model behind the User Registration form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class UserRegistrationForm extends Model
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
        if (strlen($this->telephone)>13) {
            $this->addError($attribute, 'Telephone number example 0037494678798');
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
