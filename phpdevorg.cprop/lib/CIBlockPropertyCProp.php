<?php

use \Bitrix\Main\Localization\Loc;

class CIBlockPropertyCProp
{
    private static $showedCss = false;
    private static $showedJs = false;

    public static function GetUserTypeDescription()
    {
        return array(
            'BASE_TYPE' => 'S',
            'USER_TYPE' => 'C',
            'DESCRIPTION' => Loc::getMessage('IEX_CPROP_DESC'),
            'CLASS_NAME' => 'CIBlockPropertyCProp'
            //'GetPropertyFieldHtml' => array(__CLASS__,  'GetPropertyFieldHtml'),
            //'ConvertToDB' => array(__CLASS__, 'ConvertToDB'),
            //'ConvertFromDB' => array(__CLASS__,  'ConvertFromDB'),
            //'GetEditFormHTML' => array(__CLASS__, 'GetSettingsHTML'),
            //'PrepareSettings' => array(__CLASS__, 'PrepareUserSettings'),
            //'GetLength' => array(__CLASS__, 'GetLength'),
            //'GetPublicViewHTML' => array(__CLASS__, 'GetPublicViewHTML')
        );
    }



    public static function GetIBlockTypeDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'C',
            'DESCRIPTION' => Loc::getMessage('IEX_CPROP_DESC'),
            'GetPropertyFieldHtml' => array(__CLASS__,  'GetPropertyFieldHtml'),
            'ConvertToDB' => array(__CLASS__, 'ConvertToDB'),
            'ConvertFromDB' => array(__CLASS__,  'ConvertFromDB'),
            'GetSettingsHTML' => array(__CLASS__, 'GetSettingsHTML'),
            'PrepareSettings' => array(__CLASS__, 'PrepareUserSettings'),
            'GetLength' => array(__CLASS__, 'GetLength'),
            'GetPublicViewHTML' => array(__CLASS__, 'GetPublicViewHTML')
        );
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $hideText = Loc::getMessage('IEX_CPROP_HIDE_TEXT');
        $clearText = Loc::getMessage('IEX_CPROP_CLEAR_TEXT');

        self::showCss();
        self::showJs();

        if(!empty($arProperty['USER_TYPE_SETTINGS'])){
            $arFields = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);
        }
        else{
            return '<span>'.Loc::getMessage('IEX_CPROP_ERROR_INCORRECT_SETTINGS').'</span>';
        }

        $result = '';

        $result .= '<div class="mf-gray"><a class="cl mf-toggle">'.$hideText.'</a>';
        if($arProperty['MULTIPLE'] === 'Y'){
            $result .= ' | <a class="cl mf-delete">'.$clearText.'</a></div>';
        }
        $result .= '<table class="mf-fields-list active" style="width: fit-content;" align="right">';

        foreach ($arFields as $code => $arItem){
            if($arItem['TYPE'] === 'string'){
                $result .= self::showString($code, $arItem['TITLE'], $value, $strHTMLControlName);
            }
            else if($arItem['TYPE'] === 'file'){
                $result .= self::showFile($code, $arItem['TITLE'], $value, $strHTMLControlName);
            }
            else if($arItem['TYPE'] === 'text'){
                $result .= self::showTextarea($code, $arItem['TITLE'], $value, $strHTMLControlName);
            }
            else if($arItem['TYPE'] === 'date'){
                $result .= self::showDate($code, $arItem['TITLE'], $value, $strHTMLControlName);
            }
            else if($arItem['TYPE'] === 'element'){
                $result .= self::showBindElement($code, $arItem['TITLE'], $value, $strHTMLControlName);
            }
            else if($arItem['TYPE'] === 'html'){
                $result .= self::showHTMLEditor($code, $arItem['TITLE'], $value, $strHTMLControlName);
            }
        }

        $result .= '</table>';

        return $result;
    }

    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        $result = '';

        if(!empty($arProperty['USER_TYPE_SETTINGS'])){
            $arFields = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);
        }

        if(!empty($value['VALUE'])){
            $result .= '<br>';

            $data = json_decode($value['VALUE'], true);
            foreach ($data as $code => $value){
                $title = $arFields[$code]['TITLE'];
                $type = $arFields[$code]['TYPE'];

                if($type === 'string'){
                    $result .=  $title . ': ' . $value . '<br>';
                }
                else if($type === 'text'){
                    $result .=  $title . ': ' . $value . '<br>';
                }
                else if($type === 'date'){
                    $result .=  $title . ': ' . $value . '<br>';
                }
                else if ($type === 'html') {
                    $result .= $title . ': ' . $value . '<br>';
                }
            }
        }

        return $result;
    }
    
    public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        $btnAdd = Loc::getMessage('IEX_CPROP_SETTING_BTN_ADD');
        $settingsTitle =  Loc::getMessage('IEX_CPROP_SETTINGS_TITLE');

        $arPropertyFields = array(
            'USER_TYPE_SETTINGS_TITLE' => $settingsTitle,
            'HIDE' => array('ROW_COUNT', 'COL_COUNT', 'DEFAULT_VALUE', 'SMART_FILTER', 'WITH_DESCRIPTION', 'FILTRABLE', 'MULTIPLE_CNT', 'IS_REQUIRED'),
            'SET' => array(
                'MULTIPLE_CNT' => 1,
                'SMART_FILTER' => 'N',
                'FILTRABLE' => 'N',
                'SEARCHABLE' => 1
            ),
        );

        self::showJsForSetting($strHTMLControlName["NAME"]);
        self::showCssForSetting();

        $result = '<tr><td colspan="2" align="center">
            <table id="many-fields-table" class="many-fields-table internal">        
                <tr valign="top" class="heading mf-setting-title">
                   <td>XML_ID</td>
                   <td>'.Loc::getMessage('IEX_CPROP_SETTING_FIELD_TITLE').'</td>
                   <td>'.Loc::getMessage('IEX_CPROP_SETTING_FIELD_SORT').'</td>
                   <td>'.Loc::getMessage('IEX_CPROP_SETTING_FIELD_TYPE').'</td>
                </tr>';


        $arSetting = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);

        if(!empty($arSetting)){
            foreach ($arSetting as $code => $arItem) {
                $result .= '
                       <tr valign="top">
                           <td><input type="text" class="inp-code" size="20" value="'.$code.'"></td>
                           <td><input type="text" class="inp-title" size="35" name="'.$strHTMLControlName["NAME"].'['.$code.'_TITLE]" value="'.$arItem['TITLE'].'"></td>
                           <td><input type="text" class="inp-sort" size="5" name="'.$strHTMLControlName["NAME"].'['.$code.'_SORT]" value="'.$arItem['SORT'].'"></td>
                           <td>
                                <select class="inp-type" name="'.$strHTMLControlName["NAME"].'['.$code.'_TYPE]">
                                    '.self::getOptionList($arItem['TYPE']).'
                                </select>                        
                           </td>
                       </tr>';
            }
        }

        $result .= '
               <tr valign="top">
                    <td><input type="text" class="inp-code" size="20"></td>
                    <td><input type="text" class="inp-title" size="35"></td>
                    <td><input type="text" class="inp-sort" size="5" value="500"></td>
                    <td>
                        <select class="inp-type"> '.self::getOptionList().'</select>                        
                    </td>
               </tr>
             </table>   
                
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <input type="button" value="'.$btnAdd.'" onclick="addNewRows()">
                    </td>
                </tr>
                </td></tr>';

        return $result;
    }

    public static function PrepareUserSettings($arProperty)
    {
        $result = [];
        if(!empty($arProperty['USER_TYPE_SETTINGS'])){
            foreach ($arProperty['USER_TYPE_SETTINGS'] as $code => $value) {
                $result[$code] = $value;
            }
        }
        return $result;
    }

    public static function GetLength($arProperty, $arValue)
    {
        $arFields = self::prepareSetting(unserialize($arProperty['USER_TYPE_SETTINGS']));

        $result = false;
        foreach($arValue['VALUE'] as $code => $value){
            if($arFields[$code]['TYPE'] === 'file'){
                if(!empty($value['name']) || (!empty($value['OLD']) && empty($value['DEL']))){
                    $result = true;
                    break;
                }
            }
            else{
                if(!empty($value)){
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }

    public static function ConvertToDB($arProperty, $arValue)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/convert_debug.log', print_r(['p'=>$arProperty, 'v'=>$arValue], true));
        $arFields = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);

        foreach($arValue['VALUE'] as $code => $value){
            if($arFields[$code]['TYPE'] === 'html'){
                $arValue['VALUE'][$code] = $value;
            }
            if($arFields[$code]['TYPE'] === 'file'){
                $arValue['VALUE'][$code] = self::prepareFileToDB($value);
            }
        }

        $isEmpty = true;
        foreach ($arValue['VALUE'] as $v){
            if(!empty($v)){
                $isEmpty = false;
                break;
            }
        }

        if($isEmpty === false){
            $arResult['VALUE'] = json_encode($arValue['VALUE']);
        }
        else{
            $arResult = ['VALUE' => '', 'DESCRIPTION' => ''];
        }

        return $arResult;
    }

    public static function ConvertFromDB($arProperty, $arValue)
    {
        $return = array();

        if(!empty($arValue['VALUE'])){
            $arData = json_decode($arValue['VALUE'], true);

            foreach ($arData as $code => $value){
                $return['VALUE'][$code] = $value;
            }

        }
        return $return;
    }

    //Internals

    private static function showString($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? htmlspecialchars($arValue['VALUE'][$code]) : '';
        $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td><input type="text" value="'.$v.'" name="'.$strHTMLControlName['VALUE'].'['.$code.']"/></td>
                </tr>';

        return $result;
    }

    public static function showHTMLEditor($code, $title, $arValue, $strHTMLControlName)
    {
        if (!CModule::IncludeModule('fileman')) {
            return '<tr><td colspan="2">' . Loc::getMessage('IEX_CPROP_ERROR_FILEMAN') . '</td></tr>';
        }

        // Правильные имена полей, ожидаемые инфоблоком
        $correctFieldName = $strHTMLControlName['VALUE'] . '[' . $code . ']';
        $correctFieldNameType = $strHTMLControlName['VALUE'] . '[' . $code . '_TYPE]';

        // Временные уникальные имена (без квадратных скобок)
        $tempFieldName = 'TEMP_VALUE_' . md5($correctFieldName);
        $tempFieldNameType = 'TEMP_TYPE_' . md5($correctFieldNameType);

        $value = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $type = !empty($arValue['VALUE'][$code . '_TYPE']) ? $arValue['VALUE'][$code . '_TYPE'] : 'html';

        ob_start();
        ?>
        <tr>
            <td align="right" style="width: fit-content;" valign="top"><?= htmlspecialcharsbx($title) ?>:</td>
            <td>
                <?php
                //CFileMan::ShowHTMLEditControl(
                //    $tempFieldName,                               
                //    $value,
                //    [   'height' => 600, 'width' => '100%',
                //        "arTaskbars"=>["BXComponentsTaskbar", "BXComponents2Taskbar", "BXPropertiesTaskbar", "BXSnippetsTaskbar"],
                //        ]);

                if (\CModule::IncludeModule("fileman")) {
                    \CFileMan::AddHTMLEditorFrame(
                        $$correctFieldName,
                        $value,
                        $correctFieldNameType,
                        "html",
                        ['height' => 200, 'width' => '100%']
                    );
                } else {
                    echo '<textarea name="'.$fieldName.'" rows="10" cols="50">'.htmlspecialcharsbx($val).'</textarea>';
                }

                CFileMan::AddHTMLEditorFrame(
                    $tempFieldName,
                    $value,
                    $tempFieldNameType,
                    $type,
                    [
                        'height' => 600,
                        'width' => '100%'
                    ]);
                
                //$LHE = new CHTMLEditor; 
                //$LHE->Show([
                //    'name' => $correctFieldName,
                //    'id' => $correctFieldNameType,
                //    'content' => $value,
                //    'width' => '100%',
                //    'height' => 600
                //]);
                ?>
            </td>
        </tr>
        <script>
        BX.ready(function() {
            // Переименовываем скрытое поле для значения
            var valueField = document.querySelector('textarea[name="' + '<?= CUtil::JSEscape($tempFieldName) ?>' + '"]');
            if (valueField) {
                valueField.setAttribute('name', '<?= CUtil::JSEscape($correctFieldName) ?>');
            }
            // Переименовываем скрытое поле для типа
            var typeField = document.querySelector('textarea[name="' + '<?= CUtil::JSEscape($tempFieldNameType) ?>' + '"]');
            if (typeField) {
                typeField.setAttribute('name', '<?= CUtil::JSEscape($correctFieldNameType) ?>');
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    private static function showFile($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        if(!empty($arValue['VALUE'][$code]) && !is_array($arValue['VALUE'][$code])){
            $fileId = $arValue['VALUE'][$code];
        }
        else if(!empty($arValue['VALUE'][$code]['OLD'])){
            $fileId = $arValue['VALUE'][$code]['OLD'];
        }
        else{
            $fileId = '';
        }

        if(!empty($fileId))
        {
            $arPicture = CFile::GetByID($fileId)->Fetch();
            if($arPicture)
            {
                $strImageStorePath = COption::GetOptionString('main', 'upload_dir', 'upload');
                $sImagePath = '/'.$strImageStorePath.'/'.$arPicture['SUBDIR'].'/'.$arPicture['FILE_NAME'];
                $fileType = self::getExtension($sImagePath);

                if(in_array($fileType, ['png', 'jpg', 'jpeg', 'gif'])){
                    $content = '<img src="'.$sImagePath.'">';
                }
                else{
                    $content = '<div class="mf-file-name">'.$arPicture['FILE_NAME'].'</div>';
                }

                $result = '<tr>
                        <td align="right" valign="top">'.$title.': </td>
                        <td>
                            <table class="mf-img-table">
                                <tr>
                                    <td>'.$content.'<br>
                                        <div>
                                            <label><input name="'.$strHTMLControlName['VALUE'].'['.$code.'][DEL]" value="Y" type="checkbox"> '. Loc::getMessage("IEX_CPROP_FILE_DELETE") . '</label>
                                            <input name="'.$strHTMLControlName['VALUE'].'['.$code.'][OLD]" value="'.$fileId.'" type="hidden">
                                        </div>
                                    </td>
                                </tr>
                            </table>                      
                        </td>
                    </tr>';
            }
        }
        else{
            $data = '';

            if($strHTMLControlName["MODE"] === "FORM_FILL" && CModule::IncludeModule('fileman'))
            {
                $inputName = $strHTMLControlName['VALUE'].'['.$code.']';
                $data = CFileInput::Show($inputName, $fileId,
                    array(
                        "PATH" => "Y",
                        "IMAGE" => "Y",
                        "MAX_SIZE" => array(
                            "W" => COption::GetOptionString("iblock", "detail_image_size"),
                            "H" => COption::GetOptionString("iblock", "detail_image_size"),
                        ),
                    ), array(
                        'upload' => true,
                        'medialib' => true,
                        'file_dialog' => true,
                        'cloud' => true,
                        'del' => false,
                        'description' => false,
                    )
                );
            }


            $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td>'.$data.'</td>
                </tr>';
        }

        return $result;
    }

    public static function showTextarea($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $result .= '<tr>
                    <td align="right" valign="top">'.$title.': </td>
                    <td><textarea rows="8" name="'.$strHTMLControlName['VALUE'].'['.$code.']">'.$v.'</textarea></td>
                </tr>';

        return $result;
    }

    public static function showDate($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $result .= '<tr>
                        <td align="right" valign="top">'.$title.': </td>
                        <td>
                            <table>
                                <tr>
                                    <td style="padding: 0;">
                                        <div class="adm-input-wrap adm-input-wrap-calendar">
                                            <input class="adm-input adm-input-calendar" type="text" name="'.$strHTMLControlName['VALUE'].'['.$code.']" size="23" value="'.$v.'">
                                            <span class="adm-calendar-icon"
                                                  onclick="BX.calendar({node: this, field:\''.$strHTMLControlName['VALUE'].'['.$code.']\', form: \'\', bTime: true, bHideTime: false});"></span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';

        return $result;
    }

    public static function showBindElement($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';

        $elUrl = '';
        if(!empty($v)){
            $arElem = \CIBlockElement::GetList([], ['ID' => $v],false, ['nPageSize' => 1], ['ID', 'IBLOCK_ID', 'IBLOCK_TYPE_ID', 'NAME'])->Fetch();
            if(!empty($arElem)){
                $elUrl .= '<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$arElem['IBLOCK_ID'].'&ID='.$arElem['ID'].'&type='.$arElem['IBLOCK_TYPE_ID'].'">'.$arElem['NAME'].'</a>';
            }
        }

        $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td>
                        <input name="'.$strHTMLControlName['VALUE'].'['.$code.']" id="'.$strHTMLControlName['VALUE'].'['.$code.']" value="'.$v.'" size="8" type="text" class="mf-inp-bind-elem">
                        <input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=ru&IBLOCK_ID=0&n='.$strHTMLControlName['VALUE'].'&k='.$code.'\', 900, 700);">&nbsp;
                        <span>'.$elUrl.'</span>
                    </td>
                </tr>';

        return $result;
    }

    private static function showCss()
    {
        if(!self::$showedCss) {
            self::$showedCss = true;
            ?>
            <style>
                .cl {cursor: pointer;}
                .mf-gray {color: #797777;}
                .mf-fields-list {display: none; padding-top: 10px; margin-bottom: 10px!important; margin-left: -300px!important; border-bottom: 1px #e0e8ea solid!important;}
                .mf-fields-list.active {display: block;}
                .mf-fields-list td {padding-bottom: 5px;}
                .mf-fields-list td:first-child {width: 300px; color: #616060;}
                .mf-fields-list td:last-child {padding-left: 5px;}
                .mf-fields-list input[type="text"] {width: 350px!important;}
                .mf-fields-list textarea {min-width: 350px; max-width: 650px; color: #000;}
                .mf-fields-list img {max-height: 150px; margin: 5px 0;}
                .mf-img-table {background-color: #e0e8e9; color: #616060; width: 100%;}
                .mf-fields-list input[type="text"].adm-input-calendar {width: 170px!important;}
                .mf-file-name {word-break: break-word; padding: 5px 5px 0 0; color: #101010;}
                .mf-fields-list input[type="text"].mf-inp-bind-elem {width: unset!important;}
            </style>
            <?
        }
    }

    private static function showJs()
    {
        $showText = Loc::getMessage('IEX_CPROP_SHOW_TEXT');
        $hideText = Loc::getMessage('IEX_CPROP_HIDE_TEXT');

        CJSCore::Init(array("jquery"));
        if(!self::$showedJs) {
            self::$showedJs = true;
            ?>
            <script>
                BX.ready(function() {
                    function initCProp() {
                        jQuery(document).on('click', 'a.mf-toggle', function (e) {
                            e.preventDefault();
                            var table = jQuery(this).closest('tr').find('table.mf-fields-list');
                            jQuery(table).toggleClass('active');
                            if (jQuery(table).hasClass('active')) {
                                jQuery(this).text('<?=CUtil::JSEscape($hideText)?>');
                            } else {
                                jQuery(this).text('<?=CUtil::JSEscape($showText)?>');
                            }
                        });

                        jQuery(document).on('click', 'a.mf-delete', function (e) {
                            e.preventDefault();
                            var textInputs = jQuery(this).closest('tr').find('input[type="text"]');
                            jQuery(textInputs).each(function (i, item) {
                                jQuery(item).val('');
                            });
                            var textarea = jQuery(this).closest('tr').find('textarea');
                            jQuery(textarea).each(function (i, item) {
                                jQuery(item).text('');
                            });
                            var checkBoxInputs = jQuery(this).closest('tr').find('input[type="checkbox"]');
                            jQuery(checkBoxInputs).each(function (i, item) {
                                jQuery(item).attr('checked', 'checked');
                            });
                            jQuery(this).closest('tr').hide('slow');
                        });

                        // Crutch for multiple file property
                        BX.addCustomEvent('onAddNewRowBeforeInner', function(data){
                            var html_string = data.html;
                            if (jQuery('<div>' + html_string + '</div>').find('table.mf-fields-list').length > 0) {
                                var blocks = jQuery(html_string).find('.adm-input-file-control.adm-input-file-top-shift');
                                if (blocks.length > 0) {
                                    document.cprop_endPos = 0;
                                    jQuery(blocks).each(function (i, item) {
                                        var blockId = jQuery(item).attr('id');
                                        if (blockId) {
                                            setTimeout(function (i, blockId, html_string) {
                                                var inputs = jQuery('#' + blockId + ' .adm-input-file-new');
                                                if (inputs.length > 0) {
                                                    inputs.each(function () {
                                                        jQuery(this).remove();
                                                    });
                                                }
                                                var start_pos = html_string.indexOf("new top.BX.file_input", document.cprop_endPos);
                                                if (start_pos === -1) {
                                                    start_pos = html_string.indexOf("new topWindow.BX.file_input", document.cprop_endPos);
                                                }
                                                var end_pos = html_string.indexOf(": new BX.file_input", start_pos);
                                                document.cprop_endPos = end_pos;
                                                var jsCode = html_string.substring(start_pos, end_pos);
                                                eval(jsCode);
                                            }, 500, i, blockId, html_string);
                                        }
                                    });
                                    document.cprop_endPos = 0;
                                }
                            }
                        });
                    }

                    if (typeof jQuery !== 'undefined') {
                        initCProp();
                    } else {
                        var interval = setInterval(function() {
                            if (typeof jQuery !== 'undefined') {
                                clearInterval(interval);
                                initCProp();
                            }
                        }, 50);
                    }
                });
            </script>
            <?
        }
    }

    private static function showJsForSetting($inputName)
    {
        CJSCore::Init(array("jquery"));
        $arOptions = [
            'string' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_STRING'),
            'file'   => Loc::getMessage('IEX_CPROP_FIELD_TYPE_FILE'),
            'text'   => Loc::getMessage('IEX_CPROP_FIELD_TYPE_TEXT'),
            'date'   => Loc::getMessage('IEX_CPROP_FIELD_TYPE_DATE'),
            'element'=> Loc::getMessage('IEX_CPROP_FIELD_TYPE_ELEMENT')
        ];
        $optionsJson = CUtil::PhpToJSObject($arOptions);
        ?>
        <script>
            // Глобальная функция для кнопки "Добавить" (чистый JS)
            window.addNewRows = function() {
                var table = document.getElementById('many-fields-table');
                if (!table) return;

                // Создаём строку
                var newRow = table.insertRow(-1); // в конец
                newRow.setAttribute('valign', 'top');

                // Ячейка для кода
                var cellCode = newRow.insertCell();
                var inpCode = document.createElement('input');
                inpCode.type = 'text';
                inpCode.className = 'inp-code';
                inpCode.size = 20;
                cellCode.appendChild(inpCode);

                // Ячейка для заголовка
                var cellTitle = newRow.insertCell();
                var inpTitle = document.createElement('input');
                inpTitle.type = 'text';
                inpTitle.className = 'inp-title';
                inpTitle.size = 35;
                cellTitle.appendChild(inpTitle);

                // Ячейка для сортировки
                var cellSort = newRow.insertCell();
                var inpSort = document.createElement('input');
                inpSort.type = 'text';
                inpSort.className = 'inp-sort';
                inpSort.size = 5;
                inpSort.value = '500';
                cellSort.appendChild(inpSort);

                // Ячейка для типа
                var cellType = newRow.insertCell();
                var select = document.createElement('select');
                select.className = 'inp-type';
                var options = <?=$optionsJson?>;
                for (var val in options) {
                    var option = document.createElement('option');
                    option.value = val;
                    option.textContent = options[val];
                    select.appendChild(option);
                }
                cellType.appendChild(select);
            };

            // Функция для установки атрибутов name (чистый JS)
            function updateNames(row) {
                var codeInput = row.querySelector('.inp-code');
                if (!codeInput) return;
                var code = codeInput.value.trim();

                var titleInput = row.querySelector('.inp-title');
                var sortInput = row.querySelector('.inp-sort');
                var typeSelect = row.querySelector('.inp-type');

                if (code === '') {
                    if (titleInput) titleInput.removeAttribute('name');
                    if (sortInput) sortInput.removeAttribute('name');
                    if (typeSelect) typeSelect.removeAttribute('name');
                } else {
                    if (titleInput) titleInput.setAttribute('name', '<?=CUtil::JSEscape($inputName)?>[' + code + '_TITLE]');
                    if (sortInput) sortInput.setAttribute('name', '<?=CUtil::JSEscape($inputName)?>[' + code + '_SORT]');
                    if (typeSelect) typeSelect.setAttribute('name', '<?=CUtil::JSEscape($inputName)?>[' + code + '_TYPE]');
                }
            }

            document.addEventListener('blur', function(e) {
                if (e.target.classList.contains('inp-code')) {
                    var row = e.target.closest('tr');
                    if (row) updateNames(row);
                }
            }, true);

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('inp-sort')) {
                    e.target.value = e.target.value.replace(/[^0-9]/gim, '');
                }
            });

        </script>
        <?
    }

    private static function showCssForSetting()
    {
        if(!self::$showedCss) {
            self::$showedCss = true;
            ?>
            <style>
                .many-fields-table {margin: 0 auto; /*display: inline;*/}
                .mf-setting-title td {text-align: center!important; border-bottom: unset!important;}
                .many-fields-table td {text-align: center;}
                .many-fields-table > input, .many-fields-table > select{width: 90%!important;}
                .inp-sort{text-align: center;}
                .inp-type{min-width: 125px;}
            </style>
            <?
        }
    }

    private static function prepareSetting($arSetting)
    {
        $arResult = [];

        foreach ($arSetting as $key => $value){
            if(strstr($key, '_TITLE') !== false) {
                $code = str_replace('_TITLE', '', $key);
                $arResult[$code]['TITLE'] = $value;
            }
            else if(strstr($key, '_SORT') !== false) {
                $code = str_replace('_SORT', '', $key);
                $arResult[$code]['SORT'] = $value;
            }
            else if(strstr($key, '_TYPE') !== false) {
                $code = str_replace('_TYPE', '', $key);
                $arResult[$code]['TYPE'] = $value;
            }
        }

        if(!function_exists('cmp')){
            function cmp($a, $b)
            {
                if ($a['SORT'] == $b['SORT']) {
                    return 0;
                }
                return ($a['SORT'] < $b['SORT']) ? -1 : 1;
            }
        }

        uasort($arResult, 'cmp');

        return $arResult;
    }

    private static function getOptionList($selected = 'string')
    {
        $result = '';
        $arOption = [
            'string' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_STRING'),
            'file' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_FILE'),
            'text' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_TEXT'),
            'date' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_DATE'),
            'element' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_ELEMENT'),
            'html' => 'html'
        ];

        foreach ($arOption as $code => $name){
            $s = '';
            if($code === $selected){
                $s = 'selected';
            }

            $result .= '<option value="'.$code.'" '.$s.'>'.$name.'</option>';
        }

        return $result;
    }

    private static function prepareFileToDB($arValue)
    {
        $result = false;

        if(!empty($arValue['DEL']) && $arValue['DEL'] === 'Y' && !empty($arValue['OLD'])){
            CFile::Delete($arValue['OLD']);
        }
        else if(!empty($arValue['OLD'])){
            $result = $arValue['OLD'];
        }
        else if(!empty($arValue['name'])){
            $result = CFile::SaveFile($arValue, 'vote');
        }
        else if(!empty($arValue) && is_file($_SERVER['DOCUMENT_ROOT'] . $arValue)){
            $arFile = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'] . $arValue);
            $result = CFile::SaveFile($arFile, 'vote');
        }


        return $result;
    }

    private static function getExtension($filePath)
    {
        return array_pop(explode('.', $filePath));
    }
}