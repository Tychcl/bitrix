<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Application;

class UniversalList extends \CBitrixComponent
{
    //Выполняется перед екзекутом и кэшированием, можно использовать для подготовки параметров и приведения их в норму
    public function onPrepareComponentParams($params)
    {
        $params['IBLOCK_ID'] = (int) $params['IBLOCK_ID'];
        return $params;
    }

    public function executeComponent()
    {
        // Проверка подключения модуля инфоблоков
        if (!Loader::includeModule('iblock') & $this->arParams['IBLOCK_ID'] <= 0)
        {
            ShowError(GetMessage('IBLOCK_MODULE_NOT_INSTALLED'));
            return;
        }

        if ($this->arParams['IBLOCK_ID'] <= 0 & $this->arParams['IBLOCK_TYPE'] == '')
        {
            ShowError(GetMessage('PARAMS_NOT_SELECTED'));
            return;
        }
        elseif ($this->arParams['IBLOCK_ID'] > 0){
            $this->arResult['CHOICE'] = 'ID';
            $this->getItemsByID($this->arParams['IBLOCK_ID']);
        }else{
            $this->arResult['CHOICE'] = 'TYPE';
            $this->getItemsByType($this->arParams['IBLOCK_TYPE']);
        }
        
        $this->includeComponentTemplate();
    }

    protected function getItemsByID($I_ID)
    {
        $arSelect = ['ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL'];
        $elements = \CIBlockElement::GetList(['SORT' => 'ASC'], ['IBLOCK_ID' => $I_ID, 'ACTIVE' => 'Y'], false, false, $arSelect);
        while ($el = $elements->GetNext())
        {
            if ($el['PREVIEW_PICTURE'])
            {
                $el['PREVIEW_PICTURE'] = \CFile::GetFileArray($el['PREVIEW_PICTURE']);
            }
            $this->arResult['ITEMS'][$I_ID][] = $el;
        }
    }

    protected function getItemsByType($type)
    {
        $iblocks = \CIBlock::GetList(['SORT' => 'ASC'], ['TYPE'=>$type, 'ACTIVE'=>'Y']);
        while ($iblock = $iblocks->Fetch())
        {
            $I_ID = $iblock['ID'];
            $this->getItemsByID($I_ID);
        }
    }
}