<?

use Bitrix\Main\EventManager;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

$eventManager = EventManager::getInstance();

// Rest методы для работы с заказами
$eventManager->addEventHandlerCompatible('rest', 'OnRestServiceBuildDescription', ['Otus\Event\RestEventsRegister', 'OnRestServiceBuildDescriptionHandler']);

// TODO: переписать в нормальный вид, отвязаться от идентификатора типа
$eventManager->addEventHandlerCompatible('crm', 'OnCrmDynamicItemAdd_1072', function(&$item) {
	$productRemovingService = new \Otus\Service\ProductRemovingService();
	$productRemainderService = new \Otus\Service\ProductRemainderService();
	$branchService = new \Otus\Service\BranchService();
	
	$userBranch = $branchService->getCurrentUserBranch();
	
	$bindingResult = $productRemovingService->bindRemovingToBranch($item, $userBranch['id']);
	
	if (!$bindingResult) {
		throw new \Exception("Не удалось привязать отдел {$userBranch['id']} к элементу списания");
	}
	
	$syncResult = $productRemainderService->reduceProductBalance([
		'productId' => $item->get($productRemovingService->fields['SP_PRODUCTS_REMOVING_PRODUCT']['NAME']),
		'amount' => $item->get($productRemovingService->fields['SP_PRODUCTS_REMOVING_COUNT']['NAME']),
		'branchId' => $userBranch['id']
	]);
	
	if (!$syncResult) {
		throw new \Exception("Не удалось списать продукт {$item->get($productRemovingService->fields['SP_PRODUCTS_REMOVING_PRODUCT']['NAME'])} со склада {$userBranch['id']}");
	}
	
	return true;
});

$eventManager->addEventHandler('crm', 'onEntityDetailsTabsInitialized', function(Event $event) {
    $entityId = $event->getParameter('entityID');
    $entityTypeID = $event->getParameter('entityTypeID');
    $tabs = $event->getParameter('tabs');

	if ($entityTypeID == \CCrmOwnerType::Deal) {
        $tabs[] = [
            'id' => 'deal_order_items',
            'name' => 'Элементы заказа',
            'enabled' => !empty($entityId),
            'loader' => [
                'serviceUrl' => '/local/components/otus/deal.order.items/lazyload.ajax.php?&site=' . \SITE_ID . '&' . \bitrix_sessid_get(),
                'componentData' => [
                    'template' => '',
                    'params' => [
                        // Параметры вызываемого компонента ($arParams)
                        'DEAL_ID' => $entityId,
                        'CAN_EDIT' => true,
                    ]
                ]
            ]
        ];
	}

    return new EventResult(EventResult::SUCCESS, [
        'tabs' => $tabs,
    ]);
});

