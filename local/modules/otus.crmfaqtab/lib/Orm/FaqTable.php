<?php

namespace Otus\Crmfaqtab\Orm;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

class FaqTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'otus_faq';
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete()
                ->configureTitle('ID'),

            (new StringField('QUESTION'))
                ->configureRequired()
                ->configureSize(1000)
                ->configureTitle('Вопрос'),

            (new StringField('ANSWER'))
                ->configureRequired()
                ->configureSize(1000)
                ->configureTitle('Ответ'),
        ];
    }
}