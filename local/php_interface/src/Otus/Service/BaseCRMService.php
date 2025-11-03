<?

namespace Otus\Service;

use Bitrix\Main\UserFieldTable;

abstract class BaseCRMService {
	protected $factory;
	protected $fields;
	
	/**
	 * Базовый конструктор с получением фабрики по XML_ID и кастомных полей
 	*/
	public function __construct(string $code) {
		$this->factory = \Otus\Container\CRMFactoryContainer::get($code);
		
		$entityTypeId = $this->factory->getEntityTypeId();
		
		$this->fields = \Otus\Helper\UserFieldHelper::getUserFieldsByCode(
			$entityTypeId > 128 ? "CRM_$entityTypeId" : $code
		);
	}
}