import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

export default class extends Controller {
    static values = {
        options: {type: Object, default: {}},
        initial: Array,
        searchUrl: String,
        createUrl: String,
    }

    async connect() {
        const initialItems = (this.hasInitialValue ? this.initialValue : []).map(
            item => String(item['id'])
        );
        const config = {
            plugins: ['remove_button'],
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            sortField: 'name',
            // TODO: sort by score in future
            // sortField: 'score',
            options: this.hasInitialValue ? this.initialValue : [],
            items: initialItems,
            preload: true,
            persist: false,
            create: this.handleCreate.bind(this),
            render: {
                option_create: (data, escape) => {
                    return `<div class="create">+ <strong>${escape(data.input)}</strong>...</div>`;
                }
            }
        };

        if (this.hasSearchUrlValue) {
            config.load = (query, callback) => {
                const url = new URL(this.searchUrlValue, window.location.origin);
                url.searchParams.set('query', query);
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        // console.log(data);
                        callback(data);
                    })
                    .catch(() => {
                        callback();
                    });
            };
        }
        this.select = new TomSelect(this.element, config);
    }
    async handleCreate(input, callback) {
        fetch(this.createUrlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ name: input })
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to create option');
            return response.json();
        })
        .then(data => {
            callback({
                id: data['id'],
                name: data['name']
            });
        })
        .catch(error => {
            console.error(error);
            callback(false);
        });
    }

    disconnect() {
        if (this.select) {
            this.select.destroy();
        }
    }
}
