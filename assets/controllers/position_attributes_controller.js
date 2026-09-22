import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';

export default class extends Controller {
    static targets = ["input"];

    connect() {
        this.states = {};

        this.select = new TomSelect(this.element, {
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            plugins: ['remove_button'],
            // TODO: later optional/required toggle
            // render: {
            //     item: (data, escape) => {
            //         const currentState = this.states[data.id] || 'optional';
            //         const isRequired = currentState === 'required';
                    
            //         return `<div class="item d-flex align-items-center gap-2 pe-1">
            //             <span>${escape(data.name)}</span>
            //             <button type="button" 
            //                     class="btn btn-sm ${isRequired ? 'btn-danger' : 'btn-outline-secondary'} py-0 px-2 state-toggle-btn" 
            //                     data-id="${data.id}"
            //                     style="z-index:5">
            //                 ${isRequired ? 'Required' : 'Optional'}
            //             </button>
            //         </div>`;
            //     },
            // },
            // onItemAdd: (value) => {
            //     if (!this.states[value]) {
            //         this.states[value] = 'optional';
            //     }
            // },
            // onItemRemove: (value) => {
            //     delete this.states[value];
            // }
        });

        // Event listener on the Tom Select wrapper to capture toggle button clicks
        this.select.wrapper.addEventListener('click', (event) => {
            const toggleBtn = event.target.closest('.state-toggle-btn');
            if (!toggleBtn) return;

            // Stop Tom Select from triggering drop-down or item removal on click
            event.stopPropagation();
            event.preventDefault();

            const attrId = toggleBtn.dataset.id;
            this.toggleState(attrId);
        });
    }

    toggleState(id) {
        // Toggle state between 'optional' and 'required'
        this.states[id] = this.states[id] === 'required' ? 'optional' : 'required';

        // Refresh Tom Select items to trigger re-rendering with new state badge
        this.select.refreshItems();

        // Dispatch a custom event or update a hidden form input with this.states JSON
        this.element.dispatchEvent(new CustomEvent('attributes:changed', {
            detail: { states: this.states },
            bubbles: true
        }));
    }

    disconnect() {
        if (this.select) {
            this.select.destroy();
        }
    }
}