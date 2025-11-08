<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
    'NAME' => 'Сведение остатков по филиалу',
    'DESCRIPTION' => 'Компонент выводит остатки продуктов по филиалу и позволяет изменять его',
    'PATH' => array(
        'ID' => 'otus',
        'NAME' => 'Компоненты OTUS',
    )
);