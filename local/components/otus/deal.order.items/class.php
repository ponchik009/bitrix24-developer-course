<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Entity;

class DealOrderItemsComponent extends CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    private $orderItems = [

	];
    private $menu = [
    	[
    		'ID' => 1,
    		'PRICE' => 100,
    		'TITLE' => 'Test menu 1'
		],
    	[
    		'ID' => 2,
    		'PRICE' => 120,
    		'TITLE' => 'Test menu 2'
		]
    ];
    private $additives = [
    	[
    		'ID' => 1,
    		'TITLE' => 'Test additive 1',
    		'PRICE' => 20,
		],
    	[
    		'ID' => 2,
    		'TITLE' => 'Test additive 2',
    		'PRICE' => 10,
		],
    ];
    
    // обязательный метод предпроверки данных
    public function configureActions()
    {
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
            'getAdditivesByMenuItem' => [
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
            'updateOrderItem' => [
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
            if (!$this->checkModules()) {
                return;
            }

            if (!$this->arParams['DEAL_ID']) {
                ShowError(Loc::getMessage('DEAL_ORDER_ITEMS_NO_DEAL'));
                return;
            }

            $this->loadData();
            $this->includeComponentTemplate();
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }

    private function checkModules()
    {
        if (!Loader::includeModule('crm')) {
            ShowError('Не подключен модуль CRM');
            return false;
        }

        return true;
    }

    private function loadData()
    {
        // Получаем элементы заказа, связанные со сделкой
        $this->arResult['ITEMS'] = $this->getOrderItems();
        $this->arResult['TOTAL_SUM'] = $this->calculateTotalSum();
        $this->arResult['DEAL_ID'] = $this->arParams['DEAL_ID'];
        $this->arResult['CAN_EDIT'] = $this->arParams['CAN_EDIT'];
    }

    private function getOrderItems()
    {
    	$items = [];
    	
    	foreach ($this->orderItems as $item) {
            $menuItem = $this->getMenuItem($item['UF_MENU_ITEM_ID']);
            $additives = $this->getAdditives($item['UF_ADDITIVES']);
            
            $items[] = [
                'ID' => $item['ID'],
                'MENU_ITEM' => $menuItem,
                'PRICE' => $item['UF_PRICE'] ?: $menuItem['PRICE'],
                'QUANTITY' => $item['UF_QUANTITY'],
                'ADDITIVES' => $additives,
                'ITEM_TOTAL' => $this->calculateItemTotal($item, $menuItem, $additives)
            ];
    	}

        return $items;
    }

    private function getMenuItem($menuItemId)
    {
        if (!$menuItemId) {
            return null;
        }
        
        foreach ($this->menu as $menuItem) {
        	if ($menuItem['ID'] == $menuItemId) {
        		return $menuItem;
        	}
        }
    }

    private function getAdditives($additiveIds)
    {
        if (empty($additiveIds)) {
            return [];
        }

        
        $additives = [];
        
        foreach ($this->additives as $additive) {
        	foreach ($additiveIds as $id) {
        		if ($id == $additive['ID']) {
        			$additives[] = $additive;
        		}
        	}
        }

        return $additives;
    }

    private function calculateItemTotal($item, $menuItem, $additives)
    {
        $price = $item['UF_PRICE'] ?: $menuItem['PRICE'];
        $quantity = $item['UF_QUANTITY'] ?: 1;
        
        $additivesTotal = 0;
        foreach ($additives as $additive) {
            $additivesTotal += $additive['PRICE'];
        }

        return ($price + $additivesTotal) * $quantity;
    }

    private function calculateTotalSum()
    {
        $total = 0;
        foreach ($this->arResult['ITEMS'] as $item) {
            $total += $item['ITEM_TOTAL'];
        }
        return $total;
    }
    
    public function getOrderItemsAction($dealId) {
    	return $this->orderItems;
    }
    
    public function getMenuItemsAction()
    {
        return $this->menu;
    }
    
    public function getAdditivesByMenuItemAction($menuItemId)
    {
        return $this->additives;
    }

    public function addOrderItemAction($menuItemId, $quantity, $additives = [], $customPrice = null, $dealId)
    {
        $fields = [
            'UF_DEAL_ID' => $dealId,
            'UF_MENU_ITEM_ID' => $menuItemId,
            'UF_QUANTITY' => $quantity,
            'UF_ADDITIVES' => $additives,
        ];

        if ($customPrice) {
            $fields['UF_PRICE'] = $customPrice;
        }
        
        $this->orderItems = [...$this->orderItems, $fields];

        return $this->orderItems;
    }

    public function updateOrderItemAction($itemId, $fields)
    {
        $items = [];
        
        foreach ($this->orderItems as $item) {
        	if ($item['ID'] == $itemId) {
        		$items[] = [
        			...$item,
        			...$fields,
    			];
        	} else {
        		$items[] = $item;
        	}
        }
        
        return true;
    }

    public function deleteOrderItemAction($itemId)
    {
        $items = [];
        
        foreach ($this->orderItems as $item) {
        	if ($item['ID'] != $itemId) {
        		$items[] = $item;
        	}
        }
        
        return true;
    }
}