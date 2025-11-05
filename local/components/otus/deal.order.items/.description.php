<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
    'NAME' => 'Элементы заказа в сделке',
    'DESCRIPTION' => 'Компонент выводит элементы заказа в сделке',
    'PATH' => array(
        'ID' => 'otus',
        'NAME' => 'Компоненты OTUS',
    )
);