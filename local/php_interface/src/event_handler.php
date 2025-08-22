<?

use Bitrix\Main;

$eventManager = Main\EventManager::getInstance();

// подключение кастомных типов
$eventManager->addEventHandler('iblock', 'OnIBlockPropertyBuildList', ['Otus\UserType\CUserTypeProceduresBooking', 'GetUserTypeDescription']);

// подключение JS расширений
$eventManager->addEventHandler('main', 'OnProlog', ['Otus\JsExtensions\JsExtensionsLoader', 'registerExtensions']);