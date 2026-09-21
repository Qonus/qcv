import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.css';
// import 'tom-select/dist/css/tom-select.default.min.css';

export default class extends Controller {
    static values = {
        options: {type: Object, default: {}},
        url: String,
    }

    async connect() {
        this.component = await getComponent(this.element.closest('[data-controller~="live"]'));
        const config = {
            plugins: [''],
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            sortField: 'name',
            optgroupField: 'category',
            preload: true,
            onChange: (value) => {
                this.component.action('selectAttribute', { id: value });
            }
        };

        if (this.hasUrlValue) {
            config.firstUrl = (query) => `${this.urlValue}?query=${encodeURIComponent(query)}`;
            config.load = (query, callback) => {
                const url = config.firstUrl(query);
                fetch(url)
                    .then(response => response.json())
                    .then(json => {
                        console.log(json);
                        callback(json);
                    })
                    .catch(() => {
                        callback();
                    });
            };
        }

        this.select = new TomSelect(this.element, config);
    }

    disconnect() {
        if (this.select) {
            this.select.destroy();
        }
    }
}
