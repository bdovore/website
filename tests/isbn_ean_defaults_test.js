// Test the actual script's asynchronous behaviour without network or a database.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const elements = new Map();
const requests = [];
const timers = [];
function element(id, attrs = {}) {
    const item = {attrs, value: '', checked: false, handlers: {}};
    elements.set(id, item);
    return item;
}
const checkbox = element('#flag', {
    'data-isbn-ean-default-url': '/getjson?data=SansIsbnEan',
    'data-serie-id': '#serieId', 'data-serie-input': '#serie',
    'data-album-id': '#albumId', 'data-album-input': '#album'
});
element('#serie'); element('#album');
const serieId = element('#serieId');
const albumId = element('#albumId');
function $(selector) {
    if (typeof selector === 'function') { selector(); return; }
    const items = selector === '[data-isbn-ean-default-url]' ? [checkbox]
        : typeof selector === 'string' ? [elements.get(selector)].filter(Boolean)
        : Array.isArray(selector) ? selector : [selector];
    return {
        items,
        each(fn) { items.forEach(item => fn.call(item)); return this; },
        attr(name) { return items[0]?.attrs[name]; },
        val() { return items[0]?.value; },
        prop(name, value) { items.forEach(item => { item[name] = value; }); return this; },
        add(other) { return $([...items, ...other.items]); },
        on(name, fn) { items.forEach(item => { item.handlers[name] = fn; }); return this; }
    };
}
$.getJSON = (url, data) => {
    const request = {url, data, aborted: false, callback: null,
        abort() { this.aborted = true; }, done(fn) { this.callback = fn; }};
    requests.push(request); return request;
};
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../public/script/isbn-ean-defaults.js'), 'utf8'), {
    jQuery: $, setTimeout: fn => timers.push(fn)
});
serieId.value = '1'; serieId.handlers.change();
requests.at(-1).callback({sans_isbn_ean: true});
assert.equal(checkbox.checked, true, 'flagged series prechecks the field');
serieId.value = '2'; serieId.handlers.change();
requests.at(-1).callback({sans_isbn_ean: false});
assert.equal(checkbox.checked, false, 'ordinary series clears the default');
serieId.value = '1'; serieId.handlers.change();
const stale = requests.at(-1);
serieId.value = '2'; serieId.handlers.change();
requests.at(-1).callback({sans_isbn_ean: false});
stale.callback({sans_isbn_ean: true});
assert.equal(stale.aborted, true, 'previous request aborted when context changes');
assert.equal(checkbox.checked, false, 'late response cannot apply the previous series default');
serieId.value = '1'; serieId.handlers.change();
checkbox.checked = false; checkbox.handlers.change();
requests.at(-1).callback({sans_isbn_ean: true});
assert.equal(checkbox.checked, false, 'manual override survives a pending response');
albumId.value = '10'; albumId.handlers.change();
assert.equal(requests.at(-1).data.id_tome, 10, 'existing album determines the series default');
albumId.value = ''; serieId.value = '1'; elements.get('#serie').handlers.autocompleteselect();
serieId.value = '3'; timers.shift()();
assert.equal(requests.at(-1).data.id_serie, 3, 'autocomplete reads ID after the existing selection handler');
serieId.value = ''; serieId.handlers.change();
assert.equal(checkbox.checked, false, 'empty context has no default');
console.log('8 contrôles, 0 échec(s).');
