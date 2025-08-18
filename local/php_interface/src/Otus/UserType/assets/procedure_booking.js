document.addEventListener("DOMContentLoaded", () => {
    BX.ready(function () {
		document.querySelectorAll("a[data-procedure]").forEach(link => {
			link.addEventListener("click", function(e) {
				e.preventDefault();
				
				const procedureName = link.dataset.procedure;
				const procedureId = link.dataset.procedureId;
				const doctorId = link.dataset.doctorId;
				
		        const popup = BX.PopupWindowManager.create(`popup-procedure-booking-${procedureId}`, null, {
		            content: `
		            	<form id="form-procedure-booking-${procedureId}" class="procedure-booking-form" method="POST" action="/local/ajax/create_procedure_booking.php">
		            		<label>
		            			Дата записи: 
		            			<input class="input" type="text" name="DATETIME" onclick="BX.calendar({node: this, field: this, bTime: true});" />
		            		</label>
		            		
		            		<label>
		            			ФИО пацианта:
		            			<input class="input" type="text" name="NAME" />
		            		</label>
		            		
		            		<label>
		            			Контактный телефон:
		            			<input class="input" type="tel" name="PHONE" onclick="new BX.MaskedInput({ mask: '+7 999 999 99 99', input: this, placeholder: '_' })" />
		            		</label>
		            		
		            		<input type="hidden" name="DOCTOR" value="${doctorId}" />
		            		<input type="hidden" name="PROCEDURE" value="${procedureId}" />
		            		
		            		<span class="form-success" style="color: green;"></span>
		            		<span class="form-error" style="color: red;"></span>
		            	</form>
		            `,
		            width: 400, // ширина окна
		            height: 400, // высота окна
		            zIndex: 100, // z-index
		            closeIcon: {
		                // объект со стилями для иконки закрытия, при null - иконки не будет
		                opacity: 1
		            },
		            titleBar: `Запись на процедуру ${procedureName}`,
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
		                    text: 'Сохранить', // текст кнопки
		                    className: 'ui-btn ui-btn-success book-procedure', // доп. классы
		                    events: {
		                      click: function() {
		                          // Событие при клике на кнопку
		                          const form = document.querySelector(`#form-procedure-booking-${procedureId}`);
		                          
		                          if (form) {
		                          	const formError = form.querySelector(".form-error");
		                          	const formSuccess = form.querySelector(".form-success");
		                          	if (formError) {
					        			formError.innerText = "";
					        		}
					        		if (formSuccess) {
					        			formSuccess.innerText = "";
					        		}
		                          	
		                          	const url = form.action;
		                          	
		                          	const data = Array.from(form.querySelectorAll("input")).reduce((acc, input) => {
			                          	return {
			                          		...acc,
			                          		[input.name]: input.value,
			                          	}
		                        	}, {});
		                          	
	                          	    BX.ajax({
								        // файл на который идет запрос
								        url,
								        // метод запроса GET/POST
								        method: 'POST',
								        // параметры передаваемый запросом
								        data,
								        // ответ сервера лежит в data
								        onsuccess: function(data) {
								        	try {
								            	const jsonResponse = JSON.parse(data);
								            	
								            	// обработка ошибки
								            	if (!jsonResponse?.result) {
								            		if (formError) {
								            			formError.innerText = jsonResponse?.message ?? "При выполнении запроса произошла непредвиденная ошибка";
								            		}
								            	} else {
								            		if (formSuccess) {
								            			formSuccess.innerText = jsonResponse?.message ?? "Запись успешно создана!";
								            		}
								            	}
								        	} catch (ex) {
								        		if (formError) {
								        			formError.innerText = "При выполнении запроса произошла непредвиденная ошибка";
								        		}
								        	}
								        },
								        // выполнится в случае ошибки
								        onfailure: function (...data) {
								        	// кажется, он не показывает ответ бека ;(
								            console.log(data);
								        },
								    });
		                          }
		                      }
		                    }
		                }),
		            ],
		            events: {
		               onPopupShow: function() {
		                  // Событие при показе окна
		               },
		               onPopupClose: function() {
		                  // Событие при закрытии окна                
		               }
		            }
		        });
		        
				popup.show();	
			});
		});
    });
});