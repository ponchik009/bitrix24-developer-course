<?

namespace Otus\Service;

class BranchService extends BaseCRMService {
	public function __construct() {
		parent::__construct('SP_BRANCH');
	}
	
	public function getAll() {
		$branches = $this->factory->getItems([
			'select' => [
				'ID',
				'TITLE',
				$this->fields['SP_BRANCH_ADDRESS']['NAME'],
			]
		]);
		
		return array_map(fn($item) => $this->mapItem($item), $branches);
	}
	
	public function getCurrentUserBranch() {
		global $USER;
		$userId = $USER->GetID();
		
		$currentUser = \Bitrix\Main\UserTable::getList([
			'select' => ['ID', 'UF_DEPARTMENT'],
			'filter' => [
				'ID' => $userId,
			],
			'limit' => 1
		])->fetchAll()[0];
		
		if (!empty($currentUser)) {
			$branch = $this->factory->getItems([
				'select' => [
					'ID',
					'TITLE',
					$this->fields['SP_BRANCH_ADDRESS']['NAME'],
				],
				'filter' => [
					$this->fields['SP_BRANCH_BRANCH_SECTION']['NAME'] => $currentUser['UF_DEPARTMENT']
				],
				'limit' => 1,
			])[0];
			
			return $branch ? $this->mapItem($branch) : null;
		}
	}
	
	public function getOperatorsList($branchId) {
		return $this->getUsersListByBranchAndRole($branchId, 18);
	}
	
	public function getChiefsList($branchId) {
		return $this->getUsersListByBranchAndRole($branchId, 20);
	}
	
	private function getUsersListByBranchAndRole($branchId, $roleId) {
		$result = \Bitrix\Main\UserTable::getList([
			'select' => ['ID'],
			'filter' => [
				'UF_DEPARTMENT' => $branchId,
				'=GROUPS.GROUP_ID' => $roleId,
			],
		    'runtime' => [
		        new \Bitrix\Main\Entity\ReferenceField(
		            'GROUPS',
		            '\Bitrix\Main\UserGroupTable',
		            ['=this.ID' => 'ref.USER_ID']
		        )
		    ]
		])->fetchAll();
		
		return $result;
	}
	
	private function mapItem($item) {
		return [
			'id' => $item->getId(),
			'name' => $item->getTitle(),
			'address' => $item->get($this->fields['SP_BRANCH_ADDRESS']['NAME'])
		];
	}
}