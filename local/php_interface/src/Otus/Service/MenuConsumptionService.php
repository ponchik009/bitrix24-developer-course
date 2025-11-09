<?

namespace Otus\Service;

class MenuConsumptionService extends BaseCRMService {
	public function __construct() {
		parent::__construct('SP_MENU_PRODUCTS');
	}
	
	/**
	 * Возвращает расход продуктов на элемент меню
 	*/
	public function getMenuItemConsumption($menuItemId) {
		$items = $this->factory->getItems([
			'filter' => [
				$this->fields['SP_MENU_PRODUCTS_MENU_ITEM']['NAME'] => $menuItemId
			],
			'select' => [
				'ID',
				$this->fields['SP_MENU_PRODUCTS_COMSUMPTION']['NAME'],
				$this->fields['SP_MENU_PRODUCTS_PRODUCT']['NAME'],
			]
		]);
		
		return array_map(
			fn($item) => $this->mapItem($item),
			$items
		);
	}
	
	private function mapItem($item) {
		return [
			'id' => $item->getId(),
			'consumption' => $item->get($this->fields['SP_MENU_PRODUCTS_COMSUMPTION']['NAME']),
			'product' => [
				'id' => $item->get($this->fields['SP_MENU_PRODUCTS_PRODUCT']['NAME'])
			]
		];
	}
}