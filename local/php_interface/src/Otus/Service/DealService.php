<?

namespace Otus\Service;

use Bitrix\Main\Web\Json;

use Bitrix\Main\Type\DateTime;

class DealService extends BaseCRMService {
	protected $contactService;
	protected $branchService;
	protected $menuService;
	protected $menuAvailabilityService;
	protected $dealItemService;
	protected $dealItemAdditiveService;
	
	public function __construct() {
		parent::__construct('CRM_DEAL');
		
		$this->contactService = new ContactService();
		$this->branchService = new BranchService();
		$this->menuService = new MenuService();
		$this->menuAvailabilityService = new MenuAvailabilityService();
		$this->dealItemService = new DealItemService();
		$this->dealItemAdditiveService = new DealItemAdditiveService();
	}
	
	public function add($fields) {
		try {
			// определение клиента, сделавшего заказ
			$contactId = $this->prepareContactForDeal($fields['client']);
	
			// определние оператора, который возьмет заказ		
			$dealOperator = $this->prepareDealOperator($fields['branch']['id']);
			
			// проверка доступности элементов заказа в меню
			$this->checkDealItemsAvailability($fields['branch']['id'], $fields['items']);
			
			// добавление всех элементов меню + добавок из заказа
			$menuItemsToAdd = $this->prepareDealItemsToAdd($fields['items']);

			// основные поля
			$dealFields = [
				'ASSIGNED_BY_ID' => $dealOperator['ID'] ?? 1,
				
				$this->fields['DEAL_ADDRES']['NAME'] => $fields['address']['text'],
				$this->fields['DEAL_PICKUP']['NAME'] => $fields['pickup'],
				$this->fields['DEAL_DELIVERY_DATETIME']['NAME'] => new DateTime($fields['receiptDatetime'], "Y-m-d H:i:s"),
				$this->fields['DEAL_CLIENT']['NAME'] => $contactId,
			];
			
			$item = $this->factory->createItem($dealFields);
			$operation = $this->factory->getAddOperation($item);
			$result = $operation->launch();
			
			if ($result->isSuccess()) {
				$dealId = $result->getId();
				
				// добавление элементов меню и добавок в заказ
				foreach ($menuItemsToAdd as $item) {
					// TODO: что делать, если одна из записей не добавилась?
					$menuItemAddResult = $this->dealItemService->addItemToDeal($dealId, $item);
					
					if (!empty($item['additives']) && $menuItemAddResult->isSuccess()) {
						foreach ($item['additives'] as $item) {
							// TODO: что делать, если одна из записей не добавилась?
							$additiveAddResult = $this->dealItemAdditiveService->addAdditiveToItem($menuItemAddResult->getId(), $item);
						}
					}
					
				}

				
				return $result;
			} else {
				throw new \Exception("Произошла ошибка при создании заказа: " . Json::encode($result->getErrorMessages()));
			}
		} catch (\Throwable $ex) {
			throw new \Exception("Произошла ошибка при создании заказа: " . $ex->getMessage());
		}
	}
	
	private function prepareContactForDeal($contactFields) {
		if (!empty($contactFields['id'])) {
			$contact = $this->contactService->getById($contactFields['id']);
		}
		
		if (empty($contact) && !empty($contactFields['phone'])) {
			$contact = $this->contactService->getByPhone($contactFields['phone']);
			
			if (empty($contact['id'])) {
				$contactCreateResult = $this->contactService->add($contactFields);
			}
		}
		
		$contactId = $contact['id'] ?? ($contactCreateResult->isSuccess() ? $contactCreateResult->getId() : null);
		
		if (empty($contactId)) {
			throw new \Exception("Не удалось определить клиента для создания заказа");
		}
		
		return $contactId;
	}
	
	private function prepareDealOperator($branchId) {
		$branchOperators = $this->branchService->getOperatorsList($branchId);
		// определяем случайного оператора - ответственного за заказ
		$dealOperator = $branchOperators[array_rand($branchOperators, 1)];
		
		return $dealOperator;
	}
	
	private function checkDealItemsAvailability($branchId, $items) {
		$availabelItems = $this->menuAvailabilityService->getAvailabelItemsByBranch($branchId);
		$availabelItemsMap = [];
		foreach ($availabelItems as $item) {
			$availabelItemsMap[$item['menuItem']] = $item;
		}
		foreach ($items as $item) {
			if (empty($availabelItemsMap[$item['id']]) || empty($availabelItemsMap[$item['id']]['available'])) {
				throw new \Exception("Элемент меню {$item['id']} недоступен в филиале {$branchId}");
			}
		}
	}
	
	private function prepareDealItemsToAdd($items) {
		$menuItems = $this->menuService->getAll(
			array_map(
				fn($item) => $item['id'],
				$items
			)
		);
		$menuItemsMap = [];
		foreach ($menuItems as $item) {
			$item['additivesMap'] = [];
			foreach ($item['additives'] as $additive) {
				$item['additivesMap'][$additive['id']] = $additive;
			}
			$menuItemsMap[$item['id']] = $item;
		}
		$menuItemsToAdd = [];
		foreach ($items as $item) {
			$menuItem = $menuItemsMap[$item['id']];
			
			$itemToAdd = [
				'id' => $item['id'],
				'count' => $item['count'],
				'price' => $menuItem['price']
			];
			
			foreach ($item['additives'] as $additive) {
				if (
					empty($menuItem['additivesMap'][$additive['id']])
				) {
					throw new \Exception("Добавку {$additive['id']} нельзя добавить к элементу меню {$item['id']}");
				}
				
				$itemToAdd['additives'][] = [
					'price' => $menuItem['additivesMap'][$additive['id']]['price'],
					'id' => $additive['id']
				];
			}
			
			$menuItemsToAdd[] = $itemToAdd;
		}
		
		return $menuItemsToAdd;
	}
}