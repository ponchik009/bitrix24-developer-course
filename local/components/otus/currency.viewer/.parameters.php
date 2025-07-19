<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

if(!CModule::IncludeModule("iblock"))
	return;
	
$currencyList = \Bitrix\Currency\CurrencyManager::getCurrencyList();

$arComponentParameters = array(
	"GROUPS" => array(
		"MAIN"=>array(
			"NAME"=>'Основные параметры',
			"SORT"=>"10"
		)
	),
	"PARAMETERS" => array(
		"CURRENCY" =>  array(
			"PARENT" => "MAIN",
			"NAME"=>'Валюта',
			"TYPE"=>"LIST",
			'MULTUPLE' => 'Y',
			"DEFAULT"=>"20",
			'VALUES' => $currencyList,
		)
	)
);


