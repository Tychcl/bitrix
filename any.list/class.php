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
            $I_ID = $this->arParams['IBLOCK_ID'];
            $iblock = \CIBlock::GetByID($I_ID)->GetNext();
            $this->arResult['IBLOCKS'][$I_ID] = $iblock;
            $this->getItemsByID($I_ID);
        }else{
            $this->getItemsByType($this->arParams['IBLOCK_TYPE']);
        }
        
        $this->includeComponentTemplate();
    }

    protected function getItemsByID($I_ID)
    {
        $filter = ['IBLOCK_ID' => $I_ID, 'ACTIVE' => 'Y'];
        if($this->arParams['IBLOCK_ELEMENT_FIELD_FILTER'] != ''){
            $filter[$this->arParams['IBLOCK_ELEMENT_FIELD_FILTER']] = $this->arParams['FIELD_FILTER'];
        }
        $arSelect = ['ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_PAGE_URL'];
        $elements = \CIBlockElement::GetList(['SORT' => 'ASC'], $filter, false, false, $arSelect);
        while ($el = $elements->GetNext())
        {
            if ($el['PREVIEW_PICTURE'])
            {
                $el['PREVIEW_PICTURE'] = \CFile::GetFileArray($el['PREVIEW_PICTURE']);
            }
            $this->arResult['ITEMS'][$I_ID][$el['ID']] = $el;
        }
    }

    protected function getItemsByType($type)
    {
        $iblocks = \CIBlock::GetList(['SORT' => 'ASC'], ['TYPE'=>$type, 'ACTIVE'=>'Y']);
        while ($iblock = $iblocks->Fetch())
        {
            $I_ID = $iblock['ID'];
            $this->arResult['IBLOCKS'][$I_ID] = $iblock;
            $this->getItemsByID($I_ID);
        }
    }


}