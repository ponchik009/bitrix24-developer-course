<?php

namespace Otus\Rest;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Diag\Debug;

use Bitrix\Rest\RestException;

use Otus\Helper\RestHelper;

class ContactController {
    /**
     * Получение клиентов
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
    	RestHelper::logAction('get contacts', true, $arParams);
    	
    	try {
    		$contactService = new \Otus\Service\ContactService();
    		
			$contacts = $contactService->getAll($arParams);
			
        	RestHelper::logAction('get contacts', true, $contacts);
        	
        	return $contacts;
    	} catch (RestException $ex) {
    		throw $ex;
    	} catch (\Throwable $ex) {
        	// общий лог ошибки
            RestHelper::sendFatalError('get contacts', $ex);
    	}
    }
    

}