<?

namespace Otus\Service;

class ProductRemainderService extends BaseCRMService {
	protected $branchService;
	
	public function __construct() {
		parent::__construct('SP_PRODUCTS_REMAINDER');
		
		$this->branchService = new BranchService();
	}
	
	/**
	 * Изменяет баланс продукта по ID текущего пользователя
 	*/
	public function reduceProductBalance($fields) {
		$currentAmount = $this->factory->getItems([
			'limit' => 1,
			'select' => [
				'ID',
				$this->fields['SP_PRODUCTS_REMAINDER_AMOUNT']['NAME'],
			],
			'filter' => [
				$this->fields['SP_PRODUCTS_REMAINDER_BRANCH']['NAME'] => $fields['branchId'],
				$this->fields['SP_PRODUCTS_REMAINDER_PRODUCT']['NAME'] => $fields['productId'],
			]
		])[0];
		
		if (
			$currentAmount 
			&& $currentAmount->get($this->fields['SP_PRODUCTS_REMAINDER_AMOUNT']['NAME']) >= $fields['amount']
		) {
			$currentAmount->set(
				$this->fields['SP_PRODUCTS_REMAINDER_AMOUNT']['NAME'],
				$currentAmount->get($this->fields['SP_PRODUCTS_REMAINDER_AMOUNT']['NAME']) - $fields['amount']
			);
			$operation = $this->factory->getUpdateOperation($currentAmount);
			$result = $operation->launch();
			
			return $result->isSuccess();
		}
		
		return false;
	}
}