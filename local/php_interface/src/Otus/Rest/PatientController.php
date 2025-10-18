<?php

namespace Otus\Rest;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Diag\Debug;

use Bitrix\Rest\RestException;

use Otus\Orm\PatientTable;

class PatientController {
    /**
     * Добавление элемента
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
    	Debug::writeToFile(Json::encode($arParams));
    	
    	try {
	    	$arParams['BIRTH_DATE'] = new \Bitrix\Main\Type\DateTime(
				$arParams['BIRTH_DATE'], "Y-m-d"
			);
	    	
	        $result = PatientTable::add($arParams);
	        
	        if ($result->isSuccess())
	        {
	            $id = $result->getId();
	            
	            // лог об успешном добавлении записи
	            Debug::writeToFile(Json::encode([
	            	'action' => 'patient add',
	            	'result' => true,
	            	'data' => [
	            		'id' => $id,
	            	]
	            ]));
	
	            return $id;
	        }
	        else
	        {
	        	// лог о неудаче добавления записи
	            Debug::writeToFile(Json::encode([
	            	'action' => 'patient add',
	            	'result' => false,
	            	'data' => [
	            		'error_messages' => $result->getErrorMessages(),
	            	]
	            ]));
	        	
	            throw new RestException(
	                Json::encode($result->getErrorMessages()),
	                'ERROR_NOT_CREATED',
	                \CRestServer::STATUS_WRONG_REQUEST
	            );
	        }
    	} catch (RestException $ex) {
    		throw $ex;
    	} catch (\Throwable $ex) {
        	// общий лог ошибки
            self::sendFatalError('patient add', $ex);
    	}
    }
    
    /**
     * Обновление элемента
     * 
     * @param $arParams - request params
     * @param $navStart - default start parameter (start from POST-data)
     * @param \CRestServer $server - server data
     * @return mixed
     * @throws RestException
     */
    public static function update ($arParams, $navStart, \CRestServer $server)
    {
    	// лог входящих данных
    	Debug::writeToFile(Json::encode($arParams));
    	
    	try {
	    	$arParams['BIRTH_DATE'] = new \Bitrix\Main\Type\DateTime(
				$arParams['BIRTH_DATE'], "Y-m-d"
			);
	    	
	        $result = PatientTable::update($arParams['ID'], $arParams);
	        
	        if ($result->isSuccess())
	        {
	            // лог об успешном добавлении записи
	            Debug::writeToFile(Json::encode([
	            	'action' => 'patient update',
	            	'result' => true,
	            	'data' => [
	            		'id' => $arParams['ID'],
	            	]
	            ]));
	
	            return $arParams['ID'];
	        }
	        else
	        {
	        	// лог о неудаче обновления записи
	            Debug::writeToFile(Json::encode([
	            	'action' => 'patient update',
	            	'result' => false,
	            	'data' => [
	            		'error_messages' => $result->getErrorMessages(),
	            	]
	            ]));
	        	
	            throw new RestException(
	                Json::encode($result->getErrorMessages()),
	                'ERROR_NOT_UPDATED',
	                \CRestServer::STATUS_WRONG_REQUEST
	            );
	        }
    	} catch (RestException $ex) {
    		throw $ex;
    	} catch (\Throwable $ex) {
        	// общий лог ошибки
			self::sendFatalError('patient update', $ex);
    	}
    }
    
