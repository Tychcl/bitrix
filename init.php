<?php
if (\Bitrix\Main\Loader::includeModule('dev.site')) {
    $handlers = 'Only\Site\Handlers\Iblock';
    if (class_exists($handlers)) {
        $handler = new $handlers;
        AddEventHandler('iblock', 'OnAfterIBlockElementUpdate', [$handler, 'addLog']);
        AddEventHandler('iblock', 'OnAfterIBlockElementAdd', [$handler, 'addLog']);
    }
    
    $agents = 'Only\Site\Agents\Iblock';
    if (class_exists($agents)) {
        $agentName = $agents . '::clearOldLogs();';
        $rsAgent = \CAgent::GetList([], ['NAME' => $agentName]);
        if (!$rsAgent->Fetch()) {
            \CAgent::AddAgent(
                $agentName,
                'dev.site',
                'Y',
                3600,
                '',
                'Y',
                ''
            );
        }
    }
}
