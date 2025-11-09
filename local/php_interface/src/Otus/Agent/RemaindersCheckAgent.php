<?

namespace Otus\Agent;

use Bitrix\Main\Loader;

use Bitrix\Tasks\Internals\TaskTable;
use Bitrix\Tasks\Internals\Task\TagTable;
use Bitrix\Tasks\Internals\Task\Status;

class RemaindersCheckAgent implements \Otus\Interface\Runnable {
	const TAG_NAME = 'Заказ продуктов';
	
	public static function run() {
		Loader::includeModule('tasks');
		
		$branchService = new \Otus\Service\BranchService();
		$criticalRemainderService = new \Otus\Service\CriticalRemainderService();
		$productRemainderService = new \Otus\Service\ProductRemainderService();
		
		$branches = $branchService->getAll();
		foreach ($branches as $branch) {
			$currentRemainders = $productRemainderService->getRemainders($branch['id']);
			$criticalRemainders = $criticalRemainderService->getCriticalRemaindersByBranch($branch['id']);
			
			$criticalRemaindersMap = [];
			foreach ($criticalRemainders as $remainder) {
				$criticalRemaindersMap[$remainder['productId']] = $remainder;
			}
			
			// список заканчивающихся продуктов
			$notifyList = [];
			
			foreach ($currentRemainders as $remainder) {
				if (
					!empty($criticalRemaindersMap[$remainder['product']['id']])
					&& $remainder['remainder'] <= $criticalRemaindersMap[$remainder['product']['id']]['balance']
				) {
					// текущий остаток меньше либо равен критическому
					$notifyList[] = "{$remainder['product']['name']} - осталось {$remainder['remainder']} {$remainder['product']['measurmentUnit']}";
				}
			}
			
			if (empty($notifyList)) {
				return "\Otus\Agent\ReminadersCheckAgent::run();";
			}
			
			// создание / обновление задачи
			$chiefsList = $branchService->getChiefsList($branch['id']);
			$firstCheif = $chiefsList[0];
			$existingTask = self::getSimilarOpenTask($firstCheif['ID'], "Заказть продукты");
			
			if (empty($existingTask)) {
				self::createTask($firstCheif['ID'], "Заказть продукты", implode("\n", $notifyList));
			} else {
				self::updateTask($existingTask['ID'], implode("\n", $notifyList));
			}
		}
		
		return "\Otus\Agent\ReminadersCheckAgent::run();";
	}
	
    /**
     * Проверяет наличие открытых похожих задач
     */
    private static function getSimilarOpenTask($userId, $title)
    {
        $filter = [
            '=RESPONSIBLE_ID' => $userId,
            '!=STATUS' => Status::COMPLETED, // Исключаем завершенные задач
            'TITLE' => $title,
        ];
        
        $query = TaskTable::getList([
            'select' => ['ID'],
            'filter' => $filter,
            'order' => ['ID' => 'DESC'],
            'limit' => 1,
        ]);
        
        return $query->fetch();
    }
	
	/**
     * Создает новую задачу
     */
    private static function createTask($userId, $title, $description): ?int
    {
        $task = new \Bitrix\Tasks\Item\Task();
        
        $fields = [
            'TITLE' => $title,
            'DESCRIPTION' => $description,
            'RESPONSIBLE_ID' => $userId,
            'CREATED_BY' => 1,
            'STATUS' => Status::PENDING,
        ];
        
        try {
            foreach ($fields as $key => $value) {
            	$task[$key] = $value;
            }
            
            $result = $task->save();
            
            if ($result->isSuccess()) {
		        $taskId = $task->getId();
		        
		        \CTasks::AddTags($taskId, 1, [self::TAG_NAME]);

            	return $taskId;
            } else {
            	AddMessage2Log("Ошибка создания задачи: " . \Bitrix\Main\Web\Json::encode($result->getErrorMessages()));
            	return null;
            }
            
            
        } catch (\Exception $e) {
            AddMessage2Log("Ошибка создания задачи: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Обновляет описание существующей задачи
     */
    private static function updateTask($taskId, $description) {
    	try {
	    	$task = new \Bitrix\Tasks\Item\Task($taskId);
	    	$task['DESCRIPTION'] = $description;
            $result = $task->save();
            
            if (!$result->isSuccess()) {
            	AddMessage2Log("Ошибка обновления задачи: " . \Bitrix\Main\Web\Json::encode($result->getErrorMessages()));
            }
            
            return $result->isSuccess();
    	} catch (\Exception $e) {
            AddMessage2Log("Ошибка обновления задачи: " . $e->getMessage());
            return null;
        }
    }
}