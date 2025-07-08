<?php
namespace Otus\Orm;

use Bitrix\Main\Type;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class PatientCardTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'clinic_patient_card';
    }

    public static function getMap()
	{
		return [            
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),
                
            (new DateField('CREATED_AT'))
            	->configureRequired()
                ->configureDefaultValue(new Type\DateTime),
                
            (new IntegerField('PATIENT_ID'))
            	->configureRequired(),
            (new Reference(
                'PATIENT',
                PatientTable::class,
                Join::on('this.PATIENT_ID', 'ref.ID')
            ))
            	->configureJoinType('inner'),
		];
	}
}