<?

use Bitrix\Main\EventManager;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

$eventManager = EventManager::getInstance();

// Rest методы для работы с заказами
$eventManager->addEventHandlerCompatible('rest', 'OnRestServiceBuildDescription', ['Otus\Event\RestEventsRegister', 'OnRestServiceBuildDescriptionHandler']);

// подключение JS расширений
$eventManager->addEventHandler('main', 'OnProlog', ['Otus\Event\JsExtensionsRegister', 'registerExtensions']);

// TODO: вынести определение функции в отдельный класс
/**
 * Формирование уведомлений
 */
$eventManager->addEventHandlerCompatible('crm', 'OnAfterCrmDealUpdate', function($arFields) {
	// статусы Готовится, Готов, В доставке, Доставлен
	if (
		in_array(
			$arFields['STAGE_ID'], 
			['C1:EXECUTING', 'C1:FINAL_INVOICE', 'C1:UC_S4HDT6', 'C1:WON']
		)
	) {
		function resolveDealStage($stageId) {
			if ($stageId == "C1:EXECUTING") {
				return ["Готовится", "Ваш заказ готовится!"];
			} else if ($stageId == "C1:FINAL_INVOICE") {
				return ["Готов", "Ваш заказ готов!"];
			} else if ($stageId == "C1:UC_S4HDT6") {
				return ["В доставке", "Ваш заказ передан в доставку!"];
			} else if ($stageId == "C1:WON") {
				return ["Доставлен", "Спасибо за заказ! Пожалуйста, оцените качество обслуживания: https://yandex.ru"];
			}
		}
		
		try {
			$notificationsQueueService = new \Otus\Service\NotificationsQueueService();
			
			[$stage, $text] = resolveDealStage($arFields['STAGE_ID']);
			
			$addResult = $notificationsQueueService->add($arFields['ID'], $stage, $text);
			
			if (!$addResult) {
				return false;
			}
			
			return $addResult->isSuccess();
		} catch (\Throwable $ex) {
			AddMessage2Log($ex->getMessage());
		}
	}
	

});

// TODO: вынести определение функции в отдельный класс
// TODO: исправить баг: при изменении сделки на стадии "Готов" возможно лишнее списание продуктов
/**
 * Списывание продуктов
 */
$eventManager->addEventHandlerCompatible('crm', 'OnAfterCrmDealUpdate', function($arFields) {
	// статус "Готов"
	if ($arFields["STAGE_ID"] == "C1:FINAL_INVOICE") {
		$dealItemService = new \Otus\Service\DealItemService();
		$dealItemAdditiveService = new \Otus\Service\DealItemAdditiveService();
		$menuConsumptionService = new \Otus\Service\MenuConsumptionService();
		$productRemainderService = new \Otus\Service\ProductRemainderService();
		$branchService = new \Otus\Service\BranchService();
		
		$removing = [];
		
		$dealItems = $dealItemService->getDealItems($arFields['ID']) ?? [];
		$currentUserBranch = $branchService->getCurrentUserBranch();
		
		foreach ($dealItems as $item) {
			$additives = $dealItemAdditiveService->getAdditivesByItemId($item['id']) ?? [];
			
			foreach ($additives as $additive) {
				$removing[] = [
					'branchId' => $currentUserBranch['id'],
					'productId' => $additive['product']['id'],
					'amount' => $additive['additive']['consumption'] * ($item['quantity'] ?? 1)
				];
			}
			
			$menuItemId = $item['menuItem']['id'];
			$consumptions = $menuConsumptionService->getMenuItemConsumption($menuItemId);
			
			foreach ($consumptions as $consumtion) {
				$removing[] = [
					'branchId' => $currentUserBranch['id'],
					'productId' => $consumtion['product']['id'],
					'amount' => $consumtion['consumption'] * ($item['quantity'] ?? 1)
				];
			}
		}
		
		foreach ($removing as $removingItem) {
			$productRemainderService->reduceProductBalance($removingItem, 0);
		}
	}
	
	return true;
});

