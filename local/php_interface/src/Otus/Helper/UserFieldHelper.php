<?

namespace Otus\Helper;

class UserFieldHelper {
	/**
	 * Получает пользовательские поля по коду сущности
	 * Ключами массива-ответа являются XML_ID
	 * Если у поля нет XML_ID, оно не попадает в результат
 	*/
	public static function getUserFieldsByCode($code) {
		$result = [];
		
		$entityFields = \Bitrix\Main\UserFieldTable::getList([
			'filter' => [
				'ENTITY_ID' => $code,
			]
		])->fetchAll();
		
		foreach($entityFields as $field) {
			if (!empty($field['XML_ID'])) {
				$result[$field['XML_ID']] = [
					'ID' => $field['ID'],
					'XML_ID' => $field['XML_ID'],
					'NAME' => $field['FIELD_NAME'],
					'MULTIPLE' => $field['MULTIPLE'] == "Y",
					'TYPE' => $field['USER_TYPE_ID'],
				];
			}
		}
		
		return $result;
	}
}