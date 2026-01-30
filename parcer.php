<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}
\Bitrix\Main\Loader::includeModule('iblock');

//ID Инфо блока вакансий
$IBLOCK_ID = 4;
$csvFile = $_SERVER['DOCUMENT_ROOT'] . "/local/parcer/vacancy.csv";
$el = new CIBlockElement;

$arEnums = [];
//Получение всех списков и их значений
$rsEnum = CIBlockPropertyEnum::GetList(
    ["SORT" => "ASC", "VALUE" => "ASC"],
    ['IBLOCK_ID' => $IBLOCK_ID]
);
while ($arEnum = $rsEnum->Fetch()) {
    $key = mb_strtolower(trim($arEnum['VALUE']));
    $arEnums[$arEnum['PROPERTY_CODE']][$key] = $arEnum['ID'];
}
//print_r($arEnums);

//Удаление всех старых элементов
$rsElements = CIBlockElement::GetList([], ['IBLOCK_ID' => $IBLOCK_ID], false, false, ['ID']);
while ($element = $rsElements->GetNext()) {
    CIBlockElement::Delete($element['ID']);
}

function getSimilarValue($arEnums, $key, $value){
	$simPercent = [];
	foreach($arEnums[$key] as $enumKey => $enumVal) {
		$simPercent[similar_text($value, $enumKey)] = $enumVal;
	}
	ksort($simPercent);
	return array_pop($simPercent);
}

$row = 1;
$first = true;
if (($handle = fopen($csvFile, "r")) !== false) {
	while (($data = fgetcsv($handle, 1000, ",")) !== false) {
		//Пропуск оглавления
		if($first){
			$first = false;
			continue;
		}

		$PROP['OFFICE'] = $data[1];
		$PROP['LOCATION'] = $data[2];
		$PROP['REQUIRE'] = $data[4];
		$PROP['DUTY'] = $data[5];
		$PROP['CONDITIONS'] = $data[6];
		$PROP['SALARY_VALUE'] = $data[7];
		$PROP['SALARY_TYPE'] = '';
		$PROP['TYPE'] = $data[8];
		$PROP['ACTIVITY'] = $data[9];
		$PROP['SCHEDULE'] = $data[10];
		$PROP['FIELD'] = $data[11];
		$PROP['EMAIL'] = $data[12];
		$PROP['DATE'] = date('d.m.Y');

		//6 списков: OFFICE LOCATION TYPE ACTIVITY SCHEDULE FIELD
		//Проход по каждому ключу Пропа + указатель на переменную
		foreach ($PROP as $key => &$value) {
			$value = trim(str_replace('\n', '', $value));
			//Если в строке есть маркер, то получаем массив разделяя по нему
			//убираем все лишнее тримом и соединяем пробелом
			if(strpos($value, '•') !== false){
				$exp = explode('•', $value);
				$value = join(" ",array_map(fn($s) => trim($s), $exp));
			}
			switch($key){
				case 'OFFICE': 
					$value = mb_strtolower($value);
					if ($value == 'центральный офис') {
						$value .= 'свеза ' . $data[2];
					}
					elseif(strpos($value, "(усть-ишимский филиал )") !== false){
						$value ="свеза тюмень (усть-ишимский филиал )";
					}
					elseif($value == 'лесозаготовка'){
						$value = "свеза ресурс";
					}
					$value = getSimilarValue($arEnums, $key, $value);
					break;
				case 'LOCATION':
					$value = mb_strtolower($value);
					$value = getSimilarValue($arEnums, $key, $value);
					break;
				case 'SALARY_VALUE':
					$value = mb_strtolower($value);
					if ($value == '-') {
						$PROP['SALARY_VALUE'] = '';
					} elseif ($value == 'по договоренности') {
						$value = '';
						$PROP['SALARY_TYPE'] = $arEnums['SALARY_TYPE']['договорная'];
					} else {
						$ar = explode(' ', $value);
            			if ($ar[0] == 'от' || $ar[0] == 'до') {
            			    $PROP['SALARY_TYPE'] = $arEnums['SALARY_TYPE'][$ar[0]];
            			    array_splice($ar, 0, 1);
            			    $value = implode(' ', $ar);
            			} else {
            			    $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE']['='];
            			}
					}
					break;
				case 'TYPE':
					$value = mb_strtolower($value);
					if($value == 'рабочие'){
						$value = $arEnums[$key]['рабочие'];
					}
					else{
						$value = $arEnums[$key]['продажи'];
					}
					break;
				case 'ACTIVITY':
					$value = mb_strtolower($value);
					$value = getSimilarValue($arEnums, $key, $value);
					break;
				case 'SCHEDULE':
					$value = mb_strtolower($value);
					$value = $arEnums[$key][$value];
					break;
				case 'FIELD':
					$value = mb_strtolower($value);
					$value = getSimilarValue($arEnums, $key, $value);
					break;
			}
		}
		$arLoadProductArray = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $IBLOCK_ID,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => $data[3],
            "ACTIVE" => end($data) ? 'Y' : 'N',
        ];
		if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
            continue;
        } else {
            echo $row." Error: " . $el->LAST_ERROR . '<br>'.print_r($PROP);
        }
		$row++;
	}
	fclose($handle);
}