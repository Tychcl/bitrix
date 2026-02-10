<?php
namespace Only\Site\Agents;

use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;

class Iblock
{
    public static function clearOldLogs()
    {
        if (!Loader::includeModule('iblock')) {
            return __METHOD__ . '();';
        }
        
        $iblockCode = 'LOG';
        $res = \CIBlockElement::GetList(
            ['DATE_CREATE' => 'DESC', 'ID' => 'DESC'],
            [
                'IBLOCK_CODE' => $iblockCode
            ],
            false,
            false,
            ['ID', 'DATE_CREATE']
        );
        
        $counter = 0;
        while ($arElement = $res->Fetch()) {
            $counter++;
            if ($counter > 1) {
                \CIBlockElement::Delete($arElement['ID']);
            }
        }
        
        return __METHOD__ . '();';
    }
}
