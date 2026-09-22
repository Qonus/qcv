import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
    static values = {
        options: {type: Object, default: {}},
        url: String,
    }

    async connect() {
        this.easymde = new EasyMDE({
            element: this.element,
            forceSync: true,
            spellChecker: false,
            placeholder: "Write description using Markdown...",
            minHeight: "220px",
            toolbar: [
                "bold", "italic", "heading", "|", 
                "quote", "unordered-list", "ordered-list", "|", 
                "link", "code", "table", "|", 
                "preview", "side-by-side", "fullscreen"
            ],
            status: ["lines", "words"]
        });
        this.easymde.codemirror.on('change', () => {
            console.log("change");
            this.element.value = this.easymde.value();
            this.element.dispatchEvent(new Event('input', { bubbles: true }));
            this.element.dispatchEvent(new Event('change', { bubbles: true }));
        })
    }

    disconnect() {
        if (this.easymde) {
            this.easymde.toTextArea();
            this.easymde = null;
        }
    }
}
