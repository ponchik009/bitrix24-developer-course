<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;

use Bitrix\Crm\DealTable;

use Otus\Service\MenuService;
use Otus\Service\MenuAvailabilityService;
use Otus\Service\BranchService;
use Otus\Service\DealItemService;
use Otus\Service\DealItemAdditiveService;

class DealOrderItemsComponent extends CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    private $menuService;
    private $menuAvailabilityService;
    private $branchService;
    private $dealItemService;
    private $dealItemAdditiveService;
    
    // обязательный метод предпроверки данных
    public function configureActions()
    {
    	// инициализация зависимостей
    	$this->menuService = new MenuService();
    	$this->menuAvailabilityService = new MenuAvailabilityService();
    	$this->branchService = new BranchService();
    	$this->dealItemService = new DealItemService();
    	$this->dealItemAdditiveService = new DealItemAdditiveService();
    	
        return [
            'getOrderItems' => [
                'prefilters' => [
                    new Bitrix\Main\Engine\ActionFilter\Authentication(),
                    new Bitrix\Main\Engine\ActionFilter\HttpMethod(array(Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_GET, Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_POST)),
                    new Bitrix\Main\Engine\ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
            'getMenuItems' => [
                'prefilters' => [
                    new Bitrix\Main\Engine\ActionFilter\Authentication(),
                    new Bitrix\Main\Engine\ActionFilter\HttpMethod(array(Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_GET, Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_POST)),
                    new Bitrix\Main\Engine\ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
            'addOrderItem' => [
                'prefilters' => [
                    new Bitrix\Main\Engine\ActionFilter\Authentication(),
                    new Bitrix\Main\Engine\ActionFilter\HttpMethod(array(Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_GET, Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_POST)),
                    new Bitrix\Main\Engine\ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
            'deleteOrderItem' => [
                'prefilters' => [
                    new Bitrix\Main\Engine\ActionFilter\Authentication(),
                    new Bitrix\Main\Engine\ActionFilter\HttpMethod(array(Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_GET, Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_POST)),
                    new Bitrix\Main\Engine\ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        $arParams['DEAL_ID'] = (int)$arParams['DEAL_ID'];
        $arParams['CAN_EDIT'] = $arParams['CAN_EDIT'] ?? true;
        
        return $arParams;
    }

    public function executeComponent()
    {
        try {
            if (!$this->arParams['DEAL_ID']) {
                ShowError(Loc::getMessage('DEAL_ORDER_ITEMS_NO_DEAL'));
                return;
            }

            $this->includeComponentTemplate();
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }


    
    public function getOrderItemsAction($dealId) {
    	$items = $this->dealItemService->getDealItems($dealId);
    	$totalSum = $this->dealItemService->calculateTotalSum($items);
    	
    	return [
    		'items' => $items,
    		'totalSum' => $totalSum,
    	];
    }
    
    public function getMenuItemsAction()
    {
        $menuItems = $this->menuService->getAll();
        $currentBranchId = $this->branchService->getCurrentUserBranch()['id'];
        $menuAvailable = $this->menuAvailabilityService->getAvailabelItemsByBranch($currentBranchId);
        $menuAvailableMap = [];
        
        foreach ($menuAvailable as $item) {
        	$menuAvailableMap[$item['menuItem']] = $item;
        }
        
        $result = [];
        
        foreach ($menuItems as $menuItem) {
        	if ($menuAvailableMap[$menuItem['id']]['available']) {
        		$result[] = $menuItem;
        	}
        }
        
        return $result;
    }

    public function addOrderItemAction($menuItemId, $quantity, $price, $dealId, $additives = [])
    {
    	$fields = [
    		'id' => $menuItemId,
    		'count' => $quantity,
    		'price' => $price
    	];
        
        $addItemResult = $this->dealItemService->addItemToDeal($dealId, $fields);
        
        if ($addItemResult->isSuccess()) {
        	foreach ($additives as $additive) {
        		$this->dealItemAdditiveService->addAdditiveToItem($addItemResult->getId(), $additive);
        	}
        }

        return $addItemResult->getId();
    }

    public function deleteOrderItemAction($itemId)
    {
    	$additives = $this->dealItemAdditiveService->getAdditivesByItemId($itemId);
    	// удаляем элемент из заказа
        $itemDeleteResult = $this->dealItemService->deleteItemFromDeal($itemId);
        
        // удаляем связанные добавки
        if ($itemDeleteResult && $itemDeleteResult->isSuccess()) {
        	foreach (($additives ?? []) as $additive) {
        		$this->dealItemAdditiveService->removeAdditiveFromItem($additive['id']);
        	}
        }
        
        return $itemDeleteResult->isSuccess();
    }
}