<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle('Курсы валют');

?>

<?
$APPLICATION->includeComponent(
	"otus:currency.viewer",
	"",
	[
		"CURRENCY" => ["EUR", 'USD']
	]
);
?>

<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");

?>

