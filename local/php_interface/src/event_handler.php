<?

use Bitrix\Main;

$eventManager = Main\EventManager::getInstance();

$eventManager->addEventHandler('iblock', 'OnIBlockPropertyBuildList', ['Otus\UserType\CUserTypeProceduresBooking', 'GetUserTypeDescription']);