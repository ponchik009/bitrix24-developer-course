<?php
require_once (__DIR__.'/crest.php');

$activityId = $_REQUEST['data']['FIELDS']['ID'];

// получение дела
$activity = CRest::call(
    'crm.activity.get',
    [
        'id' => $activityId
    ]
)['result'];

// writeToLog($activity, 'activity item');

try {
	// дело создано внутри контакта
	if (!empty($activity['OWNER_ID']) && $activity['OWNER_TYPE_ID'] == 3) {
		$contactId = $activity['OWNER_ID'];
		
		// writeToLog($contactId, 'contact id');
		
		// обновление контакта
		$result = CRest::call(
			'crm.contact.update',
			[
				'id' => $contactId,
				'fields' => [
					'UF_LAST_COMMUNICATION_DATE' => date("d.m.Y H:i:s"),
				]
			]
		);
		
		// writeToLog($result, 'contact update result');
	}
} catch (Throwable $ex) {
	writeToLog($ex->getMessage(), 'error catch');
}


/**
* Write data to log file.
*
* @param mixed $data
* @param string $title
*
* @return bool
*/
function writeToLog($data, $title = '') {
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}