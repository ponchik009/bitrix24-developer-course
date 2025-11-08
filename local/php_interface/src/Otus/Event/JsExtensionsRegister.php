<?

namespace Otus\Event;

use Otus\Helper\PageHelper;

class JsExtensionsRegister {
	/**
	 * Метод собирает JS расширения из метода getExtenstionsMap и регистрирует их
 	*/
    public static function registerExtensions()
    {
        global $USER;
        
        if (!$USER->IsAuthorized()) {
        	return;
        }
        
        $userGroups = $USER->GetUserGroupArray();
        
        $resultExtenstions = [];
        $arJsConfig = self::getExtenstionsMap();
        foreach ($arJsConfig as $ext => $arConfig) {
        	$arExt = $arConfig[0];
        	$pattern = $arConfig[1];
        	$groups = $arConfig[2];
        	
        	// проверка паттерна страницы
        	if (!empty($pattern) && !preg_match($pattern, PageHelper::getCurrentPagePath())) {
        		continue;
        	}
        	
        	// проверка ролей
        	$hasNeededGroup = false;
        	foreach ($groups as $group) {
        		if (in_array($group, $userGroups)) {
        			$hasNeededGroup = true;
        		}
        	}
        	if (!$hasNeededGroup) {
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
     * Значение (2 элемент) - ID ролей, наличие одной из которых у пользователя активирует расширение
     */
    private static function getExtenstionsMap() {
    	return [
            'otus_log' => [
            	[
	                'js' => '/local/js/otus/log/main.js',
	                'rel' => []
	            ],
	            null,
	            []
            ],
            'otus_remainders_calc' => [
            	[
	                'js' => '/local/js/otus/remainders_calc/main.js',
	                'rel' => []
	            ],
	            null,
	            [1, 20]
            ],
    	];
    }
}