<?

define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

include_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

use Bitrix\Main\Application;
use Bitrix\Main\Loader;

use Bitrix\Iblock\Elements\ElementProceduresBookingTable;

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Europe/Moscow');

try {
	$request = Application::getInstance()->getContext()->getRequest();

	if ($request->isPost()) {
		// проверка прав
		global $USER;
		if (!$USER->IsAdmin()) {
			echo json_encode([
				"data" => null, 
				"message" => "Недостаточно прав для совершения действия",
				"result" => false
			], JSON_UNESCAPED_UNICODE);
			exit();
		}

		// получение полей		
		$patientPhone = preg_replace('/\D/i', '', trim($request->getPost("PHONE")));
		$patientFIO = trim($request->getPost("NAME"));
		$bookingDatetime = $request->getPost("DATETIME");
		$doctorId = $request->getPost("DOCTOR");
		$procedureId = $request->getPost("PROCEDURE");
		
		// проверка существования записей на ближайшее время
		$bookingDatetimeObj = \Bitrix\Main\Type\DateTime::createFromPhp(
			new \DateTime($bookingDatetime)
		);
		// стандартный ->format у битриксовой даты кастит в локальное время, что нам не подходит
		// либо я не понимаю, как это работает
		$bookingDatetimeFilterStart = date("Y-m-d H:i:s", (clone $bookingDatetimeObj)->add("-1 hour")->getTimestamp());
		$bookingDatetimeFilterEnd = date("Y-m-d H:i:s", (clone $bookingDatetimeObj)->add("1 hour")->getTimestamp());
		$filter = [
				'DOCTOR_VALUE' => $doctorId,
				// фильтр по дате
				// проверяет занятое время за час до и после процедуры
				'>BOOKING_DATETIME_VALUE' => $bookingDatetimeFilterStart,
				'<BOOKING_DATETIME_VALUE' => $bookingDatetimeFilterEnd,
		];
		
		$existingBooking = ElementProceduresBookingTable::getList([
			'filter' => $filter,
			'select' => [
				'DOCTOR_' => 'DOCTOR',
				'BOOKING_DATETIME_' => 'BOOKING_DATETIME',
			]
		])->fetchAll();
		
		if (!empty($existingBooking)) {
			echo json_encode([
				"data" => null, 
				"message" => "Запись на это время невозможна - около этого времени уже существует забронированная процедура",
				"result" => false
			], JSON_UNESCAPED_UNICODE);
			exit();
		}
		
		$el = new CIBlockElement;
		$resId = $el->Add([
			'NAME' => "Запись от " . date("d.m.Y H:i:s"),
			"IBLOCK_ID" => ElementProceduresBookingTable::getEntity()->getIblock()->getId(),
			"PROPERTY_VALUES" => [
				"BOOKING_DATETIME" => date("Y-m-d H:i:s", $bookingDatetimeObj->getTimestamp()),
				"PROCEDURE" => $procedureId,
				"DOCTOR" => $doctorId,
				"PATIENT_FIO" => $patientFIO,
				"PATIENT_PHONE" => $patientPhone,
			]
		]);
		
		if (!$resId) {
			echo json_encode([
				"data" => null, 
				"message" => "При сохранении записи произошла ошибка: " . $el->LAST_ERROR,
				"result" => false
			], JSON_UNESCAPED_UNICODE);
			exit();
		} else {
			echo json_encode([
				"data" => [$bookingDatetimeFilterStart, $bookingDatetimeFilterEnd],
				"message" => "Запись успешно создана!",
				"result" => true
			], JSON_UNESCAPED_UNICODE);
			exit();	
		}
	}
} catch (\Thtowable $ex) {
	http_response_code(500);
	
	echo json_encode([
		"data" => null, 
		"message" => "При выполнении запроса произошла ошибка " . $ex->getMessage(),
		"result" => false
	], JSON_UNESCAPED_UNICODE);
}
 
