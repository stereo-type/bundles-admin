import * as bootstrap from "bootstrap";
import Sortable from 'sortablejs';
import tableDragger from 'table-dragger';

class Table {

    modalId = null;
    constructor() {
        this.SELECTORS = {
            SUBMITTERS: 'input[type=submit], button[type=submit]',
            TABLE: 'table.sonata-ba-list',
            ERROR_MODAL: 'errorModal',
            FORM_FILTERS: 'form.sonata-filter-form',
            SETTINGS_DRAGGABLE: '#drag-interface',
        };
    }

    submitters() {
        return document.querySelectorAll(this.SELECTORS.SUBMITTERS);
    }

    disableSubmitters() {
        const subs = this.submitters();
        for (let i = 0; i < subs.length; i++) {
            subs[i].disabled = true;
        }
    }

    enableSubmitters() {
        const subs = this.submitters();
        for (let i = 0; i < subs.length; i++) {
            subs[i].disabled = false;
        }
    }

    /**
     * Метод для корректировки урла, использовать только его.
     *
     * @param {string} url - Описание параметра.
     * @returns {URL} Описание возвращаемого значения.
     */
    fixUrl(url) {
        /**Убираем кнопку селекта
         * vendor/sonata-project/admin-bundle/src/Admin/AbstractAdmin.php : buildList()                     * */
        const urlWithSelectParam = url + (url.includes('?') ? '&' : '?') + 'select=false';
        return new URL(urlWithSelectParam, window.location.origin);
    }

    showLoader() {
        const tableContainer = document.querySelector(this.SELECTORS.TABLE);
        // const tableContainer = document.querySelector('body');
        const loader = document.createElement('div');

        if (!tableContainer) {
            return;
        }
        loader.className = 'loader-overlay';
        loader.innerHTML = ` 
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"> 
                    <span class="visually-hidden">Загрузка...</span> 
                </div> 
                <div class="mt-2 text-primary">Загрузка данных...</div> 
            `;
        loader.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(190, 190, 190, 0.7); display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 9999;';
        tableContainer.appendChild(loader);
        return loader;
    }

    showErrorModal(error) {

        // Создаем модальное окно с ошибкой
        const modalHtml = ` 
                        <div class="modal" id="errorModal" tabindex="-1" aria-hidden="true"> 
                            <div class="modal-dialog"> 
                                <div class="modal-content"> 
                                    <div class="modal-header bg-danger text-white"> 
                                        <h5 class="modal-title"> 
                                            <i class="fas fa-exclamation-triangle me-2"></i> 
                                            Ошибка 
                                        </h5> 
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button> 
                                    </div> 
                                    <div class="modal-body"> 
                                        <p>При загрузке данных произошла ошибка:</p> 
                                        <p class="text-danger">${error.message}</p> 
                                    </div> 
                                    <div class="modal-footer"> 
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button> 
                                    </div> 
                                </div> 
                            </div> 
                        </div> 
                    `;

        // Добавляем модальное окно в DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Показываем модальное окно
        const errorModal = new bootstrap.Modal(document.getElementById(this.SELECTORS.ERROR_MODAL));
        errorModal.show();

        // Удаляем модальное окно после закрытия
        errorModal.addEventListener('hidden.bs.modal', function () {
            this.remove();
        });
    }

