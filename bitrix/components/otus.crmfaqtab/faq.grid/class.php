<?php

namespace Otus\Components;

use Otus\Crmfaqtab\Orm\FaqTable;

use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\Engine\Contract\Controllerable;

use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\Grid\Options as GridOptions;

Loader::includeModule('otus.crmfaqtab');

class FaqGridComponent extends \CBitrixComponent
{
    const GRID_ID = 'FAQ_GRID';

    public function configureActions(): array
    {
        return [];
    }

    private function getElementActions(): array
    {
        return [];
    }

    private function getHeaders(): array
    {
        return [
            [
                'id' => 'ID',
                'name' => 'ID',
                'sort' => 'ID',
                'default' => true,
            ],
            [
                'id' => 'QUESTION',
                'name' => 'Вопрос',
                'sort' => 'QUESTION',
                'default' => true,
            ],
            [
                'id' => 'ANSWER',
                'name' => 'Ответ',
                'sort' => 'ANSWER',
                'default' => true,
            ],
        ];
    }

    public function executeComponent(): void
    {
        $this->prepareGridData();
        $this->includeComponentTemplate();
    }

    private function prepareGridData(): void
    {
        $this->arResult['HEADERS'] = $this->getHeaders();
        $this->arResult['FILTER_ID'] = self::GRID_ID;

        $gridOptions = new GridOptions($this->arResult['FILTER_ID']);
        $navParams = $gridOptions->getNavParams();

        $nav = new PageNavigation($this->arResult['FILTER_ID']);
        $nav->allowAllRecords(true)
            ->setPageSize($navParams['nPageSize'])
            ->initFromUri();

        $filterOption = new FilterOptions($this->arResult['FILTER_ID']);
        $filterData = $filterOption->getFilter([]);
        $filter = $this->prepareFilter($filterData);


        $sort = $gridOptions->getSorting([
            'sort' => [
                'ID' => 'DESC',
            ],
            'vars' => [
                'by' => 'by',
                'order' => 'order',
            ],
        ]);

        $questionIdsQuery = FaqTable::query()
            ->setSelect(['ID'])
            ->setFilter($filter)
            ->setLimit($nav->getLimit())
            ->setOffset($nav->getOffset())
            ->setOrder($sort['sort'])
        ;

        $countQuery = FaqTable::query()
            ->setSelect(['ID'])
            ->setFilter($filter)
        ;
        $nav->setRecordCount($countQuery->queryCountTotal());

        $faqIds = array_column($questionIdsQuery->exec()->fetchAll(), 'ID');

        if (!empty($faqIds)) {
            $faqs = FaqTable::getList([
                'filter' => ['ID' => $faqIds] + $filter,
                'select' => [
                    'ID',
                    'QUESTION',
                    'ANSWER',
                ],
                'order' => $sort['sort'],
            ]);

            $this->arResult['GRID_LIST'] = $this->prepareGridList($faqs);
        } else {
            $this->arResult['GRID_LIST'] = [];
        }

        $this->arResult['NAV'] = $nav;
        $this->arResult['UI_FILTER'] = $this->getFilterFields();
    }

    private function prepareFilter(array $filterData): array
    {
        $filter = [];

        if (!empty($filterData['FIND'])) {
            $filter['%QUESTION'] = $filterData['FIND'];
        }

        if (!empty($filterData['QUESTION'])) {
            $filter['%QUESTION'] = $filterData['QUESTION'];
        }
        
        if (!empty($filterData['ANSWER'])) {
            $filter['%ANSWER'] = $filterData['ANSWER'];
        }

        return $filter;
    }
    
    private function prepareGridList(Result $faqs): array
    {
        $faqsList = $faqs->fetchAll();

        foreach ($faqsList as $faq) {
            $gridList[] = [
                'data' => [
                    'ID' => $faq['ID'],
                    'QUESTION' => $faq['QUESTION'],
                    'ANSWER' => $faq['ANSWER'],
                ],
                'actions' => $this->getElementActions(),
            ];
        }

        return $gridList;
    }

    private function getFilterFields(): array
    {
        return [
            [
                'id' => 'QUESTION',
                'name' => 'Вопрос',
                'type' => 'string',
                'default' => true,
            ],
            [
                'id' => 'ANSWER',
                'name' => 'Ответ',
                'type' => 'string',
                'default' => true,
            ],
        ];
    }
}