<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load(['ui.buttons', 'ui.forms', 'ui.alerts', 'ajax']);

?>
<div id="deal-order-items-<?=$arParams['DEAL_ID']?>" class="deal-order-items">
    <div class="deal-order-items-header">
        <h3><?=Loc::getMessage('DEAL_ORDER_ITEMS_TITLE')?></h3>
        <?php if ($arResult['CAN_EDIT']): ?>
            <button type="button" class="ui-btn ui-btn-primary" onclick="showAddItemForm()">
                <?=Loc::getMessage('DEAL_ORDER_ITEMS_ADD_BUTTON')?>
            </button>
        <?php endif; ?>
    </div>

    <!-- Форма добавления элемента -->
    <div id="add-item-form" class="add-item-form" style="display: none;">
        <div class="ui-form">
            <div class="ui-form-row">
                <label class="ui-form-label"><?=Loc::getMessage('DEAL_ORDER_ITEMS_MENU_ITEM')?></label>
                <div class="ui-form-content">
                    <select id="menu-item-select" class="ui-ctl-element" onchange="onMenuItemChange(this.value)">
                        <option value=""><?=Loc::getMessage('DEAL_ORDER_ITEMS_SELECT_MENU_ITEM')?></option>
                    </select>
                </div>
            </div>
            
            <div class="ui-form-row">
                <label class="ui-form-label"><?=Loc::getMessage('DEAL_ORDER_ITEMS_QUANTITY')?></label>
                <div class="ui-form-content">
                    <input type="number" id="item-quantity" class="ui-ctl-element" value="1" min="1">
                </div>
            </div>

            <div class="ui-form-row">
                <label class="ui-form-label"><?=Loc::getMessage('DEAL_ORDER_ITEMS_ADDITIVES')?></label>
                <div class="ui-form-content" id="additives-container">
                    <div class="additives-loading" style="display: none;">
                        <?=Loc::getMessage('DEAL_ORDER_ITEMS_LOADING')?>
                    </div>
                </div>
            </div>

            <div class="ui-form-row">
                <label class="ui-form-label"><?=Loc::getMessage('DEAL_ORDER_ITEMS_CUSTOM_PRICE')?></label>
                <div class="ui-form-content">
                    <input type="number" id="custom-price" class="ui-ctl-element" step="0.01" placeholder="<?=Loc::getMessage('DEAL_ORDER_ITEMS_CUSTOM_PRICE_PLACEHOLDER')?>">
                </div>
            </div>

            <div class="ui-form-buttons">
                <button type="button" class="ui-btn ui-btn-primary" onclick="addOrderItem()" id="add-item-btn">
                    <?=Loc::getMessage('DEAL_ORDER_ITEMS_ADD_BUTTON')?>
                </button>
                <button type="button" class="ui-btn ui-btn-link" onclick="hideAddItemForm()">
                    <?=Loc::getMessage('DEAL_ORDER_ITEMS_CANCEL_BUTTON')?>
                </button>
            </div>
        </div>
    </div>

    <!-- Список элементов заказа -->
    <div class="order-items-list" id="order-items-container">
        
    </div>

    <!-- Итоговая сумма -->
    <div class="order-total">
        <div class="ui-alert ui-alert-success">
            <strong><?=Loc::getMessage('DEAL_ORDER_ITEMS_TOTAL_SUM')?>:</strong>
            <?=number_format($arResult['TOTAL_SUM'], 2)?> ₽
        </div>
    </div>
</div>

