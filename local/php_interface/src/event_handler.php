<?

use Bitrix\Main;

$eventManager = Main\EventManager::getInstance();

// Rest методы для работы с заказами
$eventManager->addEventHandlerCompatible('rest', 'OnRestServiceBuildDescription', ['Otus\Event\RestEventsRegister', 'OnRestServiceBuildDescriptionHandler']);