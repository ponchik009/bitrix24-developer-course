<?

namespace Otus\Service;

class CriticalRemainderService extends BaseCRMService {
	public function __construct() {
		parent::__construct('SP_CRITICAL_REMAINDER');
	}
	
	public function getCriticalRemaindersByBranch($branchId) {
		$items = $this->factory->getItems([
			'filter' => [
				$this->fields['SP_CRITICAL_REMAINDER_BRANCH']['NAME'] => $branchId
			],
			'select' => [
				'ID',
				$this->fields['SP_CRITICAL_REMAINDER_BALANCE']['NAME'],
				$this->fields['SP_CRITICAL_REMAINDER_PRODUCT']['NAME'],
			]
		]);
		
		return array_map(
			fn($item) => $this->mapItem($item),
			$items
		);
	}
	
	private function mapItem($item) {
		return [
			'id' => $item->getId(),
			'balance' => $item->get($this->fields['SP_CRITICAL_REMAINDER_BALANCE']['NAME']),
			'productId' => $item->get($this->fields['SP_CRITICAL_REMAINDER_PRODUCT']['NAME']),
		];
	}
}