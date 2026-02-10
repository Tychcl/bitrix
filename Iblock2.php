<?php
namespace Only\Site\Handlers;

class Iblock
{
    public function addLog(&$arFields)
    {
        $testFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/init_test.txt';

        $LOG = 'LOG';
        $LOG_IBLOCK = \CIBlock::GetList(Array("SORT"=>"ASC"), Array('CODE' => $LOG))->GetNext();
        //$ar = print_r($arFields, true);
        if (!isset($arFields['RESULT']) || !$arFields['RESULT']) {
            return;
        }
        if (!isset($arFields['IBLOCK_ID']) || !$arFields['IBLOCK_ID']) {
            return;
        }

        $IB_ID = $arFields['IBLOCK_ID'];
        $iblock = \CIBlock::GetByID($IB_ID)->Fetch();
        if (!$iblock || $iblock['CODE'] == $LOG) {
            return;
        }

        $EL_ID = $arFields['ID'];
        $element = \CIBlockElement::GetByID($EL_ID)->Fetch();
        if (!$element) {
            return;
        }

        $PROP = [];
        $PROP['NAME'] = $element['ID'];
        $PROP['ACTIVE'] = 'Y';
        $PROP['IBLOCK_ID'] = $LOG_IBLOCK['ID'];
        $PROP['PREVIEW_TEXT'] = $element['NAME'];
        $PROP['PREVIEW_TEXT_TYPE'] = 'text';
        $PROP['DATE_ACTIVE_FROM'] = $element['TIMESTAMP_X'];
        $PROP['CODE'] = "LOG_ELEMENT_".$element['CODE']."_".$element['ID'];
        
        $arSec = [];
        $ar = \CIBlockSection::GetList([], ['IBLOCK_CODE' => $LOG]);
        while ($sec = $ar->Fetch()) {
            $parent = \CIBlockSection::GetByID($sec['IBLOCK_SECTION_ID'])->Fetch();
            $arSec[str_replace('LOG_SECTION_', '', $sec['CODE'])] = [
                'ID' => $sec['ID'],
                'NAME' => $sec['NAME'],
                'PARENT_ID' => $parent['ID'],
                'PARENT_CODE' => $parent['CODE'],
                'PARENT_NAME' => $parent['NAME']
            ];
        }
        $arSeckeys = array_keys($arSec);

        if(!in_array($element['IBLOCK_CODE'], $arSeckeys)){
            $bs = new \CIBlockSection;
            $arFields = Array(
            	"ACTIVE" => 'N',
            	"IBLOCK_SECTION_ID" => false,
            	"IBLOCK_ID" => $LOG_IBLOCK['ID'],
            	"NAME" => $element['IBLOCK_NAME']." ".$element['IBLOCK_CODE'],
                "CODE" => "LOG_SECTION_".$element['IBLOCK_CODE']."_".$element['ID']
            );
            $PROP['IBLOCK_SECTION_ID'] = $bs->Add($arFields);
        }
        else{
            $PROP['IBLOCK_SECTION_ID'] = $arSec[$element['IBLOCK_CODE']]['ID'];
        }

        if($iblock['SECTION_PROPERTY'] == 'Y' && $section = \CIBlockSection::GetByID($element['IBLOCK_SECTION_ID'])->Fetch()){
            $depth = $section['DEPTH_LEVEL'];
            for($i = 0; $i < $depth; $i++){
                $PROP['PREVIEW_TEXT'] = $section['NAME'] ." -> ". $PROP['PREVIEW_TEXT'];
                $parent = \CIBlockSection::GetByID($section['IBLOCK_SECTION_ID'])->Fetch();
                $section = \CIBlockSection::GetByID($section['IBLOCK_SECTION_ID'])->Fetch();
            }
        }
        $PROP['PREVIEW_TEXT'] = $element['IBLOCK_NAME'] ." -> ". $PROP['PREVIEW_TEXT'];

        $res = \CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>$LOG_IBLOCK['ID']), false, Array(), Array("CODE", "ID"));
        $elements = [];
        while($ob = $res->GetNextElement())
        {
            $f = $ob->GetFields();
        	$elements[$f['CODE']] = $f['ID'];
        }
        $el = new \CIBlockElement;
        $PROP["MODIFIED_BY"] = $element['MODIFIED_BY'];
        $new_el_id = in_array($PROP['CODE'], array_keys($elements)) ? $el->Update($elements[$PROP['CODE']], $PROP) : $el->Add($PROP); 

        file_put_contents($testFile, print_r([
            'LOG_DATA' => $LOG_IBLOCK,
            'LOG_SECTIONS' => $arSec,
            'IBLOCK_DATA' => $iblock,
            'ELEMENT_DATA' => $element,
            'PROP' => $PROP,
            'TEST' => $new_el_id ? $el->LAST_ERROR : $el->LAST_ERROR
        ], true));
    }
}