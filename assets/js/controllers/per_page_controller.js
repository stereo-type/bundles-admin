import {Controller} from '@hotwired/stimulus';
// import $ from 'jquery';
import {table} from '../component/table';

export default class extends Controller {
    connect() {
        const useSelect2 = this.element.dataset.adminPerPageUseSelect2Value === 'true';
        if (useSelect2) {
            setTimeout(() => this.initializeSelect2(), 200);
        } else {
            this.initializeStandardSelect();
        }
    }

    initializeSelect2() {
        const selectElement = this.element;
        $(selectElement).on('select2:select', (event) => {
            this.reload(event);
        });
    }

    initializeStandardSelect() {
        const selectElement = this.element;
        selectElement.addEventListener('change', (event) => {
            this.reload(event);
        });
    }

    reload(event) {
        let url;
        if (event.type === 'select2:select') {
            // Для Select2 получаем значение из event.params.data.id
            url = event.params.data.id;
        } else if (event.type === 'change') {
            // Для стандартного <select> получаем значение напрямую из элемента
            url = event.target.value;
        }
        table.loadListContent(url);

    }


}
