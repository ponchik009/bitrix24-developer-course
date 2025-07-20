<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Курсы валют");

?><?$APPLICATION->IncludeComponent(
	"otus:currency.viewer",
	"",
	Array(
		"CURRENCY" => array("USD","EUR")
	)
);?><?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");

?>