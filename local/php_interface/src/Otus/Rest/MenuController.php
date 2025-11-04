<?php

namespace Otus\Rest;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Diag\Debug;

use Bitrix\Rest\RestException;

use Otus\Helper\RestHelper;

class MenuController {
    /**
     * Получение меню
     * 
     * @param $arParams - request params
     * @param $navStart - default start parameter (start from POST-data)
     * @param \CRestServer $server - server data
     * @return mixed
     * @throws RestException
     */
    public static function getAll ($arParams, $navStart, \CRestServer $server)
    {
    	// лог входящих данных
    	RestHelper::logAction('get menu', true, $arParams);
    	
    	try {
    		$menuService = new \Otus\Service\MenuService();
    		
			$menu = $menuService->getAll();
			
        	RestHelper::logAction('get menu', true, $menu);
        	
        	return $menu;
    	} catch (RestException $ex) {
    		throw $ex;
    	} catch (\Throwable $ex) {
        	// общий лог ошибки
            RestHelper::sendFatalError('get menu', $ex);
    	}
    }
    
    /**
     * Получение доступного в филиале меню
     * 
     * @param $arParams - request params
     * @param $navStart - default start parameter (start from POST-data)
     * @param \CRestServer $server - server data
     * @return mixed
     * @throws RestException
     */
    public static function getAvailabelMenu ($arParams, $navStart, \CRestServer $server)
    {
    	// лог входящих данных
    	RestHelper::logAction('get available menu', true, $arParams);
    	
    	try {
    		if (empty($arParams['branchId'])) {
	            RestHelper::sendBadRequestError('get available menu', 'Поле branchId не заполнено');
    		}
    		
    		$availabelMenuService = new \Otus\Service\MenuAvailabilityService();
    		
			$menu = $availabelMenuService->getAvailabelItemsByBranch($arParams['branchId']);
			
	        RestHelper::logAction('get available menu', true, $menu);
	        
	        return $menu;
    	} catch (RestException $ex) {
    		throw $ex;
    	} catch (\Throwable $ex) {
        	// общий лог ошибки
            RestHelper::sendFatalError('get available menu', $ex);
    	}
    }
}