<script>
    // JavaScript функционал
    BX.ready(function() {
        window.dealOrderItems = {
            dealId: <?=CUtil::PhpToJSObject($arResult['DEAL_ID'])?>,
            canEdit: <?=CUtil::PhpToJSObject($arResult['CAN_EDIT'])?>
        };

        // Загрузка элементов меню
        loadMenuItems();
        
        // Рендер элементов Заказа
        updateOrderItemsList();
    });

    function showAddItemForm() {
        BX('add-item-form').style.display = 'block';
    }

    function hideAddItemForm() {
        BX('add-item-form').style.display = 'none';
    }
    
    function resetAddItemForm() {
        BX('menu-item-select').value = '';
        BX('item-quantity').value = '1';
        BX('custom-price').value = '';
        BX('additives-container').innerHTML = '';
        BX('add-item-btn').disabled = false;
    }

    function loadMenuItems() {
        BX.ajax.runComponentAction('otus:deal.order.items', 'getMenuItems', {
            mode: 'class',
            data: {
                dealId: dealOrderItems.dealId
            }
        }).then(function(response) {
            var select = BX('menu-item-select');
            select.innerHTML = '<option value=""><?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_SELECT_MENU_ITEM'))?></option>';
            
            response.data.forEach(function(item) {
                var option = new Option(item.TITLE, item.ID);
                select.appendChild(option);
            });
        });
    }
    
    function onMenuItemChange(menuItemId) {
        if (!menuItemId) {
            BX('additives-container').innerHTML = '';
            return;
        }

        BX('additives-container').innerHTML = '<div class="additives-loading"><?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_LOADING'))?></div>';
        
        BX.ajax.runComponentAction('otus:deal.order.items', 'getAdditivesByMenuItem', {
        	mode: 'class',
            data: {
                menuItemId: menuItemId
            }
        }).then(function(response) {
            var container = BX('additives-container');
            container.innerHTML = '';

            if (response.status === 'success' && response.data.length > 0) {
                response.data.forEach(function(additive) {
                    var wrapper = BX.create('div', {
                        props: {className: 'ui-ctl ui-ctl-checkbox additive-checkbox-wrapper'},
                        children: [
                            BX.create('input', {
                                props: {
                                    type: 'checkbox',
                                    value: additive.ID,
                                    className: 'ui-ctl-element additive-checkbox'
                                }
                            }),
                            BX.create('div', {
                                props: {className: 'ui-ctl-label-text'},
                                text: additive.TITLE + ' (+' + additive.PRICE + ' ₽)'
                            })
                        ]
                    });

                    container.appendChild(wrapper);
                });
            } else {
                container.innerHTML = '<div class="no-additives"><?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_NO_ADDITIVES'))?></div>';
            }
        });
    }

    function addOrderItem() {
        var menuItemId = BX('menu-item-select').value;
        var quantity = parseInt(BX('item-quantity').value);
        var customPrice = BX('custom-price').value;
        
        // Собираем выбранные добавки
        var additiveCheckboxes = BX.findChildren(BX('additives-container'), {className: 'additive-checkbox'}, true);
        var additives = [];
        additiveCheckboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                additives.push(checkbox.value);
            }
        });

        if (!menuItemId) {
            showNotification('<?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_SELECT_MENU_ITEM_ERROR'))?>', 'error');
            return;
        }

        if (!quantity || quantity < 1) {
            showNotification('<?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_QUANTITY_ERROR'))?>', 'error');
            return;
        }

        // Блокируем кнопку на время выполнения
        var addButton = BX('add-item-btn');
        addButton.disabled = true;
        addButton.textContent = '<?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_ADDING'))?>';

        BX.ajax.runComponentAction('otus:deal.order.items', 'addOrderItem', {
        	mode: 'class',
            data: {
                dealId: dealOrderItems.dealId,
                menuItemId: menuItemId,
                quantity: quantity,
                customPrice: customPrice,
                additives: additives
            }
        }).then(function(response) {
            if (response.status === 'success') {
                showNotification(response.data.message, 'success');
                hideAddItemForm();
                updateOrderItemsList(response.data.items);
                resetAddItemForm();
            } else {
                showNotification(response.errors[0].message, 'error');
            }
            addButton.disabled = false;
            addButton.textContent = '<?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_ADD_BUTTON'))?>';
        }).catch(function(response) {
            showNotification(response.errors[0].message, 'error');
            addButton.disabled = false;
            addButton.textContent = '<?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_ADD_BUTTON'))?>';
        });
    }

    function deleteItem(itemId) {
        if (!confirm('<?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_DELETE_CONFIRM'))?>')) {
            return;
        }

        BX.ajax.runComponentAction('otus:deal.order.items', 'deleteOrderItem', {
        	mode: 'class',
            data: {
                dealId: dealOrderItems.dealId,
                itemId: itemId
            }
        }).then(function(response) {
            if (response.status === 'success') {
                showNotification(response.data.message, 'success');
                updateOrderItemsList(response.data.items);
            } else {
                showNotification(response.errors[0].message, 'error');
            }
        });
    }
    
    
    function updateOrderItemsList(items) {
        // Обновляем список элементов через AJAX
        BX.ajax.runComponentAction('otus:deal.order.items', 'getOrderItems', {
        	mode: 'class',
            data: {
                dealId: dealOrderItems.dealId
            }
        }).then(function(response) {
            if (response.status === 'success') {
                renderOrderItems(response.data.items ?? []);
                updateOrderTotal(response.data.totalSum ?? 0);
            }
        });
    }
    
    function renderOrderItems(items) {
        var container = BX('order-items-container');
        
        if (items.length === 0) {
            container.innerHTML = '<div class="ui-alert ui-alert-primary"><?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_NO_ITEMS'))?></div>';
            return;
        }

        var html = '<table class="ui-table">' +
            '<thead>' +
                '<tr>' +
                    '<th><?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_COLUMN_ITEM'))?></th>' +
                    '<th><?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_COLUMN_PRICE'))?></th>' +
                    '<th><?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_COLUMN_QUANTITY'))?></th>' +
                    '<th><?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_COLUMN_ADDITIVES'))?></th>' +
                    '<th><?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_COLUMN_TOTAL'))?></th>' +
                    (dealOrderItems.canEdit ? '<th><?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_COLUMN_ACTIONS'))?></th>' : '') +
                '</tr>' +
            '</thead>' +
            '<tbody>';

        items.forEach(function(item) {
            html += '<tr data-item-id="' + item.ID + '">' +
                '<td>' + BX.util.htmlspecialchars(item.MENU_ITEM_TITLE) + '</td>' +
                '<td>' + formatPrice(item.PRICE) + '</td>' +
                '<td>' + item.QUANTITY + '</td>' +
                '<td>' + renderAdditives(item.ADDITIVES) + '</td>' +
                '<td>' + formatPrice(item.ITEM_TOTAL) + '</td>';

            if (dealOrderItems.canEdit) {
                html += '<td>' +
                    '<button type="button" class="ui-btn ui-btn-light" onclick="editItem(' + item.ID + ')"><?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_EDIT_BUTTON'))?></button>' +
                    '<button type="button" class="ui-btn ui-btn-light-danger" onclick="deleteItem(' + item.ID + ')"><?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_DELETE_BUTTON'))?></button>' +
                '</td>';
            }

            html += '</tr>';
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function renderAdditives(additives) {
        if (!additives || additives.length === 0) {
            return '-';
        }

        return additives.map(function(additive) {
            return '<span class="additive-tag">' + BX.util.htmlspecialchars(additive.TITLE) + '</span>';
        }).join('');
    }

    function updateOrderTotal(totalSum) {
        var container = BX('order-total-container');
        container.innerHTML = '<div class="ui-alert ui-alert-success">' +
            '<strong><?=CUtil::JSEscape(Loc::getMessage('DEAL_ORDER_ITEMS_TOTAL_SUM'))?>:</strong> ' +
            formatPrice(totalSum) +
            '</div>';
    }

    function formatPrice(price) {
        return parseFloat(price).toFixed(2) + ' ₽';
    }

    function showNotification(message, type) {
        BX.UI.Notification.Center.notify({
            content: message,
            autoHideDelay: 3000,
            type: type
        });
    }
    
    function editItem(itemId) {
        // Реализация редактирования элемента
        showNotification('Функция редактирования в разработке', 'info');
    }
</script>