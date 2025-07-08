<?php
namespace Otus\Orm;

use Bitrix\Main\ORM\Data\DataManager;

use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;

use Bitrix\Iblock\Elements\ElementdoctorsTable;

use Bitrix\Main\ORM\Query\Join;

class PatientTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'clinic_patient';
    }

    public static function getMap()
	{
		return [            
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

            (new StringField('FIRST_NAME'))
                ->configureRequired()
                ->configureSize(255),
                
            (new StringField('LAST_NAME'))
                ->configureRequired()
                ->configureSize(255),
                
            (new StringField('SECOND_NAME'))
                ->configureRequired()
                ->configureSize(255),
                
            (new DateField('BIRTH_DATE'))
                ->configureRequired(),
               
            // пример one-to-one связи ПАЦИЕНТ - КАРТОЧКА
            (new Reference(
                'PATIENT_CARD',
                PatientCardTable::class,
                Join::on('this.ID', 'ref.PATIENT_ID')
            ))
            	// left join, карточки может не существовать
            	->configureJoinType('left'),
            
            // пример one-to-many связи ДОВЕРЕННЫЙ - ДОВЕРИТЕЛЬ (на эту же таблицу, хотя на практике доверенное лицо может не быть пациентом)
            (new IntegerField('TRUSTEE_ID')),
            (new Reference(
                'TRUSTEE',
                PatientTable::class,
                Join::on('this.TRUSTEE_ID', 'ref.ID')
            ))
            	->configureJoinType('left'),
            (new OneToMany('PRINCIPALS', PatientTable::class, 'TRUSTEE'))
            	->configureJoinType('left'),
            	
            (new ManyToMany('FAVORITE_DOCTORS', ElementdoctorsTable::class))
                ->configureTableName('clinic_favorite_doctors')
                ->configureLocalPrimary('ID', 'PATIENT_ID')
                ->configureRemotePrimary('ID', 'DOCTOR_ID'),
		];
	}
}