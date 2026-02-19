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

$base = $arResult["ITEMS"][0]["LIST_PAGE_URL"];
#echo "<pre>";
#print_r($arResult);
#echo "</pre>";
$this->setFrameMode(true); 
?>

<div class="any-list">
	<article class="any-list iblock">
		<h4>Название инфоблока</h4>
		<div class="any-list iblock elements">
			<section class="any-list iblock element">
				<a href=""><!-- тут должна быть ссылка на детальную страничку-->
					<p class="any-list iblock element name">Название элемента инфоблока</p>
					<p class="any-list iblock element desc">Описание элемента инфоблока</p>
				</a>
			</section>
			<section class="any-list iblock element">
				<a href=""><!-- тут должна быть ссылка на детальную страничку-->
					<p class="any-list iblock element name">Название элемента инфоблока</p>
					<p class="any-list iblock element desc">Описание элемента инфоблока</p>
				</a>
			</section>
		</div>
	</article>
</div>

<div class="barba-wrapper">
    <div class="article-list">
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?><br />
<?endif;?>
<?foreach($arResult["ITEMS"] as $arItem):?>
	<?
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
	?>

	<a class="article-item article-list__item" data-anim="anim-3" href="<?=$arItem["DETAIL_PAGE_URL"]?>">
            <div class="article-item__background">
                <?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"])):?>
                    <img src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" alt=""/>
                <?endif?>
            </div>
            <div class="article-item__wrapper">
                <div class="article-item__title"><?echo $arItem["NAME"]?></div>
                <div class="article-item__content"><?echo $arItem["PREVIEW_TEXT"];?></div>
            </div>
        </a>

<?endforeach;?>
</div>
</div>