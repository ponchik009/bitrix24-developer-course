<?

namespace Otus\Helper;

use Bitrix\Main\UserFieldTable;

class UserFieldHelper {
	/**
	 * Получает пользовательские поля по коду сущности
	 * Ключами массива-ответа являются XML_ID
	 * Если у поля нет XML_ID, оно не попадает в результат
 	*/
	public static function getUserFieldsByCode($code) {
		$result = [];
		
		$entityFields = UserFieldTable::getList([
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
	
	/**
	 * Получает значения для полей-енамов
	 * Ключами массива ответа являются ID элементов списка
 	*/
 	public static function getUserFieldEnums($fieldId) {
 		$dbRes = \CUserFieldEnum::GetList(
 			['ID' => 'DESC'],
 			[
 				'USER_FIELD_ID' => $fieldId,
 			]
 		);
 		
 		$result = [];
 		
 		while ($item = $dbRes->fetch()) {
 			$result[$item['ID']] = [
 				'ID' => $item['ID'],
 				'VALUE' => $item['VALUE'],
 				'XML_ID' => $item['XML_ID'],
 			];
 		}
 		
 		return $result;
 	}
}