<?

namespace Otus\Service;

use Bitrix\Crm\Multifield\Collection;
use Bitrix\Crm\Multifield\Type\Phone;
use Bitrix\Crm\Multifield\Value;

class ContactService extends BaseCRMService {
	public function __construct() {
		parent::__construct('CRM_CONTACT');
	}
	
	public function add($params) {
		$item = $this->factory->createItem([
			'NAME' => $params['name'],
			'ASSIGNED_BY_ID' => 1
		]);
		
		$phoneCollection = new Collection();
		
		$phoneValue = new Value();
		$phoneValue
		    ->setTypeId(\Bitrix\Crm\Multifield\Type\Phone::ID)
		    ->setValueType('WORK')
		    ->setValue("+7{$params['phone']}");
		    
		// Добавляем телефон в коллекцию
		$phoneCollection->add($phoneValue);
		
		// Устанавливаем коллекцию в элемент
		$item->setFm($phoneCollection);
				
		$operation = $this->factory->getAddOperation($item);
		
		return $operation->launch();
	}
	
	public function getById($id) {
		$item = $this->factory->getItem($id);
		
		return $this->prepareItem($item);
	}
	
	public function getByPhone(string $phone) {
	    $item = $this->factory->getItems([
	        'select' => ['ID', 'NAME', 'PHONE'],
	        'filter' => [
	            '%PHONE' => $phone
	        ],
	        'order' => ['ID' => 'DESC'],
	        'limit' => '1'
	    ])[0];
	    
	    return $this->prepareItem($item);
	}
	
	public function getAll($params) {
		$items = $this->factory->getItems([
			'select' => [
				'ID',
				'PHONE',
				'NAME'
			],
			'offset' => ($params['page'] ?? 0) * 20,
			'limit' => 20
		]);
		
		return array_map(
			fn($item) => $this->prepareItem($item),
			$items,
		);
	}
	
	private function prepareItem($item) {
		if (empty($item)) {
			return null;
		}
		
		$phone = array_shift(
			array_filter(
				$item->getFm()->getAll(),
				fn($item) => $item->getTypeId() == 'PHONE'
			)
		);
		
		return [
			'id' => $item->getId(),
			'name' => $item->getName(),
			'phone' => $phone ? $phone->getValue() : null
		];
	}
}