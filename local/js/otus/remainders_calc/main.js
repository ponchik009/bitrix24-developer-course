
(function () {
	init();
	
	/**
	 * Метод инициализирует работу модуля:
	 * 1. Подписывается на ивент ontimemanwindowopen
	 * 2. Ловит нажатие на кнопку завершения рабочего дня
	 * 3. Открывает модальное окно для сведения остатков
	 * 4. Завершает рабочий день после сведения остатков
 	*/
	function init() {
		function setupEndButton() {
			const popup = BX.PopupWindowManager.getPopupById("timeman_main");
			
			if (!popup) {
				return;
			}
			
			const container = popup.contentContainer;
			const endButton = container.querySelector(".ui-btn-icon-stop");
			
			// сценарий, когда рабочий день начат
			if (!endButton) {
				console.log("Не нашел кнопку для завершения рабочего дня");
				return;
			}
			
			// сценарий, когда рабочий день не начат
			const endButtonCopy = endButton.cloneNode(true);
			endButton.replaceWith(endButtonCopy);

			BX.unbindAll(endButtonCopy);
			BX.bind(endButtonCopy, 'click', function(e) {
		        runRemainderComponent(
		        	() => {
		        		endButtonCopy.replaceWith(endButton);
		        		endButton.click();
		        	}
		    	);
		    });
		}
		
		BX.addCustomEvent("ontimemanwindowopen", () => {
			setupEndButton();
		});
		
		BX.addCustomEvent('onAjaxSuccessFinish', function(e) {
			if (
				e && 
				(e.url.startsWith("/bitrix/tools/timeman.php?action=open") || e.url.startsWith("/bitrix/tools/timeman.php?action=reopen"))
			) {
				setupEndButton();
			}
		});
	}
	
	/**
	 * Метод отвечает за обработку модального окна при завершении рабочего дня:
	 * 1. Рендерит окно сведения остатков
	 * 2. Вызывает метод callback при подтверждении
 	*/
	function runRemainderComponent(callback) {
		BX.ajax({
		    url: '/local/components/otus/branch.remainder.counter/lazyload.ajax.php?site=' + BX.message('SITE_ID') + '&sessid=' + BX.bitrix_sessid(),
		    data: {},
		    method: 'POST',
		    dataType: 'html',
		    onsuccess: function(html) {
                const popup = BX.PopupWindowManager.create(`remainders-calculate`, null, {
		            content: html,
		            titleBar: `Сведение остатков`,
		            width: 600, // ширина окна
		            height: 600, // высота окна
		            zIndex: 100, // z-index
		            closeIcon: {
		                // объект со стилями для иконки закрытия, при null - иконки не будет
		                opacity: 1
		            },
		            closeByEsc: true, // закрытие окна по esc
		            darkMode: false, // окно будет светлым или темным
		            autoHide: false, // закрытие при клике вне окна
		            draggable: false, // можно двигать или нет
		            resizable: false, // можно ресайзить
		            min_height: 100, // минимальная высота окна
		            min_width: 100, // минимальная ширина окна
		            lightShadow: true, // использовать светлую тень у окна
		            angle: false, // появится уголок
		            overlay: {
		                // объект со стилями фона
		                backgroundColor: 'black',
		                opacity: 500
		            }, 
		            buttons: [
		                new BX.PopupWindowButton({
		                    text: 'Подтвердить', // текст кнопки
		                    className: 'ui-btn ui-btn-success', // доп. классы
		                    events: {
		                      click: function(...args) {
								changeRemainders().then(() => {
									callback();
									
									BX.PopupWindowManager.getCurrentPopup()?.close();
								});
		                      }
		                    }
		                }),
		                new BX.PopupWindowButton({
		                    text: 'Отменить', // текст кнопки
		                    className: 'ui-btn ui-btn-hint', // доп. классы
		                    events: {
		                      click: function() {
		                          BX.PopupWindowManager.getCurrentPopup()?.close();
		                      }
		                    }
		                }),
		            ],
		        });
		        
				popup.show();
		    },
		    onfailure: function() {
		        console.error('Ошибка загрузки компонента');
		    }
		});
	}
})();