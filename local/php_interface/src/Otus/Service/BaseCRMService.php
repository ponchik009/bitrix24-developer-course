<?

namespace Otus\Service;

use Otus\Helper\UserFieldHelper;

abstract class BaseCRMService {
	public $factory;
	public $fields;
	public $enums;
	
	/**
	 * Базовый конструктор с получением фабрики по XML_ID и кастомных полей
 	*/
	public function __construct(string $code) {
		$this->factory = \Otus\Container\CRMFactoryContainer::get($code);
		
		$entityTypeId = $this->factory->getEntityTypeId();
		
		$this->fields = UserFieldHelper::getUserFieldsByCode(
			$entityTypeId > 128 ? "CRM_{$this->factory->getType()->getId()}" : $code
		);
		
		$this->enums = [];
		foreach ($this->fields as $xmlId => $field) {
			if ($field['TYPE'] == 'enumeration') {
				$this->enums[$xmlId] = UserFieldHelper::getUserFieldEnums($field['ID']);
			}
		}
	}
	
	public function resolveEnumItemByValue($enumCode, $value) {
		$result = null;
		
		foreach ($this->enums[$enumCode] as $enumItem) {
			if ($enumItem['VALUE'] == $value) {
				$result = $enumItem;
			}
		}
		
		return $result;
	}
}