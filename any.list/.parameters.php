<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

// проверяем доступность
use Bitrix\Main\Loader;
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
        //"GREETING_TEXT" => [
        //    "PARENT" => "BASE", // Группа "Основные параметры"
        //    "NAME" => "Текст приветствия",
        //    "TYPE" => "STRING", // Тип поля - строка
        //    "DEFAULT" => "Hello, World!", // Значение по умолчанию
        //],
    ],
];