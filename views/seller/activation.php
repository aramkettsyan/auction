<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Seller Registration';
?>

<h1><?= Html::encode($this->title) ?></h1>

<p> Your telephone number is <?= $telephone ?> </p>
<a href="/seller/change-telephone-number?session_id=<?= $session_id ?>&telephone=<?= $telephone ?>">Change telephone number</a>

<?php $form = ActiveForm::begin([
    'id' => 'telephone-activation-form'
]); ?>

<?= $form->field($model, 'telephone_activation_token')->textInput() ?>

<div>
    <div>
        <?= Html::submitButton('Activate', ['class' => 'btn btn-primary', 'name' => 'activation-button']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