    loadListContent(url) {
        const context = this;
        const fixedUrl = this.fixUrl(url);
        // Показываем лоадер
        const loader = this.showLoader();
        this.disableSubmitters();
        fetch(fixedUrl,
            {headers: {'X-Requested-With': 'XMLHttpRequest'}}
        )
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const wrap = document.querySelector('.sonata-list-wrap');
                const contentWrap = wrap.querySelector('.box-primary')

                // Обновляем таблицу
                const newTable = doc.querySelector('.sonata-ba-list');
                const oldTable = wrap.querySelector('.sonata-ba-list');
                const body = contentWrap.querySelector('.box-body');
                const newBody = doc.querySelector('.sonata-list-wrap .box-body');
                if (newTable && oldTable) {
                    oldTable.replaceWith(newTable);
                } else if (oldTable && !newTable) {
                    body.replaceWith(newBody);
                } else if (!oldTable && newTable) {
                    body.replaceWith(newBody);
                }

                // Обновляем пагинацию
                const newPagination = doc.querySelector('.pagination');
                const oldPagination = wrap.querySelector('.pagination');
                const footer = contentWrap.querySelector('.box-footer')
                const newFooter = doc.querySelector('.sonata-list-wrap .box-footer')
                if (newPagination && oldPagination) {
                    oldPagination.replaceWith(newPagination);
                } else if (oldPagination && !newPagination) {
                    footer.remove();
                } else if (!oldPagination && newPagination) {
                    if (footer) {
                        footer.replaceWith(newFooter)
                    } else {
                        contentWrap.appendChild(newFooter);
                    }
                }

                // Обновляем блок с информацией о записях и экспортными кнопками
                const activePage = document.querySelector('.pagination .active a');
                if (activePage) {
                    const pageInfoBlock = document.querySelector('section.content .pull-right');
                    if (pageInfoBlock) {
                        // Извлекаем текст из блока и ищем информацию о текущей странице и общее количество страниц
                        const pageInfoText = pageInfoBlock.textContent;
                        const pageInfoMatch = pageInfoText.match(/(\d+)\s*\/\s*(\d+)/); // Находим "9 / 11" в строке

                        // Проверяем, что данные найдены
                        if (pageInfoMatch) {
                            const currentPage = activePage.textContent; // Текущая страница из пагинации
                            const totalPages = pageInfoMatch[2]; // Общее количество страниц из строки

                            // Извлекаем общее количество записей
                            const totalRecordsMatch = pageInfoText.match(/Всего\s+(\d+)\s+записей/);
                            const totalRecords = totalRecordsMatch ? totalRecordsMatch[1] : '?';

                            // Находим нужный блок, который нужно обновить
                            const pageTextBlock = pageInfoBlock.childNodes[2]; // Это тот блок, где "9 / 11"
                            if (pageTextBlock) {
                                pageTextBlock.textContent = ` \u00A0-\u00A0\ ${currentPage} / ${totalPages} \u00A0-\u00A0\ Всего ${totalRecords} записей \u00A0-\u00A0\ `;
                            }
                        }
                    }
                }
                history.pushState(null, '', fixedUrl.toString());
                this.attachPagingListeners(); // Повторно навешиваем обработчики
            })
            .catch(error => {
                this.showErrorModal(error);
                console.error('Ошибка загрузки:', error);
            })
            .finally(() => {
                // Убираем лоадер
                loader?.remove();
                // Включаем кнопки сабмита
                context.enableSubmitters();
                if(context.modalId) {
                    context.initDraggable(this.modalId);
                }
            });
    }

    attachPagingListeners() {
        const context = this;
        // Remove existing listeners before attaching new ones
        const paginationLinks = document.querySelectorAll('.pagination a, th a');
        paginationLinks.forEach(link => {
            link.removeEventListener('click', handlePaginationClick);
        });

        // Add new listeners
        paginationLinks.forEach(link => {
            link.addEventListener('click', handlePaginationClick);
        });


        function handlePaginationClick(e) {
            e.preventDefault();
            context.loadListContent(this.href);
        }

    }

    attachFilterFormListeners() {
        const context = this;
        const formFilters = document.querySelector(this.SELECTORS.FORM_FILTERS);
        if (!formFilters) {
            console.error('Form not found');
            return;
        }
        formFilters.removeEventListener('submit', handleFilterFormSubmit);

        formFilters.addEventListener('submit', handleFilterFormSubmit);

        /**Соната при стирании значения инпута убирает у инпута name, из-за чего даже если еще раз ввести
         * значение в инпут, он не сработает, по этому модифицируем это поведение*/
        Array.from(formFilters.elements).forEach(element => {
            if (element.type !== 'submit') {
                setupBackupName(element);
                element.addEventListener('input', function () {
                    restoreFieldName(element);
                });
            }
        });

        function handleFilterFormSubmit(evt) {
            evt.preventDefault();
            evt.stopImmediatePropagation()

            // Восстанавливаем атрибуты `name` перед отправкой формы
            Array.from(formFilters.elements).forEach(element => {
                restoreFieldName(element);
            });

            const formData = new FormData(formFilters);
            // Преобразуем данные в объект
            const filters = {};
            for (const [key, value] of formData.entries()) {
                filters[key] = value;
            }

            // Формируем строку GET-параметров
            const queryString = new URLSearchParams(filters).toString();
            // Получаем базовый URL формы
            const baseUrl = formFilters.action;
            const url = baseUrl + (baseUrl.includes('?') ? '&' : '?') + queryString;
            context.loadListContent(url, formData);
        }

        function setupBackupName(element) {
            // Если у элемента есть `name`, сохраняем его в `backup_name`
            if (element.name && !element.getAttribute('backup_name')) {
                element.setAttribute('backup_name', element.name);
            }
        }

        function restoreFieldName(element) {
            // Если `name` отсутствует, но есть `backup_name`, восстанавливаем `name`
            if (!element.name && element.getAttribute('backup_name') && element.value) {
                element.setAttribute('name', element.getAttribute('backup_name'));
            }
        }
    }

    initSettingsModal(modalId) {
        const context = this;
        let modalTrigger = document.getElementById(modalId);
        if (!modalTrigger) {
            console.error('no modal trigger found');
            return;
        }

        modalTrigger.addEventListener("click", function (evt) {
            evt.preventDefault();

            let targetId = this.getAttribute('data-bs-target');
            if (targetId.startsWith('#')) {
                targetId = targetId.substring(1);
            }
            const modalElement = document.getElementById(targetId);
            if (!modalElement) {
                console.error('modalElement not found');
                return;
            }
            if (modalElement) {
                // // Manually trigger the display of the modal
                const modal = bootstrap.Modal.getInstance(modalElement);
                modal.show();

                const modalBody = modalElement.querySelector('.modal-body');
                const getData = modalTrigger.getAttribute('href');
                const action = modalTrigger.getAttribute('data-action');
                const tableName = modalTrigger.getAttribute('data-table-name');

                modalBody.innerHTML = `
        <form action="${action}" method="POST" class="px-4 py-3 form-update" id="columns_accept-update">
          <input type="hidden" name="columns_order" id="columns-order" />
          <input type="hidden" name="columns_hidden" id="columns-hidden" />
          <input type="hidden" name="table_name" value="${tableName}" />
          <ul id="drag-interface">
          </ul>
          <button type="submit" class="btn btn-primary d-flex mx-auto mt-2 mb-2">
            Принять
          </button>
        </form>
        `;

                // Загрузка данных через AJAX
                const formData = new FormData();
                formData.append('table_name', tableName);

                fetch(getData, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            const ul = modalBody.querySelector(context.SELECTORS.SETTINGS_DRAGGABLE);
                            const orderFields = data.data.order;
                            const hiddenFields = data.data.hidden || [];

                            orderFields.forEach(fieldName => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item d-flex align-items-center';

                                const checkbox = document.createElement('input');
                                checkbox.type = 'checkbox';
                                checkbox.className = 'form-check-input me-2';
                                checkbox.value = fieldName;
                                checkbox.checked = !hiddenFields.includes(fieldName);

                                checkbox.addEventListener('change', updateHiddenFields);

                                const label = document.createElement('label');
                                label.className = 'form-check-label drag-label';
                                label.textContent = fieldName;

                                const icon = document.createElement('span');
                                icon.className = 'drag-interface-icon';

                                li.appendChild(checkbox);
                                li.appendChild(icon);
                                li.appendChild(label);
                                ul.appendChild(li);
                            });

                            updateHiddenFields();
                            initSortable();
                        } else {
                            console.error('error get data');
                        }
                    });

                function updateHiddenFields() {
                    const checkboxes = modalBody.querySelectorAll('#drag-interface input[type="checkbox"]');
                    const columnsOrder = [];
                    const columnsHidden = [];

                    checkboxes.forEach(checkbox => {
                        columnsOrder.push(checkbox.value);
                        if (!checkbox.checked) {
                            columnsHidden.push(checkbox.value);
                        }
                    });

                    modalBody.querySelector('#columns-order').value = columnsOrder.join(',');
                    modalBody.querySelector('#columns-hidden').value = columnsHidden.join(',');
                }

                function initSortable() {
                    let dragItems = document.querySelector(context.SELECTORS.SETTINGS_DRAGGABLE);
                    if (dragItems) {
                        new Sortable(dragItems, {
                            animation: 150,
                            onEnd: function (evt) {
                                updateHiddenFields()
                            }
                        });
                    }
                }

                // Перехват отправки формы
                const form = modalBody.querySelector('form');
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    context.saveSettings(action, new FormData(this), modalElement);
                });
            }
        })
    }

    saveSettings(action, formData, modalElement) {
        const context = this;

        // for (let [key, value] of formData.entries()) {
        //     console.log(key, value);
        // }
        return fetch(action, {
            method: 'POST',
            body: formData
        })

            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    modal.hide();
                    // ассинхронная загрузка данных
                    context.loadListContent(window.location.href);
                    // window.location.reload(); // Перезагрузка страницы при успехе
                } else {
                    alert(result.message || 'Произошла ошибка при сохранении');
                }
            })
            .catch(error => {
                alert('Произошла ошибка при отправке данных: ' + error);
            });
    }

    initDraggable(modalId) {
        const tables = document.querySelectorAll(this.SELECTORS.TABLE);
        let modalTrigger = document.getElementById(modalId);
        /**из модалки берем урл сохранения, она всегда маст хэв быть в разметке*/
        if (!modalTrigger) {
            console.error('no modal trigger found initDraggable');
            return;
        }

        const action = modalTrigger.getAttribute('data-action');
        const tableName = modalTrigger.getAttribute('data-table-name');

        const parts = action.split('/');

        if (parts[parts.length - 1] === 'setSettings') {

            parts[parts.length - 1] = 'orderChanged';
            const newUrl = parts.join('/');
            tables.forEach(table => {
                if (!table.classList.contains('sindu_origin_table')) {
                    const dragger = tableDragger(table, {mode: 'column', animation: 150});
                    dragger.on('drop', async function (oldIndex, newIndex, el, mode) {
                        if (oldIndex !== newIndex) {
                            const data = {
                                old_index: oldIndex,
                                new_index: newIndex,
                                table_name: tableName
                            }
                            const formData = new FormData();
                            for (const [key, value] of Object.entries(data)) {
                                formData.append(key, value);
                            }

                            fetch(newUrl, {
                                method: 'POST',
                                body: formData
                            })
                        }
                    })
                }
            })
        }

    }

    init(modalId) {
        this.modalId = modalId;
        this.attachFilterFormListeners();
        this.attachPagingListeners();
        this.initSettingsModal(modalId);
        this.initDraggable(modalId);
    }
}

const tableInstance = new Table();

export const table = {
    loadListContent: tableInstance.loadListContent.bind(tableInstance),
    init: tableInstance.init.bind(tableInstance)
};

