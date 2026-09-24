import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.css';
// import 'tom-select/dist/css/tom-select.default.min.css';

export default class extends Controller {
    static values = {
        options: {type: Object, default: {}},
        url: String,
        default: {type: Object, default: null},
    }

    async connect() {
        this.component = await getComponent(this.element.closest('[data-controller~="live"]'));
        const config = {
            plugins: ['dropdown_input'],
            valueField: 'id',
            labelField: 'name',
            searchField: ['name', 'category'],
            sortField: 'name',
            optgroupField: 'category',
            optgroupLabelField: 'name',
            allowEmptyOption: false,
            preload: true,
            onChange: (value) => {
                this.component.action('selectAttribute', { id: value ?? null });
            },
        };

        if (this.hasUrlValue) {
            config.firstUrl = (query) => `${this.urlValue}?query=${encodeURIComponent(query)}`;
            config.load = (query, callback) => {
                const url = config.firstUrl(query);
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        const uniqueGroups = [...new Set(data.map(item => item.category))];
                        uniqueGroups.forEach(groupName => {
                            if (groupName && !this.select.optgroups[groupName]) {
                                this.select.addOptionGroup(groupName, {
                                    name: groupName
                                });
                            }
                        });
                        callback(data);
                    })
                    .catch(() => {
                        callback();
                    });
            };
        }
        this.select = new TomSelect(this.element, config);
        if (this.defaultValue != null) {
            this.select.addOption(this.defaultValue);
            this.select.setValue(this.defaultValue.id);
        }
    }

    disconnect() {
        if (this.select) {
            this.select.destroy();
        }
    }
}
