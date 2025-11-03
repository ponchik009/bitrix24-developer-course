<?

namespace Otus\Helper;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Diag\Debug;

use Bitrix\Rest\RestException;

class RestHelper {
    /**
     * Стандартная обработка результата действия над CRM сущностью
     * 
     * @param $result
     */
	public function processResult($result) {
        if ($result->isSuccess())
        {
            $id = $result->getId();
            
            // лог об успешном добавлении записи
        	self::logAction('order add', true, [
        		'id' => $id,
        	]);

            return $id;
        }
        else
        {
        	self::logAction('order add', false, [
    			'error_messages' => $result->getErrorMessages(),
			]);
        	
            throw new RestException(
                Json::encode($result->getErrorMessages()),
                'ERROR_NOT_CREATED',
                \CRestServer::STATUS_WRONG_REQUEST
            );
        }
	}
	
    /**
     * Отправляет общую ошибку
     * 
     * @param string $code - код запроса (действие, которое совершалось)
     * @param \Throwable $exception - полученная ошибка
     */
    public static function sendFatalError($code, $exception) {
    	self::logAction($code, false, [
        		'error_messages' => [$exception->getMessage()],
    	]);
    	
        throw new RestException(
            $exception->getMessage(),
            'ERROR_FATAL',
            \CRestServer::STATUS_INTERNAL
        );
    }
    
    /**
     * Логирует произвольное действие
     */
    public static function logAction($code, $result, $data) {
        Debug::writeToFile(Json::encode([
        	'action' => $code,
        	'result' => $result,
        	'data' => $data,
        ]));
    }
}
