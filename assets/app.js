import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.min.css';
import 'bootstrap';
import './styles/app.css';
import './autosave.js';


document.addEventListener('DOMContentLoaded', function() {
    const editorElement = document.getElementById('markdown-editor');
    
    if (editorElement) {
        const easymde = new EasyMDE({
            element: editorElement,
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
    }
});

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.clickable-row').forEach(row => {
        row.style.cursor = 'pointer';

        row.addEventListener('click', function(e) {
            const selection = window.getSelection();
            if (selection.toString().length > 0) {
                return;
            }

            if (e.target.closest('a, button, input, select')) {
                return;
            }

            window.location.href = this.dataset.href;
        });
    });
});