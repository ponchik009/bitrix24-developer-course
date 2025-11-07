<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load(['ui.buttons', 'ui.forms', 'ui.alerts', 'ajax']);

?>
<div id="deal-order-items-<?=$arParams['DEAL_ID']?>" class="deal-order-items">
    <div class="deal-order-items-header">
        <h3><?=Loc::getMessage('DEAL_ORDER_ITEMS_TITLE')?></h3>
        <?php if ($arParams['CAN_EDIT']): ?>
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
            <strong><?=Loc::getMessage('DEAL_ORDER_ITEMS_TOTAL_SUM')?>: </strong>
             <?=number_format(0, 2)?> ₽
        </div>
    </div>
</div>

<script>
	let menuItems = [];
	let currentMenuItemAdditives = [];
	
	let selectedMenuItem = null;
	let selectedAdditives = [];
	
	const dealId = <?=CUtil::PhpToJSObject($arParams['DEAL_ID'])?>;
	const canEdit = <?=CUtil::PhpToJSObject($arParams['CAN_EDIT'])?>;
	
    // JavaScript функционал
    BX.ready(function() {
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
        BX('additives-container').innerHTML = '';
        BX('add-item-btn').disabled = false;
        
        selectedAdditives = [];
        selectedMenuItem = null;
    }

    function loadMenuItems() {
        BX.ajax.runComponentAction('otus:deal.order.items', 'getMenuItems', {
            mode: 'class',
            data: {
                dealId,
            }
        }).then(function(response) {
            var select = BX('menu-item-select');
            select.innerHTML = '<option value=""><?=Loc::getMessage('DEAL_ORDER_ITEMS_SELECT_MENU_ITEM')?></option>';
            
            menuItems = response.data;
            
            response.data.forEach(function(item) {
                var option = new Option(item.name, item.id);
                select.appendChild(option);
            });
        });
    }
    
    function onMenuItemChange(menuItemId) {
    	selectedAdditives = [];
    	selectedMenuItem = null;
    	
        if (!menuItemId) {
            BX('additives-container').innerHTML = '';
            return;
        }

        BX('additives-container').innerHTML = '<div class="additives-loading"></div>';
        
        var container = BX('additives-container');
        container.innerHTML = '';
        
    	var menuItemId = BX('menu-item-select').value;
    	selectedMenuItem = menuItems.find(el => el.id == menuItemId);
    	currentMenuItemAdditives = selectedMenuItem?.additives;
    	
        currentMenuItemAdditives.forEach(function(additive) {
            var wrapper = BX.create('label', {
                props: {className: 'additive-checkbox-wrapper'},
                children: [
                    BX.create('input', {
                        props: {
                            type: 'checkbox',
                            value: additive.id,
                            className: 'additive-checkbox',
                        }
                    }),
                    BX.create('span', {
                        props: {className: 'ui-ctl-label-text'},
                        text: additive.name + ' (+' + additive.price + ' ₽)'
                    })
                ]
            });
            
            wrapper.addEventListener("change", () => {
        		if (!selectedAdditives.some(el => el.id == additive.id)) {
        			selectedAdditives.push(additive);
        		} else {
        			selectedAdditives = selectedAdditives.filter(el => el.id !== additive.id);
        		}
            });

            container.appendChild(wrapper);
        });
    }

    function addOrderItem() {
        var menuItemId = selectedMenuItem?.id;
        var quantity = parseInt(BX('item-quantity').value);
        var price = selectedMenuItem?.price;

        if (!menuItemId) {
            showNotification('<?=Loc::getMessage('DEAL_ORDER_ITEMS_SELECT_MENU_ITEM_ERROR')?>', 'error');
            return;
        }

        if (!quantity || quantity < 1) {
            showNotification('<?=Loc::getMessage('DEAL_ORDER_ITEMS_QUANTITY_ERROR')?>', 'error');
            return;
        }

        // Блокируем кнопку на время выполнения
        var addButton = BX('add-item-btn');
        addButton.disabled = true;
        addButton.textContent = '<?=Loc::getMessage('DEAL_ORDER_ITEMS_ADDING')?>';
        
        BX.ajax.runComponentAction('otus:deal.order.items', 'addOrderItem', {
        	mode: 'class',
            data: {
                dealId,
                menuItemId: menuItemId,
                quantity: quantity,
                price: price,
                additives: selectedAdditives?.length ? selectedAdditives : null,
            }
        }).then(function(response) {
            if (response.status === 'success') {
                showNotification('Элемент успешно добавлен', 'success');
                hideAddItemForm();
                updateOrderItemsList();
                resetAddItemForm();
            } else {
                showNotification(response.errors[0].message, 'error');
            }
            addButton.disabled = false;
            addButton.textContent = '<?=Loc::getMessage('DEAL_ORDER_ITEMS_ADD_BUTTON')?>';
        }).catch(function(response) {
            showNotification(response.errors[0].message, 'error');
            addButton.disabled = false;
            addButton.textContent = '<?=Loc::getMessage('DEAL_ORDER_ITEMS_ADD_BUTTON')?>';
        });
    }

    function deleteItem(itemId) {
        if (!confirm('<?=Loc::getMessage('DEAL_ORDER_ITEMS_DELETE_CONFIRM')?>')) {
            return;
        }

        BX.ajax.runComponentAction('otus:deal.order.items', 'deleteOrderItem', {
        	mode: 'class',
            data: {
                dealId,
                itemId: itemId
            }
        }).then(function(response) {
            if (response.status === 'success') {
                showNotification('Элемент успешно удален', 'success');
                updateOrderItemsList();
            } else {
                showNotification(response.errors[0].message, 'error');
            }
        });
    }
    
    
    function updateOrderItemsList() {
        // Обновляем список элементов через AJAX
        BX.ajax.runComponentAction('otus:deal.order.items', 'getOrderItems', {
        	mode: 'class',
            data: {
                dealId,
            }
        }).then(function(response) {
            if (response.status === 'success') {
                renderOrderItems(response.data ?? []);
                // updateOrderTotal(response.data.totalSum ?? 0);
            }
        });
    }
    
    function renderOrderItems(items) {
        var container = BX('order-items-container');
        
        if (items.length === 0) {
            container.innerHTML = '<div class="ui-alert ui-alert-primary"><?=Loc::getMessage('DEAL_ORDER_ITEMS_NO_ITEMS')?></div>';
            return;
        }

        var html = '<table class="ui-table">' +
            '<thead>' +
                '<tr>' +
                    '<th><?=Loc::getMessage('DEAL_ORDER_ITEMS_COLUMN_ITEM')?></th>' +
                    '<th><?=Loc::getMessage('DEAL_ORDER_ITEMS_COLUMN_PRICE')?></th>' +
                    '<th><?=Loc::getMessage('DEAL_ORDER_ITEMS_COLUMN_QUANTITY')?></th>' +
                    '<th><?=Loc::getMessage('DEAL_ORDER_ITEMS_COLUMN_ADDITIVES')?></th>' +
                    '<th><?=Loc::getMessage('DEAL_ORDER_ITEMS_COLUMN_TOTAL')?></th>' +
                    (canEdit ? '<th><?=Loc::getMessage('DEAL_ORDER_ITEMS_COLUMN_ACTIONS')?></th>' : '') +
                '</tr>' +
            '</thead>' +
            '<tbody>';

        items.forEach(function(item) {
            html += '<tr data-item-id="' + item.ID + '">' +
                '<td>' + BX.util.htmlspecialchars(item.name) + '</td>' +
                '<td>' + formatPrice(item.price) + '</td>' +
                '<td>' + item.quantity + '</td>' +
                '<td>' + renderAdditives(item.additives) + '</td>' +
                '<td>' + formatPrice(item.ITEM_TOTAL) + '</td>';

            if (canEdit) {
                html += '<td>' +
                    '<button type="button" class="ui-btn ui-btn-light-danger" onclick="deleteItem(' + item.id + ')"><?=Loc::getMessage('DEAL_ORDER_ITEMS_DELETE_BUTTON')?></button>' +
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
            return '<span class="additive-tag">' + BX.util.htmlspecialchars(additive.name) + '</span>';
        }).join('');
    }

    // function updateOrderTotal(totalSum) {
    //     var container = BX('order-total-container');
    //     container.innerHTML = '<div class="ui-alert ui-alert-success">' +
    //         '<strong><?=Loc::getMessage('DEAL_ORDER_ITEMS_TOTAL_SUM')?>:</strong> ' +
    //         formatPrice(totalSum) +
    //         '</div>';
    // }

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
</script>