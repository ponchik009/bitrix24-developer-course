<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

?>

<?php
$APPLICATION->IncludeComponent(
    'otus:deal.order.items',
    '',
    array(
        'DEAL_ID' => 14,
        'CAN_EDIT' => true
    )
);
?>

<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");

?>