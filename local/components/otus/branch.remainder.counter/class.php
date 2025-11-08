<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;

use Otus\Service\ProductRemainderService;
use Otus\Service\BranchService;

class BranchRemainderCounterComponent extends CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    private $productRemainderService;
    private $branchService;
    private $branchId;
    
    // обязательный метод предпроверки данных
    public function configureActions()
    {
    	// инициализация зависимостей
    	$this->productRemainderService = new ProductRemainderService();
    	$this->branchService = new BranchService();
    	$this->branchId = $this->branchService->getCurrentUserBranch()['id'];
    	
        return [
            'getRemainders' => [
                'prefilters' => [
                    new Bitrix\Main\Engine\ActionFilter\Authentication(),
                    new Bitrix\Main\Engine\ActionFilter\HttpMethod(array(Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_GET, Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_POST)),
                    new Bitrix\Main\Engine\ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
            'changeRemainders' => [
                'prefilters' => [
                    new Bitrix\Main\Engine\ActionFilter\Authentication(),
                    new Bitrix\Main\Engine\ActionFilter\HttpMethod(array(Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_GET, Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_POST)),
                    new Bitrix\Main\Engine\ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
        ];
    }

    public function executeComponent()
    {
        try {
            $this->includeComponentTemplate();
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }
    
    public function getRemaindersAction() {
    	$remainders = $this->productRemainderService->getRemainders($this->branchId);
    	
    	return $remainders;
    }
    
    /**
     * @param $items - массив объектов с полями ($productId, $amount)
     */
    public function changeRemaindersAction($items = []) {
    	$result = true;
    	
    	foreach ($items as $item) {
    		$updateRes = $this->productRemainderService->changeProductBalance([
    			'branchId' => $this->branchId,
    			...$item
    		]);
    		
    		$result = $result && $updateRes;
    	}
    	
    	return $result;
    }
}