<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>

<div class="card">
	<?if($arParams["DISPLAY_NAME"]!="N" && $arResult["NAME"]):?>
		<h1 class="title"><?=$arResult["NAME"]?></h1>
	<?endif;?>
	<?if($arParams["DISPLAY_DATE"]!="N" && $arResult["DISPLAY_ACTIVE_FROM"]):?>
		<p class="date"><?=$arResult["DISPLAY_ACTIVE_FROM"]?></p>
	<?endif;?>
    <div class="content">
		<?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arResult["DETAIL_PICTURE"])):?>
			<div class="image">
            	<img src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" alt="<?=$arResult["DETAIL_PICTURE"]["ALT"]?>" data-object-fit="cover"/>
        	</div>
		<?endif?>
		<?if($arResult["DETAIL_TEXT"] <> ''):?>
        <div class="text">
			<?echo $arResult["DETAIL_TEXT"];?>
            <!--<p>За первое полугодие 2019 года «ЭР-Телеком Холдинг» показал
                двузначное увеличение выручки – на 11%, по сравнению с аналогичным периодом прошлого года, что составило
                21 438 млн.руб. Прирост произошел в двух бизнес–сегментах – в B2B увеличение на 15%, а в B2C – на
                9%.</p>
                <p>Достигнутые результаты наглядно показывают, что развитие компании соответствует заявленной ранее
                    стратегии.Более подробную информацию можно найти в разделе «Инвесторам».</p>-->
            </div>
		<?endif?>
    </div>
    <!--<a class="back_button" href="#">Назад к новостям</a>-->
</div>