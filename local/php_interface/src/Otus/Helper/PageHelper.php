<?

namespace Otus\Helper;

class PageHelper {
    /**
     * Метод возвращает директорию текущего запроса
     */
    public static function getCurrentPagePath() {
    	$request = \Bitrix\Main\Application::getContext()::getInstance()->getRequest();
    	
    	return $request->getRequestedPageDirectory();
    }
}