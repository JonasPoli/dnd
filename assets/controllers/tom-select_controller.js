import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';

export default class extends Controller {
    connect() {
        // Initialize TomSelect on this element
        new TomSelect(this.element, {
            plugins: ['remove_button'],
            create: false,
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            placeholder: this.element.getAttribute('placeholder') || 'Selecione...'
        });
    }
}
