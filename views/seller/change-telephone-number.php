<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Seller Registration';
?>

<h1><?= Html::encode($this->title) ?></h1>

<p> Your telephone number is <?= $telephone ?> </p>

<?php $form = ActiveForm::begin([
    'id' => 'change-telephone-form'
]); ?>

<?= $form->field($model, 'telephone',[
    'template' => '{label} <input disabled style="width:8px" type="text" value="0"> {input}{error} (Greq hamar@ aranc 0-i)'
])->textInput() ?>
<?= $form->field($model, 'password')->passwordInput() ?>

<div>
    <div>
        <?= Html::submitButton('Change', ['class' => 'btn btn-primary', 'name' => 'change-button']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
