
(function () {
	init();
	
	/**
	 * Метод инициализирует работу модуля:
	 * 1. Подписывается на ивент ontimemanwindowopen
	 * 2. Отключает бинды кнопки старта / продолжения рабочего дня
	 * 3. Открывает модальное окно подтверждения для старта рабочего дня
 	*/
	function init() {
		function setupStartButton() {
			const popup = BX.PopupWindowManager.getPopupById("timeman_main");
			
			if (!popup) {
				return;
			}
			
			const container = popup.contentContainer;
			const startButton = container.querySelector(".ui-btn-icon-start");
			
			// сценарий, когда рабочий день начат
			if (!startButton) {
				console.log("Не нашел кнопку для старта рабочего дня");
				return;
			}
			
			// сценарий, когда рабочий день не начат
			const startButtonCopy = startButton.cloneNode(true);
			startButton.replaceWith(startButtonCopy);

			BX.unbindAll(startButtonCopy);
			BX.bind(startButtonCopy, 'click', function(e) {
		        showConfirmationModal(
		        	() => {
		        		startButtonCopy.replaceWith(startButton);
		        		startButton.click();
		        	}
		    	);
		    });
		}
		
		BX.addCustomEvent("ontimemanwindowopen", () => {
			setupStartButton();
		});
		
		BX.addCustomEvent('onAjaxSuccessFinish', function(e) {
			if (e && e.url.startsWith("/bitrix/tools/timeman.php?action=close")) {
				setupStartButton();
			}
		});
	}
	
	/**
	 * Метод отвечает за обработку модального окна подтверждения старта рабочего дня:
	 * 1. Рендерит окно подтверждения
	 * 2. Вызывает метод callback при подтверждении
 	*/
	function showConfirmationModal(callback) {
        const popup = BX.PopupWindowManager.create(`daystart-confirm`, null, {
            content: `
            	<span>Подтвердите действие</span>
            `,
            titleBar: `Управление рабочим днем`,
            width: 400, // ширина окна
            height: 400, // высота окна
            zIndex: 100, // z-index
            closeIcon: {
                // объект со стилями для иконки закрытия, при null - иконки не будет
                opacity: 1
            },
            closeByEsc: true, // закрытие окна по esc
            darkMode: false, // окно будет светлым или темным
            autoHide: true, // закрытие при клике вне окна
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
                      	callback();
                      	
                      	BX.PopupWindowManager.getCurrentPopup()?.close();
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
	}
})();
