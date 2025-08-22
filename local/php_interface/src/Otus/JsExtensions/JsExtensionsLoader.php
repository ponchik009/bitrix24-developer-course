<?

namespace Otus\JsExtensions;

class JsExtensionsLoader {
	/**
	 * Метод собирает JS расширения из метода getExtenstionsMap и регистрирует их
 	*/
    public static function registerExtensions()
    {
        global $USER;
        
        if (!$USER->IsAuthorized()) {
        	return;
        }
        
        $resultExtenstions = [];
        $arJsConfig = self::getExtenstionsMap();
        foreach ($arJsConfig as $ext => $arConfig) {
        	$arExt = $arConfig[0];
        	$pattern = $arConfig[1];
        	
        	if (!empty($pattern) && !preg_match($pattern, self::getCurrentPagePath())) {
        		continue;
        	}
        	
            \CJSCore::RegisterExt($ext, $arExt);
            $resultExtenstions[] = $ext;
        }
        \CUtil::InitJSCore($resultExtenstions);
    }

    /**
     * Метод возвращает массив JS расширений с конфигурацией
     * Ключ - название расширения
     * Значение (0 элемент) - массив, передаваемый в метод \CJSCore::RegisterExt
     * Значение (1 элемент) - паттерн url, по которому определяется, подключать ли расширение на страницу. null - подключать на любую страницу
     */
    private static function getExtenstionsMap() {
    	return [
            'otus_daystart' => [
            	[
	                'js' => '/local/js/otus/daystart/main.js',
	                'rel' => ["ajax", "popup"]
	            ],
	            null,
            ],
            'otus_log' => [
            	[
	                'js' => '/local/js/otus/log/main.js',
	                'rel' => []
	            ],
	            null,
            ],
    	];
    }
    
    /**
     * Метод возвращает директорию текущего запроса
     * TODO: вынести в отдельный класс-хелпер, методу тут не место
     */
    private static function getCurrentPagePath() {
    	$request = \Bitrix\Main\Application::getContext()::getInstance()->getRequest();
    	
    	return $request->getRequestedPageDirectory();
    }
}
