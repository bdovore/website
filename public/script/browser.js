document.addEventListener('DOMContentLoaded', () => {
    const browser = document.querySelector('.bdo-browser');
    if (!browser) return;
    const search = browser.querySelector('.browser-search');
    const input = search.elements.q;
    const filters = browser.querySelector('.browser-filters');
    const explore = browser.querySelector('.browser-explore');
    const list = browser.querySelector('.browser-list');
    const matches = browser.querySelector('.browser-matches');
    const children = {ser: 'alb', aut: 'ser', edit: 'col', col: 'ser'};
    const requests = new WeakMap();
    let timer;

    function params(target, type) {
        const global = matches.contains(target);
        const query = new URLSearchParams();
        if (!global) {
            const current = new URLSearchParams(location.search);
            if (current.get('origine')) query.set('origine', current.get('origine'));
            for (const [key, value] of current) {
                if (/^a_idGenre(?:\[\d*\])?$/.test(key)) query.append('a_idGenre[]', value);
            }
        }
        query.set('type', type);
        const node = target.closest('.browser-node');
        if (node) {
            query.set('parent', node.dataset.kind);
            query.set('id', node.dataset.id);
            if (node.dataset.publisher) query.set('publisher', node.dataset.publisher);
            if (global) query.set('global', '1');
        } else if (global) {
            query.set('q', input.value.trim());
        }
        return query;
    }

    async function load(target, query, append = false) {
        requests.get(target)?.abort();
        const controller = new AbortController();
        requests.set(target, controller);
        target.setAttribute('aria-busy', 'true');
        if (append) target.disabled = true;
        else target.textContent = 'Chargement…';
        try {
            const response = await fetch(browser.dataset.endpoint + '?' + query, {signal: controller.signal});
            if (!response.ok) throw new Error();
            const html = await response.text();
            if (requests.get(target) !== controller) return;
            if (append) {
                target.insertAdjacentHTML('beforebegin', html);
                target.remove();
            } else {
                target.innerHTML = html;
                target.dataset.loaded = '1';
            }
        } catch (error) {
            if (error.name === 'AbortError') return;
            if (append) {
                target.disabled = false;
                target.textContent = 'Réessayer';
            } else {
                const retry = document.createElement('button');
                retry.textContent = 'Réessayer le chargement';
                retry.onclick = () => load(target, query);
                target.replaceChildren(retry);
            }
        } finally {
            if (requests.get(target) === controller) target.removeAttribute('aria-busy');
        }
    }

    function updateUrl() {
        const url = new URL(location.href);
        url.searchParams.delete('let');
        url.searchParams.set('rb_browse', filters.elements.rb_browse.value);
        if (input.value.trim()) url.searchParams.set('q', input.value.trim());
        else url.searchParams.delete('q');
        history.replaceState(null, '', url);
    }
    function refresh() {
        clearTimeout(timer);
        updateUrl();
        const searching = Boolean(input.value.trim());
        explore.hidden = searching;
        matches.hidden = !searching;
        if (searching) load(matches, params(matches, 'all'));
        else if (!list.dataset.loaded) load(list, params(list, filters.elements.rb_browse.value));
    }
    search.onsubmit = event => { event.preventDefault(); refresh(); };
    input.oninput = () => {
        clearTimeout(timer);
        requests.get(matches)?.abort();
        if (!input.value.trim()) refresh();
        else timer = setTimeout(refresh, 300);
    };
    filters.addEventListener('change', event => {
        if (event.target.type !== 'checkbox') return;
        const overflow = filters.querySelectorAll('input[type=checkbox]:checked').length > 10;
        if (overflow) event.target.checked = false;
        filters.querySelector('.browser-limit').hidden = !overflow;
    });
    filters.elements.origine.onchange = () => {
        filters.querySelectorAll(':checked[type=checkbox]').forEach(box => { box.checked = false; });
        filters.requestSubmit();
    };
    browser.querySelector('.browser-genres input[type=search]').oninput = event => {
        const term = event.target.value.toLocaleLowerCase('fr');
        filters.querySelectorAll('div label').forEach(label => {
            label.hidden = !label.textContent.toLocaleLowerCase('fr').includes(term);
        });
    };
    browser.addEventListener('toggle', event => {
        const node = event.target;
        if (!node.matches('.browser-node[open]')) return;
        const target = node.querySelector('.browser-children');
        if (!target.dataset.loaded && !target.hasAttribute('aria-busy'))
            load(target, params(target, children[node.dataset.kind]));
    }, true);
    browser.addEventListener('click', event => {
        const button = event.target.closest('button');
        if (!button) return;
        if (button.matches('.browser-types button')) {
            filters.elements.rb_browse.value = button.dataset.type;
            browser.querySelectorAll('.browser-types button').forEach(tab =>
                tab.setAttribute('aria-pressed', String(tab === button)));
            updateUrl();
            load(list, params(list, button.dataset.type));
        } else if (button.matches('.browser-more')) {
            const query = params(button, button.dataset.type);
            query.set('offset', button.dataset.offset);
            load(button, query, true);
        } else if (button.dataset.scroll) {
            const rail = button.closest('.browser-branch').querySelector('.browser-rail');
            rail.scrollBy({left: Number(button.dataset.scroll) * rail.clientWidth, behavior: 'smooth'});
        }
    });
    refresh();
});
