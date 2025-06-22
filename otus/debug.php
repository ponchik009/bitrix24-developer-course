<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Diag\Debug;

$nowDate = date("Y-m-d H:i:s");

Debug::writeToFile($nowDate);

throw new Exception("Тестовая ошибка");

?>