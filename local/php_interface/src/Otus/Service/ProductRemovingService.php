<?

namespace Otus\Service;

class ProductRemovingService extends BaseCRMService {
	public function __construct() {
		parent::__construct('SP_PRODUCTS_REMOVING');
	}
	
	public function bindRemovingToBranch($item, $branchId) {
		$item->set(
			$this->fields['SP_PRODUCTS_REMOVING_BRANCH']['NAME'],
			$branchId ?? 2
		);
		
		$operation = $this->factory->getUpdateOperation($item);
		$operation->disableBizProc();
		$operation->disableAfterSaveActions();
		$result = $operation->launch();
		
		return $result->isSuccess();
	}
}