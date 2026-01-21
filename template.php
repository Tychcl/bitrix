<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arResult
 */

if ($arResult["isFormErrors"] == "Y"):?><?=$arResult["FORM_ERRORS_TEXT"];?><?endif;?>
<?= $arResult["FORM_NOTE"] ?? '' ?>
<?if ($arResult["isFormNote"] != "Y")
{
?>
<div class="contact-form">
    <div class="form_head">
<?
if ($arResult["isFormDescription"] == "Y" || $arResult["isFormTitle"] == "Y" || $arResult["isFormImage"] == "Y")
{
if ($arResult["isFormTitle"])
{
?>
	<h1><?=$arResult["FORM_TITLE"]?>
<?
}?>
	<p><?=$arResult["FORM_DESCRIPTION"]?></p>
	</div>
<?
} // endif
?>
	<?=$arResult["FORM_HEADER"]?>
        <div class="contact-form__inputs"></div>
	<?
	foreach (array_slice($arResult["QUESTIONS"],0,count($arResult["QUESTIONS"]) - 1) as $FIELD_SID => $arQuestion)
	{
		if ($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] == 'hidden')
		{
			echo $arQuestion["HTML_CODE"];
		}
		else
		{?>
			<div class="input-group">
                <label><?=$arQuestion["CAPTION"]?></label>
                <?=$arQuestion["HTML_CODE"]?>
            </div>
	<? 	}
	} //end foreach
	?>	</div>
		<div class="contact-form__message">
            <label><?= end($arResult["QUESTIONS"])["CAPTION"] ?></label>
            <textarea class="form-textarea" name="message" required></textarea>
        </div>

		<div class="contact-form__bottom">
            <p class="agreement-text">Нажимая &laquo;Отправить&raquo;, Вы&nbsp;подтверждаете, что
                ознакомлены, полностью согласны и&nbsp;принимаете условия &laquo;Согласия на&nbsp;обработку персональных
                данных&raquo;.
            </p>
            <input class="submit-btn" type="submit" name="web_form_submit" value="<?= $arResult["arForm"]["BUTTON"] ?>"/>
		</div>
	<?=$arResult["FORM_FOOTER"]?>
</div>
<?
}