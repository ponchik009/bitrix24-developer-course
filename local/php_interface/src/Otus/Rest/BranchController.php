<?php

namespace Otus\Rest;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Diag\Debug;

use Bitrix\Rest\RestException;

use Otus\Helper\RestHelper;

class BranchController {
    /**
     * Получение филиалов
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
    	RestHelper::logAction('get branches', true, $arParams);
    	
    	try {
    		$branchService = new \Otus\Service\BranchService();
    		
			$branches = $branchService->getAll();
			
        	RestHelper::logAction('get branches', true, $branches);
        	
        	return $branches;
    	} catch (RestException $ex) {
    		throw $ex;
    	} catch (\Throwable $ex) {
        	// общий лог ошибки
            RestHelper::sendFatalError('get branches', $ex);
    	}
    }
    

}