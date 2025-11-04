<?

namespace Otus\Service;

use Bitrix\Main\Entity\Query;

class MenuService extends BaseCRMService {
	protected $additiveService;

	public function __construct() {
		parent::__construct('SP_MENU');
		
		$this->additiveService = new AdditiveService();
	}
	
	public function getAll($ids = []) {
		$filter = [];
		
		if (!empty($ids)) {
			$filter['ID'] = $ids;
		}
		
		$menu = $this->factory->getItems([
			'select' => [
			    'ID',
			    'TITLE',
			    $this->fields['SP_MENU_PHOTO']['NAME'],
			    $this->fields['SP_MENU_PRICE']['NAME'],
			    $this->fields['SP_MENU_DESCRIPTION']['NAME'],
			    $this->fields['SP_MENU_ADDITIVES']['NAME'],
			],
			'order' => ['TITLE' => 'ASC'],
			'filter' => $filter,
		]);
		
		$result = [];
		foreach ($menu as $item) {
			$menuItem = $this->mapItem($item);
			$menuItem['additives'] = $this->additiveService->getAdditiveByMenuItem($menuItem['id']);
			
			$result[] = $menuItem;
		}
		
		return $result;
	}
	
	private function mapItem($item) {
		$photo = \CFile::GetFileArray($item[$this->fields['SP_MENU_PHOTO']['NAME']]);

		return [
			'id' => $item['ID'],
			'name' => $item['TITLE'],
			'price' => \Otus\Helper\MoneyFieldHelper::getMoneyNumber($item[$this->fields['SP_MENU_PRICE']['NAME']]),
			'photo' => $photo ? 
				[
					'id' => $photo['ID'],
					'src' => $photo['SRC'],
				] 
				: null,
		];
	}
}