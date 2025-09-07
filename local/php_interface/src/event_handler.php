<?

use Bitrix\Main;

$eventManager = Main\EventManager::getInstance();

// подключение кастомных типов
$eventManager->addEventHandler('iblock', 'OnIBlockPropertyBuildList', ['Otus\UserType\CUserTypeProceduresBooking', 'GetUserTypeDescription']);

// подключение JS расширений
$eventManager->addEventHandler('main', 'OnProlog', ['Otus\JsExtensions\JsExtensionsLoader', 'registerExtensions']);

// сихнронизация ИБ заявки с ЦРМ сделки
$eventManager->addEventHandler('crm', 'OnAfterCrmDealUpdate', ['Otus\Event\SynchronizationDealAndRequest', 'synchronizeToRequestOnDealUpdate']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', ['Otus\Event\SynchronizationDealAndRequest', 'synchronizeToDealOnRequestUpdate']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', ['Otus\Event\SynchronizationDealAndRequest', 'synchronizeToDealOnRequestUpdate']);