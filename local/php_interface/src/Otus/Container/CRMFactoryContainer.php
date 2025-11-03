<?

namespace Otus\Container;

use Bitrix\Crm\Service\Container;

class CRMFactoryContainer {
	private static $container;
	private static $map;
	private static $factories;
	
	/**
	 * Фукнция получает фабрику CRM-сущности или СП по XML_ID
 	*/
	public static function get($code) {
		if (empty(self::$container)) {
			self::loadCRMTypes();
			self::loadDynamicTypes();
		}
		
		if (empty(self::$factories[$code]) && !empty(self::$map[$code])) {
			$factories[$code] = self::$container->getFactory(self::$map[$code]);
		}
	
		return $factories[$code];
	}
	
	/**
	 * Функция подгружает все сущности CRM
 	*/
	private static function loadCRMTypes() {
        $typeMap = [
            'CRM_LEAD' => \CCrmOwnerType::Lead,
            'CRM_DEAL' => \CCrmOwnerType::Deal,
            'CRM_CONTACT' => \CCrmOwnerType::Contact,
            'CRM_COMPANY' => \CCrmOwnerType::Company,
            'CRM_QUOTE' => \CCrmOwnerType::Quote,
            'CRM_INVOICE' => \CCrmOwnerType::Invoice,
            'CRM_SMART_INVOICE' => \CCrmOwnerType::SmartInvoice
        ];
        
		foreach ($typeMap as $code => $id) {
			self::$map[$code] = $id;
		}
	}
	
	/**
	 * Функция подгружает все СП
 	*/
	private static function loadDynamicTypes() {
		self::$container = Container::getInstance();
		$dynamicTypesMap = self::$container->getDynamicTypesMap()->load();
		
		foreach ($dynamicTypesMap->getTypes() as $type)
		{
			$xmlId = $type->getCode();
			
			if (!empty($xmlId)) {
				self::$map[$xmlId] = $type->getEntityTypeId();
			}
		}
	}
}