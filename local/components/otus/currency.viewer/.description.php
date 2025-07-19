<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => 'Вывод валюты',
	"DESCRIPTION" =>  'Компонент выводит курс выбранной валюты',
	"ICON" => "/images/news_list.gif",
	"SORT" => 10,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "otus",
		"CHILD" => array(
			"ID" => "currency",
			"NAME" => 'Вывод валюты',
			"SORT" => 10,
		),
	),
);

?>