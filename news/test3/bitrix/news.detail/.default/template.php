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
		<p class="date"><?=$arResult["DISPLAY_ACTIVE_FROM"]?></p>
    <div class="content">
		<?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arResult["DETAIL_PICTURE"])):?>
			<div class="image">
            	<img src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" alt="<?=$arResult["DETAIL_PICTURE"]["ALT"]?>" data-object-fit="cover"/>
        	</div>
		<?endif?>
		<?if($arResult["DETAIL_TEXT"] <> ''):?>
        <div class="text">
			<?echo $arResult["DETAIL_TEXT"];?>
            </div>
		<?endif?>
    </div>
    <a class="back_button" href="<?=$arResult["IBLOCK"]["LIST_PAGE_URL"]?>">Назад к новостям</a>
</div>