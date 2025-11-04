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
    	RestHelper::logAction('add order', true, $arParams);
    	
    	try {
  			if (
  				empty($arParams['client']['id']) 
  				&& empty($arParams['client']['phone'])
  			) {
  				RestHelper::sendBadRequestError('add order', 'Информация о клиенте не определена. Укажите phone или id в поле client');
    		}
    		
  			if (
  				empty($arParams['branch']['id']) 
  			) {
  				RestHelper::sendBadRequestError('add order', 'Информация о филиале не определена. Укажите id в поле branch');
    		}
    		
    		if (
    			empty($arParams['receiptDatetime'])
    		) {
    			RestHelper::sendBadRequestError('add order', 'Информация о времени получения не определена. Укажите receiptDatetime');
    		}
    		
    		if (
    			empty($arParams['address']['text'])
    		) {
    			RestHelper::sendBadRequestError('add order', 'Информация об адресе не определена. Укажите text в поле address');
    		}
    		
    		if (
    			empty($arParams['items'])
    		) {
    			RestHelper::sendBadRequestError('add order', 'Информация о составе заказа не определена. Укажите массив items в запросе');
    		}
    		
    		$dealService = new \Otus\Service\DealService();
    		
    		$addResult = $dealService->add($arParams);
    		
	        return RestHelper::processResult($addResult);
    	} catch (RestException $ex) {
    		throw $ex;
    	} catch (\Throwable $ex) {
        	// общий лог ошибки
            RestHelper::sendFatalError('add order', $ex);
    	}
    }
    

}