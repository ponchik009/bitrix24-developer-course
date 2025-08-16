<?php
namespace Otus\Crmfaqtab\Data;

use Otus\Crmfaqtab\Orm\FaqTable;

use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

class TestDataInstaller
{
    public static function addQuestions(): void
    {
        $questions = [
            [
                'QUESTION' => 'Как перевести сделку в следующую стадию?',
                'ANSWER' => 'Перевести сделку в новую стадию можно в карточке сделки, кликнув по необходимой стадии. Аналогичный способ - перетащить сделку на странице списка в представлении "Канбан". Обратите внимание, что перевод сделок из определенных стадий может быть доступен только с определенными правами. Ознакомтесь с инструкцией.',
            ],
            [
                'QUESTION' => 'Тестовый вопрос 1',
                'ANSWER' => 'Тестовый ответ 1',
            ],
            [
                'QUESTION' => 'Тестовый вопрос 2',
                'ANSWER' => 'Тестовый ответ 2',
            ],
            [
                'QUESTION' => 'Тестовый вопрос 3',
                'ANSWER' => 'Тестовый ответ 3',
            ],
        ];

        foreach ($questions as $question) {
            FaqTable::add($question);
        }
    }
}