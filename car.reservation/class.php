<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Application;

class CarReservation extends \CBitrixComponent
{
    private $Post = 'Post'; //highload блок должностей
    private $Cars = 'Cars'; //highload блок автомобилей
    private $Reservation = 'RESERVATION'; //IBLOCK аренд авто
    
    public function onPrepareComponentParams($params)
    {
        if (!Loader::includeModule("highloadblock"))
        {
            ShowError('highloadblock модуль не загрузился'/*GetMessage('IBLOCK_MODULE_NOT_INSTALLED')*/);
            die();
        }

        global $USER;
        $userId = $USER->GetID();

        if (!$userId)
        {
            ShowError('Юзер айди не получен');
            die();
        }

        $request = Application::getInstance()->getContext()->getRequest();
        $startDateRaw = $request->getQuery('start');
        $endDateRaw = $request->getQuery('end');
        if (!$startDateRaw || !$endDateRaw){
            ShowError('Требуется указать start и end в параметрах');
            die();
        }
        $startDate = new DateTime($startDateRaw);
        $endDate = new DateTime($endDateRaw);
        $startDateFormatted = $startDate->format('Y-m-d H:i:s');
        $startDateFormatted = $endDate->format('Y-m-d H:i:s');

        //Данные о пользователе
        $params['START'] = $startDateFormatted;
        $params['END'] = $startDateFormatted;
        $params['USER']['ID'] = $userId;
        $params['USER']['UF_POST'] = \Bitrix\Main\UserTable::getList([
            'filter' => ['=ID' => $params['USER']['ID']],
            'select' => ['UF_POST']
        ])->fetch()['UF_POST'];
        return $params;
    }

    public function executeComponent()
    {
        
        echo '<pre>';
        //echo print_r($this->arParams, true);

        //получение доступных категорий комфорта для пользователя
        $strEntityDataClass = $this->getDataClass($this->Post);
        $arUserAvailableCategories = $strEntityDataClass::getList(array(
            'select' => array('UF_CATEGORY_COMFORT_ID'),
            'filter' => array('=ID' => $this->arParams['USER']['UF_POST'])
        ))->Fetch()['UF_CATEGORY_COMFORT_ID'];
        //echo print_r($arUserAvailableCategories, true);

        //получение автомобилей с доступными уровнями комфорта для пользователя
        $strEntityDataClass = $this->getDataClass($this->Cars);
        $arSelect = [
            'ID',
            'UF_NAME',
            'UF_BRAND',
            'UF_MODEL',
            'UF_DRIVER',
            'UF_COMFORT'
        ];
        $arFilter = [          
            'UF_COMFORT' => $arUserAvailableCategories
        ];
        $rsData = $strEntityDataClass::getList(['select' => $arSelect, 'filter' => $arFilter]);
        while($car = $rsData->Fetch()){
            $cars[$car['ID']] = $car;
        }
        //echo print_r($cars, true);

        $arFilter = [          
            'IBLOCK_CODE' => $this->Reservation,
            'ACTIVE' => 'Y',
            '<=PROPERTY_START' => $this->arParams['START'],
            '>=PROPERTY_END' => $this->arParams['END']
        ];
        $rsData = \CIBlockElement::GetList([],$arFilter, false, false, ['ID', 'PROPERTY_CAR']);
        while ($car = $rsData->Fetch()) {
            unset($cars[$car['PROPERTY_CAR_VALUE']]);
        }
        echo print_r($cars, true);
        echo '</pre>';
    }

    private function getDataClass($name){
        $HLBlockId = HighloadBlockTable::resolveHighloadblock($name)['ID'];
        $HLBlock = HighloadBlockTable::getById($HLBlockId)->fetch();
        $obEntity = HighloadBlockTable::compileEntity($HLBlock);
        return $obEntity->getDataClass();
    }
}