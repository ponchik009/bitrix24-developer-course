<?

namespace Otus\Service;

class DealItemService extends BaseCRMService {
	public function __construct() {
		parent::__construct('SP_DEAL_ITEMS');
	}
	
	public function addItemToDeal($dealId, $fields) {
		$item = $this->factory->createItem([
			$this->fields['SP_DEAL_ITEMS_DEAL']['NAME'] => $dealId,
			$this->fields['SP_DEAL_ITEMS_MENU_ITEM']['NAME'] => $fields['id'],
			$this->fields['SP_DEAL_ITEMS_COUNT']['NAME'] => $fields['count'],
			$this->fields['SP_DEAL_ITEMS_PRICE']['NAME'] => $fields['price'],
		]);
		
		$operation = $this->factory->getAddOperation($item);
		return $operation->launch();
	}
}