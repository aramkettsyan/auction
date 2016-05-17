<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Seller Registration';
?>
<div>
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields:</p>

    <a href="/site/user-registration">User Registration</a>


    <?php $form = ActiveForm::begin([
        'id' => 'registration-form'
    ]); ?>

        <?= $form->field($model, 'username')->textInput() ?>

        <?= $form->field($model, 'email')->textInput() ?>

        <?= $form->field($model, 'first_name')->textInput() ?>

        <?= $form->field($model, 'last_name')->textInput() ?>

        <?= $form->field($model, 'telephone',[
            'template' => '{label} <input disabled style="width:8px" type="text" value="0"> {input}{error} (Greq hamar@ aranc 0-i)'
        ])->textInput() ?>

        <?= $form->field($model, 'password')->passwordInput() ?>

        <?= $form->field($model, 'repeat_password')->passwordInput() ?>

        <div>
            <div>
                <?= Html::submitButton('Registration', ['class' => 'btn btn-primary', 'name' => 'registration-button']) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>

    <script type="text/javascript">
        $('document').ready(function(){

        });
    </script>
</div>
