<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load(['ui.buttons', 'ui.forms', 'ui.alerts', 'ajax']);

?>

<!-- Список элементов заказа -->
<div class="remainders-items-list" id="remainders-items-container">
    
</div>

<script>
    // JavaScript функционал
    BX.ready(function() {
        // Рендер остатков
        loadRemainders();
    });

    async function changeRemainders() {
        const container = BX("remainders-items-container");

        const remainders = [];
        container.querySelectorAll("tbody tr").forEach(row => {
            const itemId = row.dataset.itemId;
            const remainder = row.querySelector("input").value;

            remainders.push({
                amount: remainder,
                productId: itemId,
            });            
        });

        const promise = BX.ajax.runComponentAction('otus:branch.remainder.counter', 'changeRemainders', {
            mode: 'class',
            data: {
                items: remainders,
            }
        });

        promise.then(function(response) {
            if (response.status === 'success') {
                showNotification('Остатки успешно обновлены', 'success');
            } else {
                showNotification(response.errors[0].message, 'error');
            }
        }).catch(function(response) {
            console.log(response);
            showNotification(response.errors[0].message, 'error');
        })

        return promise;
    }
    
    function loadRemainders() {
        // Обновляем список элементов через AJAX
        BX.ajax.runComponentAction('otus:branch.remainder.counter', 'getRemainders', {
        	mode: 'class',
            data: {}
        }).then(function(response) {
            if (response.status === 'success') {
                renderRemainders(response.data ?? []);
            } else {
                showNotification(response.errors[0].message, 'error');
            }
        }).catch(function(response) {
        	console.log(response);
        	showNotification(response.errors[0].message, 'error');
        });
    }
    
    function renderRemainders(items) {
        var container = BX('remainders-items-container');
        
        if (items.length === 0) {
            container.innerHTML = '<div class="ui-alert ui-alert-primary">Элементов не найдено</div>';
            return;
        }

        var html = '<table class="ui-table">' +
            '<thead>' +
                '<tr>' +
                    '<th>№</th>' +
                    '<th>Продукт</th>' +
                    '<th>Остаток</th>' +
                    '<th>Реальное значение</th>' +
                '</tr>' +
            '</thead>' +
            '<tbody>';

        items.forEach(function(item, index) {
            html += '<tr data-item-id="' + item.product.id + '">' +
                '<td>' + (index + 1) + '</td>' +
                '<td>' + BX.util.htmlspecialchars(item.product.name) + '</td>' +
                '<td>' + item.remainder + ' ' + item.product.measurmentUnit + '</td>' +
                '<td><input class="ui-ctl-element" type="number" min="1" value="' + item.remainder + '" name="REMAINDER" /> ' + item.product.measurmentUnit + '</td>';

            html += '</tr>';
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function showNotification(message, type) {
        BX.UI.Notification.Center.notify({
            content: message,
            autoHideDelay: 3000,
            type: type
        });
    }
</script>