<?php

namespace Otus\Rest;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Diag\Debug;

use Bitrix\Rest\RestException;

use Otus\Helper\RestHelper;

class DealController {
    /**
     * Добавление заказа
     * 
     * @param $arParams - request params
     * @param $navStart - default start parameter (start from POST-data)
     * @param \CRestServer $server - server data
     * @return mixed
     * @throws RestException
     */
    public static function add ($arParams, $navStart, \CRestServer $server)
    {
    	// лог входящих данных
    	RestHelper::logAction('order add request receiving', true, $arParams);
    	
    	try {
	        
	        RestHelper::processResult($result);
    	} catch (RestException $ex) {
    		throw $ex;
    	} catch (\Throwable $ex) {
        	// общий лог ошибки
            RestHelper::sendFatalError('patient add', $ex);
    	}
    }
    

}