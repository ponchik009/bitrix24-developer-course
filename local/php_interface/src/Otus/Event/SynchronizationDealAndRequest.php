<?

namespace Otus\Event;

use Bitrix\Crm\Service;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\Elements\ElementRequestsTable;

class SynchronizationDealAndRequest {
	/**
	 * Синхронизация Заявки при обновлении сделки
 	*/
	public static function synchronizeToRequestOnDealUpdate($arFields) {
		// поиск связанной Заявки
		$request = ElementRequestsTable::getList([
			'filter' => [
				'=DEAL_VALUE' => $arFields['ID']
			],
			'select' => [
				'ID',
				'NAME',
				'DEAL_' => 'DEAL',
			]
		])->fetch();
		
		if (empty($request)) {
			return true;
		}
		
		$sum = $arFields['OPPORTUNITY'] . '|' . $arFields['CURRENCY_ID'];
		$responsible = $arFields['ASSIGNED_BY_ID'] ?? 1;
		
		$updateData = [];
		if (!empty($arFields['OPPORTUNITY']) && !empty($arFields['CURRENCY_ID'])) {
			$updateData['SUM']['VALUE'] = $sum;
		}
		if (!empty($arFields['ASSIGNED_BY_ID'])) {
			$updateData['RESPONSIBLE'] = $responsible;
		}
		
		$iblockId = ElementRequestsTable::getEntity()->getIblock()->getId();
		\CIBlockElement::SetPropertyValuesEx($request['ID'], $iblockId, $updateData);
	}
	
	/**
	 * Синхронизация сделки при обновлении Заявки
 	*/
	public static function synchronizeToDealOnRequestUpdate($arFields) {
		$iblockId = ElementRequestsTable::getEntity()->getIblock()->getId();
		
		// если изменился элемент другого инфоблока
		if ($iblockId != $arFields['IBLOCK_ID']) {
			return true;
		}
		
		// получение полей инфоблока (чтобы не работать с ID поля напрямую)
		$properties = PropertyTable::getList([
		    'filter' => [
		    	'IBLOCK_ID' => $iblockId
		    ],
		    'select' => ['ID', 'CODE', 'NAME']
		])->fetchAll();
		$propertiesMap = [];
		foreach ($properties as $prop) {
			$propertiesMap[$prop['CODE']] = $prop;
		}
		
		// получение текущих значений из $arFields
		[$currentSum, $currencyId] = explode(
			"|", 
			array_values( $arFields["PROPERTY_VALUES"][$propertiesMap['SUM']['ID']] ?? [] )[0]["VALUE"]
		);
		$currentResponsible = $arFields["PROPERTY_VALUES"][$propertiesMap['RESPONSIBLE']['ID']];
		$dealId = array_values($arFields["PROPERTY_VALUES"][$propertiesMap['DEAL']['ID']] ?? [])[0]["VALUE"];
		
		if (empty($dealId)) {
			return true;
		}
		
		// получение сделки и обновление данных
		$container = Service\Container::getInstance();
    	$factory = $container->getFactory(\CCrmOwnerType::Deal);
    	$deal = $factory->getItem($dealId);
    	
    	if (!empty($deal)) {
    		if (!empty($currencyId)) {
	    		$deal->set("OPPORTUNITY", $currentSum);
	    		$deal->set("CURRENCY_ID", $currencyId ?? "RUB");
    		}
    		if (!empty($currentResponsible)) {
    			$deal->set("ASSIGNED_BY_ID", $currentResponsible ?? 1);
    		}
    		
    		$operation = $factory->getUpdateOperation($deal);
    		$operation->disableAfterSaveActions();
    		$result = $operation->launch();
    		
    		if (!empty($result->getErrorMessages())) {
    			throw new Exception(implode(";", $result->getErrorMessages()));
    		}
    		
    		return $result->isSuccess();
    	}
    	
    	return true;
	}
}

?>