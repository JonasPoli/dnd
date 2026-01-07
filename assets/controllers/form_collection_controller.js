import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["collection"];
    static values = {
        index: Number,
        prototype: String,
    }

    add(event) {
        event.preventDefault();

        const prototype = this.prototypeValue;
        const index = this.indexValue;

        // Replace __name__ with the current index
        const newForm = prototype.replace(/__name__/g, index);

        // Increase index for next item
        this.indexValue++;

        // Create a temporary container to convert string to DOM
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = newForm;

        // Append the new element to the collection target
        // If the prototype returns a TR (like in ClassLevel), append the TR. 
        // If it returns a DIV, append the DIV.
        // We append the children of the temp div.
        const newElement = tempDiv.firstElementChild;

        if (this.hasCollectionTarget) {
            this.collectionTarget.appendChild(newElement);
        } else {
            // Fallback if no specific target (e.g. direct list)
            this.element.appendChild(newElement);
        }
    }

    remove(event) {
        event.preventDefault();

        const item = event.target.closest('[data-collection-item]');
        if (item) {
            item.remove();
        } else {
            // Try to find the closest parent div or tr if data attribute missing
            // This is a fail-safe, but best practice is to add data-collection-item to the row
            event.target.closest('tr, .flex')?.remove();
        }
    }
}
