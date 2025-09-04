<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = [
    "NAME" => 'Получение заказчика по ИНН',
    "DESCRIPTION" => 'Активити ищет компанию по ИНН в сервисе Dadata и заводит её в системе. Устанавливает CompanyId для связывания с компанией.',
    "TYPE" => "activity",
    "CLASS" => "GetCustomerByInnActivity",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "other",
    ],
    "RETURN" => [
        "CompanyId" => [
            "NAME" => 'ID компании',
            "TYPE" => "string",
        ],
    ],
];