// TODO: переписать в нормальный вид, отвязаться от идентификатора типа
// TODO: вынести определение функции в отдельный класс
/**
 * Привязка списания к филиалу
 */
$eventManager->addEventHandlerCompatible('crm', 'OnCrmDynamicItemAdd_1072', function($item) {
	$productRemovingService = new \Otus\Service\ProductRemovingService();
	$productRemainderService = new \Otus\Service\ProductRemainderService();
	$branchService = new \Otus\Service\BranchService();
	
	$userBranch = $branchService->getCurrentUserBranch();
	
	$bindingResult = $productRemovingService->bindRemovingToBranch($item, $userBranch['id']);
	
	if (!$bindingResult) {
		throw new \Exception("Не удалось привязать филиал {$userBranch['id']} к элементу списания");
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

$eventManager->addEventHandlerCompatible('crm', 'OnCrmDynamicItemAdd_1052', function($item) {
	return onDealItemsChange($item);
});

$eventManager->addEventHandlerCompatible('crm', 'OnCrmDynamicItemDelete_1052', function($item) {
	return onDealItemsChange($item);
});

// TODO: вынести определение функции в отдельный класс
function onDealItemsChange($item) {
	$dealService = new \Otus\Service\DealService();
	$dealItemService = new \Otus\Service\DealItemService();
	$dealId = $item->get($dealItemService->fields['SP_DEAL_ITEMS_DEAL']['NAME']);

	$dealItems = $dealItemService->getDealItems($dealId);
	
	$totalSum = $dealItemService->calculateTotalSum($dealItems);
	
	$deal = $dealService->factory->getItem($dealId);
	$deal->set(
		$dealService->fields['DEAL_TOTAL_PRICE']['NAME'],
		"$totalSum|RUB"
	);
	$dealUpdateOperation = $dealService->factory->getUpdateOperation($deal);
	$dealUpdateResult = $dealUpdateOperation->launch();
	
	return $dealUpdateResult->isSuccess();
}

// TODO: вынести определение функции в отдельный класс
$eventManager->addEventHandler('crm', 'onEntityDetailsTabsInitialized', function(Event $event) {
    $entityId = $event->getParameter('entityID');
    $entityTypeID = $event->getParameter('entityTypeID');
    $tabs = $event->getParameter('tabs');
    
    $reflection = new \ReflectionClass($event);
    $property = $reflection->getProperty('parameters');
    $property->setAccessible(true);
  
    $eventParameters = $property->getValue($event);

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
	
    $eventParameters['tabs'] = $tabs;
    $property->setValue($event, $eventParameters);

    return new EventResult(EventResult::SUCCESS, [
        'tabs' => $tabs,
    ]);
});

// TODO: вынести определение функции в отдельный класс
$eventManager->addEventHandler('crm', 'onEntityDetailsTabsInitialized', function(Event $event) {
    $entityId = $event->getParameter('entityID');
    $entityTypeID = $event->getParameter('entityTypeID');
    $tabs = $event->getParameter('tabs');
    
    $reflection = new \ReflectionClass($event);
    $property = $reflection->getProperty('parameters');
    $property->setAccessible(true);
  
    $eventParameters = $property->getValue($event);
    
	if ($entityTypeID == \CCrmOwnerType::Contact) {
        $tabs[] = [
            'id' => 'contact_orders',
            'name' => 'Заказы',
            'enabled' => !empty($entityId),
            'loader' => [
                'serviceUrl' => '/local/components/otus/contact.orders.list/lazyload.ajax.php?&site=' . \SITE_ID . '&' . \bitrix_sessid_get(),
                'componentData' => [
                    'template' => '',
                    'params' => [
                        // Параметры вызываемого компонента ($arParams)
                        'CONTACT_ID' => $entityId,
                    ]
                ]
            ]
        ];
	}
	
    $eventParameters['tabs'] = $tabs;
    $property->setValue($event, $eventParameters);

    return new EventResult(EventResult::SUCCESS, [
        'tabs' => $tabs,
    ]);
});
