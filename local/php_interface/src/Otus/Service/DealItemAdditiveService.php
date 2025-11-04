<?

namespace Otus\Service;

class DealItemAdditiveService extends BaseCRMService {
	public function __construct() {
		parent::__construct('SP_DEAL_ITEMS_ADDITIVES');
	}
	
	public function addAdditiveToItem($itemId, $fields) {
		$item = $this->factory->createItem([
			$this->fields['SP_DEAL_ITEMS_ADDITIVES_DEAL_ITEM']['NAME'] => $itemId,
			$this->fields['SP_DEAL_ITEMS_ADDITIVES_ADDITIVE']['NAME'] => $fields['id'],
			$this->fields['SP_DEAL_ITEMS_ADDITIVES_PRICE']['NAME'] => $fields['price'],
		]);
		
		$operation = $this->factory->getAddOperation($item);
		return $operation->launch();
	}
}