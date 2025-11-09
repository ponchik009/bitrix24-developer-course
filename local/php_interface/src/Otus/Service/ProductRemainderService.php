<?

namespace Otus\Service;

class ProductRemainderService extends BaseCRMService {
	protected $productService;
	
	public function __construct() {
		parent::__construct('SP_PRODUCTS_REMAINDER');
		
		$this->productService = new ProductService();
	}
	
	/**
	 * Уменьшает баланс продукта по ID филиала и продукта
	 * 
	 * @param $minBalance - если задан, то при списании большего количества, чем имеется на складе, баланс продуктов будет опущен до $minBalance
 	*/
	public function reduceProductBalance($fields, $minBalance = null) {
		$currentAmount = $this->getProductAmount($fields['branchId'], $fields['productId']);
		
		if (
			$currentAmount 
		) {
			if ($currentAmount->get($this->fields['SP_PRODUCTS_REMAINDER_AMOUNT']['NAME']) >= $fields['amount']) {
				$currentAmount->set(
					$this->fields['SP_PRODUCTS_REMAINDER_AMOUNT']['NAME'],
					$currentAmount->get($this->fields['SP_PRODUCTS_REMAINDER_AMOUNT']['NAME']) - $fields['amount']
				);
			} else if (is_numeric($minBalance)) {
				$currentAmount->set(
					$this->fields['SP_PRODUCTS_REMAINDER_AMOUNT']['NAME'],
					$minBalance
				);
			}
			
			$operation = $this->factory->getUpdateOperation($currentAmount);
			$result = $operation->launch();
			
			return $result->isSuccess();
		}
		
		return false;
	}
	
	/**
	 * Изменяет баланс продукта по ID филиала и продукта
 	*/
 	public function changeProductBalance($fields) {
		$currentAmount = $this->getProductAmount($fields['branchId'], $fields['productId']);
		
		if (
			$currentAmount
		) {
			$currentAmount->set(
				$this->fields['SP_PRODUCTS_REMAINDER_AMOUNT']['NAME'],
				$fields['amount']
			);
			$operation = $this->factory->getUpdateOperation($currentAmount);
			$result = $operation->launch();
			
			return $result->isSuccess();
		}
		
		return false;
	}
	
	/**
	 * Получает все остатки по филиалу (с информацией о продукте)
 	*/
 	public function getRemainders($branchId) {
 		$items = $this->factory->getItems([
 			'select' => [
 				'ID',
 				$this->fields['SP_PRODUCTS_REMAINDER_AMOUNT']['NAME'],
 				$this->fields['SP_PRODUCTS_REMAINDER_PRODUCT']['NAME'],
 				'PRODUCT',
 			],
 			'filter' => [
 				$this->fields['SP_PRODUCTS_REMAINDER_BRANCH']['NAME'] => $branchId,
 			],
 			'runtime' => [
				'PRODUCT' => [
	        		'data_type' => $this->productService->factory->getDataClass(),
			        'reference' => [
			            "=this.{$this->fields['SP_PRODUCTS_REMAINDER_PRODUCT']['NAME']}" => 'ref.ID'
			        ],
			        'join_type' => 'left'
			    ]
		    ]
 		]);
 		
 		return array_map(
 			fn($item) => $this->mapItem($item),
 			$items
 		);
 	}
	
	/**
	 * Получает текущий баланс продукта по филиалу
 	*/
	public function getProductAmount($branchId, $productId) {
		$amountItem = $this->factory->getItems([
			'limit' => 1,
			'select' => [
				'ID',
				$this->fields['SP_PRODUCTS_REMAINDER_AMOUNT']['NAME'],
			],
			'filter' => [
				$this->fields['SP_PRODUCTS_REMAINDER_BRANCH']['NAME'] => $branchId,
				$this->fields['SP_PRODUCTS_REMAINDER_PRODUCT']['NAME'] => $productId,
			]
		])[0];
		
		return $amountItem;
	}
	
	private function mapItem($item) {
		return [
			'id' => $item->getId(),
			'product' => [
				'name' => $item->get('PRODUCT')->getTitle(),
				'id' => $item->get('PRODUCT')->getId(),
				'measurmentUnit' => $this->productService->enums['SP_PRODUCT_MEASUREMENT_UNIT'][
					$item->get('PRODUCT')->get($this->productService->fields['SP_PRODUCT_MEASUREMENT_UNIT']['NAME'])
				]['VALUE']
			],
			'remainder' => $item->get($this->fields['SP_PRODUCTS_REMAINDER_AMOUNT']['NAME']),
		];
	}
}