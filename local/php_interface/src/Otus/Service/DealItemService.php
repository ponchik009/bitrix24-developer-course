<?

namespace Otus\Service;

class DealItemService extends BaseCRMService {
	protected $menuService;
	protected $dealItemAdditiveService;
	
	public function __construct() {
		parent::__construct('SP_DEAL_ITEMS');
		
		$this->menuService = new MenuService();
		$this->dealItemAdditiveService = new DealItemAdditiveService();
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
	
	public function deleteItemFromDeal($itemId) {
		$item = $this->factory->getItem(intval($itemId));
		
		if (!$item) {
			return null;
		}
		
		$operation = $this->factory->getDeleteOperation($item);
		return $operation->launch();
	}
	
	public function getDealItems($dealId) {
		$items = $this->factory->getItems([
			'select' => [
				'ID',
				$this->fields['SP_DEAL_ITEMS_MENU_ITEM']['NAME'],
				$this->fields['SP_DEAL_ITEMS_COUNT']['NAME'],
				$this->fields['SP_DEAL_ITEMS_PRICE']['NAME'],
				'MENU_ITEM'
			],
			'filter' => [
				$this->fields['SP_DEAL_ITEMS_DEAL']['NAME'] => $dealId
			],
			'runtime' => [
				'MENU_ITEM' => [
	        		'data_type' => $this->menuService->factory->getDataClass(),
			        'reference' => [
			            "=this.{$this->fields['SP_DEAL_ITEMS_MENU_ITEM']['NAME']}" => 'ref.ID'
			        ],
			        'join_type' => 'left'
			    ]
			]
		]);
		
		$result = [];
		
		foreach ($items as $item) {
			$itemToReturn = $this->mapItem($item);
			
			$itemToReturn['additives'] = $this->dealItemAdditiveService->getAdditivesByItemId($itemToReturn['id']);
			
			$result[] = $itemToReturn;
		}
		
		return $result;
	}
	
	private function mapItem($item) {
		return [
			'id' => $item->getId(),
			'price' => \Otus\Helper\MoneyFieldHelper::getMoneyNumber($item->get($this->fields['SP_DEAL_ITEMS_PRICE']['NAME'])),
			'name' => $item->get('MENU_ITEM')->getTitle(),
			'quantity' => $item->get($this->fields['SP_DEAL_ITEMS_COUNT']['NAME']),
			
		];
	}
}