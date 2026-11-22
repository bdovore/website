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
    const scope = search.elements.scope;
    const scopeGroup = search.querySelector('.browser-scope');
    function defaultScope() {
        const tab = filters.elements.rb_browse.value;
        return tab === 'ser' ? 'all' : tab;
    }
    scope.value = defaultScope();
    const savedScope = new URLSearchParams(location.search).get('scope');
    if (input.value.trim() && Array.from(scope).some(radio => radio.value === savedScope)) {
        scope.value = savedScope;
    }
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
        if (window.jQuery?.browser?.mobile) query.set('mobile', 'T');
        const node = target.closest('.browser-node');
        if (node) {
            query.set('parent', node.dataset.kind);
            query.set('id', node.dataset.id);
            const collection = node.closest('[data-kind=col]');
            if (collection) {
                query.set('publisher', collection.dataset.publisher);
                if (node.dataset.kind === 'ser') query.set('collection', collection.dataset.id);
            }
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
            window.lazyInfoCollection?.init();
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
        if (input.value.trim()) {
            url.searchParams.set('q', input.value.trim());
            url.searchParams.set('scope', scope.value);
        } else {
            url.searchParams.delete('q');
            url.searchParams.delete('scope');
        }
        history.replaceState(null, '', url);
    }
    function refresh() {
        clearTimeout(timer);
        updateUrl();
        const searching = Boolean(input.value.trim());
        scopeGroup.hidden = !searching;
        if (!searching) scope.value = defaultScope();
        explore.hidden = searching;
        matches.hidden = !searching;
        if (searching) load(matches, params(matches, scope.value));
        else if (!list.dataset.loaded) load(list, params(list, filters.elements.rb_browse.value));
    }
    search.onsubmit = event => { event.preventDefault(); refresh(); };
    scopeGroup.onchange = refresh;
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
    const rayon = filters.elements.origine;
    const genreSearch = filters.querySelector('input[type=search]');
    function updateGenres() {
        const name = filters.querySelector('input[name=origine]:checked').dataset.name;
        const term = genreSearch.value.trim().toLocaleLowerCase('fr');
        for (const label of filters.querySelectorAll('label[data-rayon]')) {
            const outside = Boolean(name && label.dataset.rayon !== name);
            label.querySelector('input').disabled = outside;
            label.hidden = outside || !label.textContent.toLocaleLowerCase('fr').includes(term);
        }
    }
    filters.querySelector('.browser-rayons').onchange = () => {
        filters.querySelectorAll('input[type=checkbox]:checked').forEach(box => { box.checked = false; });
        genreSearch.value = '';
        updateGenres();
        filters.querySelector('.browser-genre-count').textContent = '';
        filters.querySelector('.browser-limit').hidden = true;
        browser.querySelector('.browser-chips')?.remove();
        const url = new URL(location.href);
        for (const key of Array.from(url.searchParams.keys())) {
            if (/^a_idGenre(?:\[\d*\])?$/.test(key)) url.searchParams.delete(key);
        }
        if (rayon.value) url.searchParams.set('origine', rayon.value);
        else url.searchParams.delete('origine');
        history.replaceState(null, '', url);
        load(list, params(list, filters.elements.rb_browse.value));
    };
    genreSearch.oninput = updateGenres;
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
            scope.value = defaultScope();
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
