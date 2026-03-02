<?php

class PropertyCProp{
    //Возвращает массив с описанием типа свойства
    public function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'S', //базовый тип данных в БД (S – строка)
            'USER_TYPE' => 'HTMLEDITOR', // уникальный идентификатор типа
            'DESCRIPTION' => 'HTML редактор', //название, видимое в списке

            //методы для различных операций
            'GetPropertyFieldHtml' => array(__CLASS__,  'GetPropertyFieldHtml'), //как отображать поле в форме редактирования элемента
            'ConvertToDB' => array(__CLASS__, 'ConvertToDB'), //преобразование значения перед сохранением в БД
            'ConvertFromDB' => array(__CLASS__,  'ConvertFromDB'), //преобразование при чтении из БД
            //'GetSettingsHTML' => array(__CLASS__, 'GetSettingsHTML'), // HTML настроек свойства
            //'PrepareSettings' => array(__CLASS__, 'PrepareUserSettings'), // подготовка настроек перед сохранением
            //'GetLength' => array(__CLASS__, 'GetLength'), // проверка, заполнено ли свойство
            'GetPublicViewHTML' => array(__CLASS__, 'GetPublicViewHTML') // отображение на публичной части
        );
    }

     public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $value = $value['VALUE'] ?? ''; // текущее значение
        $fieldName = $strHTMLControlName['VALUE']; // имя поля

        // Уникальный ID для контейнера редактора
        $editorId = 'html_editor_' . $arProperty['ID'] . '_' .$fieldName;

        //Функция включает буферизацию вывода. Пока буферизация вывода активна, вывод из скрипта не отправляется, вместо этого вывод сохраняется во внутреннем буфере
        ob_start();
        ?>
        <tr>
            <td align="right" valign="top"><?= htmlspecialcharsbx($arProperty['NAME']) ?>:</td>
            <td>
                <?php
                \CHTMLEditor::ShowEditor([
                    'name' => $fieldName,
                    'id' => $editorId,
                    'value' => $value,
                    'width' => '100%',
                    'height' => 250,
                    'minBodyWidth' => 350,
                    'normalBodyWidth' => 750,
                ]);
                ?>
            </td>
        </tr>
        <?php
        //Получает содержимое активного буфера вывода и выключает его
        return ob_get_clean();
    }

    public static function ConvertToDB($arProperty, $value)
    {
        return $value;
    }

    public static function ConvertFromDB($arProperty, $value)
    {
        return $value;
    }

    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return $value['VALUE'] ?? '';
    }

}