<?

namespace Otus\Agent;

class SendNotificationsAgent implements \Otus\Interface\Runnable {
	public static function run() {
		try {
			$notificationsService = new \Otus\Service\NotificationsQueueService();
			$dealService = new \Otus\Service\DealService();
			$contactService = new \Otus\Service\ContactService();
	
			$notifications = $notificationsService->getQueue();
			
			foreach ($notifications as $notification) {
				$deal = $dealService->factory->getItem($notification['deal']['id']);
				
				if (!$deal) {
					$notificationsService->updateNotificationStatus($notification['id'], false, "Сделка не найдена");
					continue;
				}
				
				$contact = $contactService->getById(
					$deal->get(
						$dealService->fields['DEAL_CLIENT']['NAME']
					)
				);
				
				if (!$contact['phone']) {
					$notificationsService->updateNotificationStatus($notification['id'], false, "Клиент не найден");
					continue;
				}
				
				$result = self::sendNotification([
					'message' => $notification['text'],
					'phone' => $contact['phone']
				]);
				
				$updateResult = $notificationsService->updateNotificationStatus($notification['id'], $result, $result ? null : "Не удалось отправить уведомление");
				
				if (!$updateResult->isSuccess()) {
					AddMessage2Log($updateResult->getErrorMessages());
				}
			}
		} catch (\Throwable $ex) {
			AddMessage2Log($ex->getMessage());
		}
		
		return "\Otus\Agent\SendNotificationsAgent::run();";
	}
	
	private static function sendNotification($data) {
	    $httpClient = new \Bitrix\Main\Web\HttpClient();
	    $httpClient->setHeader('Content-Type', 'application/json');
	
	    $result = $httpClient->post("https://www.randomnumberapi.com/api/v1.0/random?min=1&max=100", json_encode($data));
	
	    if ($result === false) {
	        $errors = $httpClient->getError();
	        
	        AddMessage2Log($errors);
	        
	        return false;
	    }
	
	    return json_decode($result, true)[0] <= 90;
	}
}