    /**
     * Удаление элемента
     * 
     * @param $arParams - request params
     * @param $navStart - default start parameter (start from POST-data)
     * @param \CRestServer $server - server data
     * @return mixed
     * @throws RestException
     */
    public static function remove ($arParams, $navStart, \CRestServer $server)
    {
    	// лог входящих данных
    	Debug::writeToFile(Json::encode($arParams));
    	
    	try {
	        $result = PatientTable::delete($arParams['ID']);
	        
	        if ($result->isSuccess())
	        {
	            // лог об успешном добавлении записи
	            Debug::writeToFile(Json::encode([
	            	'action' => 'patient remove',
	            	'result' => true,
	            	'data' => [
	            		'id' => $arParams['ID'],
	            	]
	            ]));
	
	            return $arParams['ID'];
	        }
	        else
	        {
	        	// лог о неудаче удаления записи
	            Debug::writeToFile(Json::encode([
	            	'action' => 'patient remove',
	            	'result' => false,
	            	'data' => [
	            		'error_messages' => $result->getErrorMessages(),
	            	]
	            ]));
	        	
	            throw new RestException(
	                Json::encode($result->getErrorMessages()),
	                'ERROR_NOT_UPDATED',
	                \CRestServer::STATUS_WRONG_REQUEST
	            );
	        }
    	} catch (RestException $ex) {
    		throw $ex;
    	} catch (\Throwable $ex) {
        	// общий лог ошибки
			self::sendFatalError('patient remove', $ex);
    	}
    }
    
    /**
     * Получение списка элементов
     * 
     * @param $arParams - request params
     * @param $navStart - default start parameter (start from POST-data)
     * @param \CRestServer $server - server data
     * @return mixed
     * @throws RestException
     */
    public static function getList ($arParams, $navStart, \CRestServer $server)
    {
    	// лог входящих данных
    	Debug::writeToFile(Json::encode($arParams));
    	
    	try {
	        $result = PatientTable::getList([
	        	'offset' => $navStart ?? 0,
	        	'limit' => $arParams['limit'] ?? 50,
	        	'select' => [
	        		'ID',
	        		'FIRST_NAME',
	        		'LAST_NAME',
	        		'SECOND_NAME',
	        		'BIRTH_DATE',
	        	]
	        ])->fetchAll();
	        
	        return array_map(fn($item) => ([
	        	...$item,
	        	'BIRTH_DATE' => $item['BIRTH_DATE']->toString(),
	        ]), $result);
    	} catch (RestException $ex) {
    		throw $ex;
    	} catch (\Throwable $ex) {
        	// общий лог ошибки
			self::sendFatalError('patient get list', $ex);
    	}
    }
    
    /**
     * Получение элемента по ID
     * 
     * @param $arParams - request params
     * @param $navStart - default start parameter (start from POST-data)
     * @param \CRestServer $server - server data
     * @return mixed
     * @throws RestException
     */
    public static function getById ($arParams, $navStart, \CRestServer $server)
    {
    	// лог входящих данных
    	Debug::writeToFile(Json::encode($arParams));
    	
    	try {
	        $result = PatientTable::getList([
	        	'limit' => 1,
	        	'filter' => [
	        		'=ID' => $arParams['ID'],
	        	],
	        	'select' => [
	        		'ID',
	        		'FIRST_NAME',
	        		'LAST_NAME',
	        		'SECOND_NAME',
	        		'BIRTH_DATE',
	        	]
	        ])->fetch();
	        
	        if (empty($result)) {
    	        throw new RestException(
		            'Элемент не найден',
		            RestException::ERROR_NOT_FOUND,
		            \CRestServer::STATUS_NOT_FOUND
		        );
	        }
	        
	        return ([
	        	...$result,
	        	'BIRTH_DATE' => $result['BIRTH_DATE']->toString(),
	        ]);
    	} catch (RestException $ex) {
    		throw $ex;
    	} catch (\Throwable $ex) {
        	// общий лог ошибки
			self::sendFatalError('patient get by id', $ex);
    	}
    }
    
    /**
     * Send fatal error
     * 
     * @param string $code - код запроса
     * @param \Throwable $exception - полученная ошибка
     */
    private static function sendFatalError($code, $exception) {
        Debug::writeToFile(Json::encode([
        	'action' => $code,
        	'result' => false,
        	'data' => [
        		'error_messages' => [$exception->getMessage()],
        	]
        ]));
    	
        throw new RestException(
            $exception->getMessage(),
            'ERROR_FATAL',
            \CRestServer::STATUS_INTERNAL
        );
    }
}