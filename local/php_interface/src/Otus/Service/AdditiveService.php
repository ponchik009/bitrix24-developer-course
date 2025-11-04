<?

namespace Otus\Service;

use Bitrix\Main\Entity\Query;

class AdditiveService extends BaseCRMService {
	protected $productService;
	
	public function __construct() {
		parent::__construct('SP_ADDITIVES');
		
		$this->productService = new ProductService();
	}
	
	public function getAdditiveByMenuItem($menuItemId) {
		$productDataClass = $this->productService->factory->getDataClass();
		
		$query = new Query($this->factory->getDataClass());
		$query->setSelect([
		    'ID',
		    $this->fields['SP_ADDITIVE_PRICE']['NAME'],
		    $this->fields['SP_ADDITIVE_PRODUCT']['NAME'],
		    'PRODUCT_TITLE' => 'PRODUCT.TITLE'
		]);
		$query->setFilter([
			$this->fields['SP_ADDITIVE_MENU_ITEM']['NAME'],
			$menuItemId
		]);
		
		$query->registerRuntimeField(
		    'PRODUCT',
		    [
		        'data_type' => $productDataClass,
		        'reference' => [
		            "=this.{$this->fields['SP_ADDITIVE_PRODUCT']['NAME']}" => 'ref.ID'
		        ],
		        'join_type' => 'left'
		    ]
		);
		
		$additives = $query->exec();
		$result = [];
		
		while ($row = $additives->fetch()) {
			$result[] = $this->mapItem($row);
		}
		
		return $result;
	}
	
	private function mapItem($item) {
		return [
			'id' => $item['ID'],
			'name' => $item['PRODUCT_TITLE'],
			'price' => \Otus\Helper\MoneyFieldHelper::getMoneyNumber($item[$this->fields['SP_ADDITIVE_PRICE']['NAME']]),
		];
	}
}