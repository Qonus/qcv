const dirty = new Set();
const frozen = new Set();
let saveTimer = null;
// let autosaveFields = [];

function getScope(el) {
    return el.closest('[data-autosave-url]');
}

function readValue(el) {
    return el.type === 'checkbox' ? el.checked : el.value;
}

function writeValue(el, value) {
    if (el.type === 'checkbox') el.checked = Boolean(value);
    else el.value = value;
}

function scheduleSave(el) {
    console.log("SCHEDULE SAVE");
    if (frozen.has(el) || !getScope(el)) return;
    dirty.add(el);
    if(saveTimer == null) {
        saveTimer = setInterval(flushDirty, 5000);
    }
    // clearTimeout(saveTimer);
    // saveTimer = setTimeout(flushDirty, 5000);
}

async function saveField(el) {
    const scope = getScope(el);
    if (!scope) return;
    const body = {
        value: readValue(el),
        version: scope.dataset.version === '' || scope.dataset.version === undefined
        ? null
        : parseInt(scope.dataset.version, 10),
    };
    if (el.dataset.autosaveField) {
        body.field = el.dataset.autosaveField;
    }
    
    try {
        const res = await fetch(scope.dataset.autosaveUrl, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        
        const result = await res.json();
        if (res.status === 409) {
            console.log(result);
            showConflict(el, scope, result);
            return;
        }
        console.log(result);
        scope.dataset.version = result.version;
        el.classList.remove('is-invalid');
    } catch (e) {
        console.error('Autosave failed', e);
        dirty.add(el);
    }
}

async function flushDirty() {
    const batch = Array.from(dirty);
    dirty.clear();

    for (const el of batch) {
        if (frozen.has(el)) continue;
        await saveField(el);
    }
}

function showConflict(el, scope, conflict) {
    frozen.add(el);
    dirty.delete(el);

    let banner = el.nextElementSibling?.matches('.autosave-conflict') ? el.nextElementSibling : null;
    if (!banner) {
        banner = document.createElement('div');
        banner.className = 'autosave-conflict alert alert-warning py-2 px-3 mt-1 mb-2 '
            + 'd-flex justify-content-between align-items-center gap-2';
        el.insertAdjacentElement('afterend', banner);

        banner.innerHTML = `
            <span>Someone else set this to <strong>${conflict.currentValue}</strong> first.</span>
            <span class="d-flex gap-2 flex-shrink-0">
                <button type="button" class="btn btn-sm btn-outline-danger" data-resolve="mine">Keep mine</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-resolve="theirs">Use theirs</button>
            </span>
        `;
    } else {
        banner.style.display = 'flex';
        const theirsSpan = banner.querySelector('.conflict-theirs-value');
        if (theirsSpan) {
            theirsSpan.textContent = conflict.currentValue;
        }
        el.classList.add('is-invalid');
    }
    
    banner.querySelector('[data-resolve="mine"]').onclick = () => {
        scope.dataset.version = conflict.currentVersion;
        frozen.delete(el);
        banner.style.display = 'none';
        saveField(el);
    };
    banner.querySelector('[data-resolve="theirs"]').onclick = () => {
        writeValue(el, conflict.currentValue);
        scope.dataset.version = conflict.currentVersion;
        frozen.delete(el);
        banner.style.display = 'none';
        el.classList.remove('is-invalid');
    };
}

// document.addEventListener('DOMContentLoaded', () => {
//     autoSaveFields = document.querySelectorAll('.autosave-field');
// });

document.addEventListener('input', (e) => {
    // console.log("INPUT");
    // console.log(e.target);
    if (e.target.matches?.('.autosave-field')) scheduleSave(e.target);
});
document.addEventListener('change', (e) => {
    // console.log("CHANGE");
    if (e.target.matches?.('.autosave-field')) scheduleSave(e.target);
});
window.addEventListener('beforeunload', flushDirty);