<?php

namespace Otus\Components;

use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Result;

use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\Grid\Options as GridOptions;

use \Otus\Service\DealService;

class ContactOrdersComponent extends \CBitrixComponent
{
    const GRID_ID = 'CONTACT_ORDERS';
    
    protected $dealService;

    public function configureActions(): array
    {
        return [];
    }

    private function getColumns(): array
    {
        return [
            [
                'id' => 'ID',
                'name' => 'ID',
                'sort' => 'ID',
                'default' => true,
            ],
            [
                'id' => 'NAME',
                'name' => 'Имя',
                'sort' => 'NAME',
                'default' => true,
                'type' => 'custom',
	            'render' => function($value, $item) {
	                return '<a href="/crm/deal/details/' . $item['ID'] . '/" class="ui-link">' . htmlspecialcharsbx($value) . '</a>';
	            }
            ],
            [
                'id' => 'DATE',
                'name' => 'Дата',
                'sort' => 'DATE',
                'default' => true,
            ],
            [
                'id' => 'TOTAL_PRICE',
                'name' => 'Стоимость',
                'sort' => 'TOTAL_PRICE',
                'default' => true,
            ],
        ];
    }

    public function executeComponent(): void
    {
    	if (!$this->arParams['CONTACT_ID']) {
    		ShowError('Не указан ID контакта');
    		return;
    	}
    	
    	$this->initialize();
    	
        $this->prepareGridData();
        
        $this->includeComponentTemplate();
    }
    
    private function initialize() {
    	$this->dealService = new DealService();
    }

    private function prepareGridData(): void
    {
        $this->arResult['COLUMNS'] = $this->getColumns();
        $this->arResult['GRID_ID'] = self::GRID_ID;

        $this->arResult['GRID_LIST'] = $this->prepareGridList();
    }
    
    private function prepareGridList(): array
    {
        $deals = $this->dealService->getDealsByContactId($this->arParams['CONTACT_ID']);

        foreach ($deals as $deal) {
            $gridList[] = [
                'data' => [
                    'ID' => $deal['id'],
                    'NAME' => '<a href="/crm/deal/details/' . $deal['id'] . '/">' . $deal['name'] . '</a>',
                    'DATE' => $deal['date'],
                    'TOTAL_PRICE' => $deal['total_price'],
                ]
            ];
        }

        return $gridList;
    }
}