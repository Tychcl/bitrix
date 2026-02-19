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

<div class="any-list">
	<?foreach($arResult['IBLOCKS'] as $ib_key => $ib_value):?>
		<article class="any-list iblock">
			<h4><?=$ib_value['NAME']?></h4>
			<div class="any-list iblock elements">
				<?foreach($arResult['ITEMS'][$ib_key] as $el_key => $el_value):?>
					<?
					$this->AddEditAction($el_value['ID'], $el_value['EDIT_LINK'], CIBlock::GetArrayByID($el_value["IBLOCK_ID"], "ELEMENT_EDIT"));
					$this->AddDeleteAction($el_value['ID'], $el_value['DELETE_LINK'], CIBlock::GetArrayByID($el_value["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
					?>
					<section class="any-list iblock element">
						<a href="<?=$el_value['DETAIL_PAGE_URL']?>"><!-- тут должна быть ссылка на детальную страничку-->
							<p class="any-list iblock element name"><?=$el_value['NAME']?></p>
							<p class="any-list iblock element desc"><?=$el_value['PREVIEW_TEXT']?></p>
						</a>
					</section>
				<?endforeach;?>
			</div>
		</article>
	<?endforeach;?>
</div>