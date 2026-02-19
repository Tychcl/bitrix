<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

// проверяем доступность
use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
if (!Loader::includeModule('iblock'))
{
	return;
}

//получение инфоблоков для параметров
$arIBlock = [];
$rsIBlock = CIBlock::GetList(["SORT" => "ASC"], ['ACTIVE' => 'Y',]);
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}


$arField = [];
$rsFields = array_keys(ElementTable::getMap());
foreach ($rsFields as $field) {
	$arField[$field] = $field;
}
$arComponentParameters = [
    "PARAMETERS" => [
		"IBLOCK_TYPE" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("BN_P_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => CIBlockParameters::GetIBlockTypes(),
			"REFRESH" => "Y",
			"DEFAULT" => ''
		],
		"IBLOCK_ID" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("BN_P_IBLOCK"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => ''
		],
		"IBLOCK_ELEMENT_FIELD_FILTER" => [
			"PARENT" => "LIST_SETTINGS",
			"NAME" => "Поле элемента",
			"TYPE" => "LIST",
			"VALUES" => $arField,
			"REFRESH" => "Y",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => ''
		],
        "FIELD_FILTER" => [
            "PARENT" => "LIST_SETTINGS",
            "NAME" => "Фильтр",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
    ],
];