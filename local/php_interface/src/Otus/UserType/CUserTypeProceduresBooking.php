<?php

namespace Otus\UserType;

use Bitrix\Main\Loader;
use Bitrix\Iblock;
use Bitrix\Iblock\Elements\ElementDoctorsTable;

/**
 * Реализация свойства "Запись на процедуры"
 */
class CUserTypeProceduresBooking {
    /**
     * Метод возвращает массив описания собственного типа свойств
     * @return array
     */
    public static function GetUserTypeDescription()
    {
        return array(
            'USER_TYPE_ID' => 'procedures_booking', // Уникальный идентификатор типа свойств
            'USER_TYPE' => 'PROCEDURES_BOOKING',
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => 'Запись на процедуры',
            'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
            'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
        );
    }
    
    /**
     * Метод возвращает представление просмотра в публичной части
     * 
     * @return string
     */
	public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
	{
		// не очень оптимальное, но пока что самое простое решение
		
		$doctor = ElementDoctorsTable::getList([
			'filter' => [
				'=ID' => $arProperty["ELEMENT_ID"]
			],
			'select' => [
				'ID',
				'PROCEDURE_IDS.ELEMENT',
			]
		])->fetchObject();
		
		// но почему-то jquery не подключается :(
		\CJSCore::Init(['masked_input', 'popup', 'jquery']);
		
		$result = '
			<script defer src="/local/php_interface/src/Otus/UserType/assets/procedure_booking.js?t=' . time() . '"></script>
			<link type="text/css" rel="stylesheet" href="/local/php_interface/src/Otus/UserType/assets/procedure_booking.css" />
			<div>
		';
		
		foreach ($doctor->getProcedureIds()->getAll() as $procedure) {
			$procedureName = $procedure->getElement()->getName();
			$procedureId = $procedure->getElement()->getId();
			$result .= '<a data-doctor-id="' . $doctor->getId() . '" data-procedure="' . $procedureName . '" data-procedure-id="' . $procedureId . '" href="#" class="link">' . $procedureName . '</a><br>';
		}
		
		$result .= "</div>";
		
		return $result;
	}
}