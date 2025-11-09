<?

namespace Otus\Service;

class NotificationsQueueService extends BaseCRMService {
	public function __construct() {
		parent::__construct('SP_NOTIFICATIONS_QUEUE');
	}
	
	public function add($dealId, $stage, $text) {
		$stageId = $this->resolveEnumItemByValue('SP_NOTIFICATIONS_QUEUE_DEAL_STAGE', $stage)['ID'];
		
		if (!$stageId) {
			return false;
		}
		
		$existed = $this->isNotificationExists($dealId, $stageId);
		
		if (!empty($existed)) {
			$result = new \Bitrix\Main\Result();
			$result->setData(['id' => $existed['ID']]);
			return $result;
		}
		
		$item = $this->factory->createItem([
			$this->fields['SP_NOTIFICATIONS_QUEUE_TEXT']['NAME'] => $name,
			$this->fields['SP_NOTIFICATIONS_QUEUE_DEAL_STAGE']['NAME'] => $stageId,
			$this->fields['SP_NOTIFICATIONS_QUEUE_DEAL']['NAME'] => $dealId,
			$this->fields['SP_NOTIFICATIONS_QUEUE_TEXT']['NAME'] => $text,
		]);
		
		$operation = $this->factory->getAddOperation($item);
		$operation->disableCheckAccess();
		$result = $operation->launch();
		
		return $result;
	}
	
	public function isNotificationExists($dealId, $stageId) {
		$item = $this->factory->getItems([
			'limit' => 1,
			'select' => ['ID'],
			'filter' => [
				$this->fields['SP_NOTIFICATIONS_QUEUE_DEAL_STAGE']['NAME'] => $stageId,
				$this->fields['SP_NOTIFICATIONS_QUEUE_DEAL']['NAME'] => $dealId,
			]
		])[0];
		
		return $item;
	}
	
	public function getQueue() {
		$registerStatusId = $this->resolveEnumItemByValue('SP_NOTIFICATIONS_QUEUE_STATUS', "Зарегистрировано")['ID'];
		
		$items = $this->factory->getItems([
			'filter' => [
				$this->fields['SP_NOTIFICATIONS_QUEUE_STATUS']['NAME'] => $registerStatusId
			],
			'select' => [
				'ID',
				$this->fields['SP_NOTIFICATIONS_QUEUE_TEXT']['NAME'],
				$this->fields['SP_NOTIFICATIONS_QUEUE_DEAL_STAGE']['NAME'],
				$this->fields['SP_NOTIFICATIONS_QUEUE_DEAL']['NAME'],
			]
		]);
		
		return array_map(
			fn($item) => $this->mapItem($item),
			$items
		);
	}
	
	public function updateNotificationStatus($id, bool $success, $errorText) {
		$item = $this->factory->getItem($id);
		
		if (!$item) {
			return false;
		}
		
		$successStatus = $this->resolveEnumItemByValue('SP_NOTIFICATIONS_QUEUE_STATUS', "Отправлено")['ID'];
		$failureStatus = $this->resolveEnumItemByValue('SP_NOTIFICATIONS_QUEUE_STATUS', "Ошибка")['ID'];
		
		$item->set(
			$this->fields['SP_NOTIFICATIONS_QUEUE_STATUS']['NAME'],
			$success ? $successStatus : $failureStatus
		);
		
		if (!$success) {
			$item->set(
				$this->fields['SP_NOTIFICATIONS_QUEUE_ERROR_TEXT']['NAME'],
				$errorText ?? ""
			);
		}
		
		$operation = $this->factory->getUpdateOperation($item);
		$operation->disableCheckAccess();
		return $operation->launch();
	}
	
	private function mapItem($item) {
		return [
			'id' => $item->getId(),
			'text' => $item->get($this->fields['SP_NOTIFICATIONS_QUEUE_TEXT']['NAME']),
			'stage' => $item->get($this->fields['SP_NOTIFICATIONS_QUEUE_DEAL_STAGE']['NAME']),
			'deal' => [
				'id' => $item->get($this->fields['SP_NOTIFICATIONS_QUEUE_DEAL']['NAME']),
			]
		];
	}
}