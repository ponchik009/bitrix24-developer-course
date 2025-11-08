<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
    'NAME' => 'Список заказов клиента',
    'DESCRIPTION' => 'Компонент выводит список заказов клиента',
    'PATH' => array(
        'ID' => 'otus',
        'NAME' => 'Компоненты OTUS',
    )
);