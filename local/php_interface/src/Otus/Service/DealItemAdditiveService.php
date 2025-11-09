<?

namespace Otus\Service;

class DealItemAdditiveService extends BaseCRMService {
	protected $additiveService;
	protected $productService;
	
	public function __construct() {
		parent::__construct('SP_DEAL_ITEMS_ADDITIVES');
		
		$this->additiveService = new AdditiveService();
		$this->productService = new ProductService();
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
	
	public function getAdditivesByItemId($itemId) {
		$additives = $this->factory->getItems([
			'select' => [
				'ID',
				$this->fields['SP_DEAL_ITEMS_ADDITIVES_PRICE']['NAME'],
				$this->fields['SP_DEAL_ITEMS_ADDITIVES_ADDITIVE']['NAME'],
				'ADDITIVE',
				'PRODUCT',
			],
			'filter' => [
				$this->fields['SP_DEAL_ITEMS_ADDITIVES_DEAL_ITEM']['NAME'] => $itemId
			],
			'runtime' => [
				'ADDITIVE' => [
	        		'data_type' => $this->additiveService->factory->getDataClass(),
			        'reference' => [
			            "=this.{$this->fields['SP_DEAL_ITEMS_ADDITIVES_ADDITIVE']['NAME']}" => 'ref.ID'
			        ],
			        'join_type' => 'left'
			    ],
				'PRODUCT' => [
	        		'data_type' => $this->productService->factory->getDataClass(),
			        'reference' => [
			            "=this.ADDITIVE.{$this->additiveService->fields['SP_ADDITIVE_PRODUCT']['NAME']}" => 'ref.ID'
			        ],
			        'join_type' => 'left'
			    ]
			]
		]);
		
		return array_map(
			fn($item) => $this->mapItem($item),
			$additives ?? []
		);
	}
	
	public function removeAdditiveFromItem($itemAdditiveId) {
		$item = $this->factory->getItem($itemAdditiveId);
		
		if (!$item) {
			return null;
		}
		
		$operation = $this->factory->getDeleteOperation($item);
		return $operation->launch();
	}
	
	private function mapItem($item) {
		return [
			'id' => $item->getId(),
			'price' => \Otus\Helper\MoneyFieldHelper::getMoneyNumber($item->get($this->fields['SP_DEAL_ITEMS_ADDITIVES_PRICE']['NAME'])),
			'name' => $item->get('ADDITIVE')->getTitle(),
			'additive' => [
				'id' => $item->get('ADDITIVE')->getId(),
				'consumption' => $item->get('ADDITIVE')->get($this->additiveService->fields['SP_ADDITIVE_CONSUMPTION']['NAME']),
			],
			'product' => [
				'id' => $item->get('PRODUCT')->getId(),
			]
		];
	}
}