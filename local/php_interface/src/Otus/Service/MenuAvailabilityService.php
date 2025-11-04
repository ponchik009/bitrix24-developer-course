<?

namespace Otus\Service;

use Bitrix\Main\Entity\Query;

class MenuAvailabilityService extends BaseCRMService {
	public function __construct() {
		parent::__construct('SP_MENU_AVAILABILITY');
	}
	
	public function getAvailabelItemsByBranch($branchId) {
		$items = $this->factory->getItems([
			'select' => [
				$this->fields['SP_MENU_AVAILABILITY_AVAILABEL']['NAME'],
				$this->fields['SP_MENU_AVAILABILITY_MENU_ITEM']['NAME'],
			],
			'filter' => [
				$this->fields['SP_MENU_AVAILABILITY_BRANCH']['NAME'] => $branchId,
			]
		]);
		
		return array_map(fn($item) => $this->mapItem($item), $items);
	}
	
	private function mapItem($item) {
		return [
			'available' => $item->get($this->fields['SP_MENU_AVAILABILITY_AVAILABEL']['NAME']),
			'menuItem' => $item->get($this->fields['SP_MENU_AVAILABILITY_MENU_ITEM']['NAME']),
		];
	}
}