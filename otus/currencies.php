<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Курсы валют");

?><?$APPLICATION->IncludeComponent(
	"otus:currency.viewer", 
	".default", 
	array(
		"CURRENCY" => array(
			0 => "USD",
			1 => "EUR",
		),
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);?><?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");

?>