/* Saisie assistée de date partielle (année, mois ou jour) pour les fiches Para-BD.
 * Affiche un calendrier drill-down (année -> mois -> jour) et conserve la valeur
 * au format ISO attendu par le serveur (AAAA, AAAA-MM ou AAAA-MM-JJ). */
(function ($) {
    'use strict';
    if (!$ || window.parabdDatefieldInit) return;
    window.parabdDatefieldInit = true;

    var MONTHS = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    var MONTHS_SHORT = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    var DOW = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

    function pad(n) { return n < 10 ? '0' + n : String(n); }

    function parseValue(str) {
        str = $.trim(str || '');
        if (!str) return null;
        var m;
        if ((m = str.match(/^(\d{4})-(\d{2})-(\d{2})$/))) return { y: +m[1], mo: +m[2], d: +m[3], precision: 'day' };
        if ((m = str.match(/^(\d{4})-(\d{2})$/))) return { y: +m[1], mo: +m[2], d: null, precision: 'month' };
        if ((m = str.match(/^(\d{4})$/))) return { y: +m[1], mo: null, d: null, precision: 'year' };
        return null;
    }

    function toIso(d) {
        if (!d) return '';
        if (d.precision === 'year') return String(d.y);
        if (d.precision === 'month') return d.y + '-' + pad(d.mo);
        return d.y + '-' + pad(d.mo) + '-' + pad(d.d);
    }

    function toFrench(d) {
        if (!d) return '';
        if (d.precision === 'year') return String(d.y);
        if (d.precision === 'month') return MONTHS[d.mo - 1] + ' ' + d.y;
        return d.d + ' ' + MONTHS[d.mo - 1] + ' ' + d.y;
    }

    function daysInMonth(y, mo) { return new Date(y, mo, 0).getDate(); }
    function firstDayDow(y, mo) { return (new Date(y, mo - 1, 1).getDay() + 6) % 7; } // 0 = lundi

    function buildWidget($root) {
        if ($root.data('parabd-datefield')) return;
        $root.data('parabd-datefield', true);

        var $display = $root.find('.parabd-datefield-display');
        var $value = $root.find('.parabd-datefield-value');
        var $btn = $root.find('.parabd-datefield-btn');
        var $clear = $root.find('.parabd-datefield-clear');
        var $popup = $('<div class="parabd-dp" hidden></div>').appendTo($root);

        var state = {
            selected: parseValue($value.val()),
            view: 'day',
            viewYear: 0,
            viewMonth: 1,
            yearStart: 0
        };
        if (state.selected) {
            state.viewYear = state.selected.y;
            state.viewMonth = state.selected.mo || 1;
        } else {
            var now = new Date();
            state.viewYear = now.getFullYear();
            state.viewMonth = now.getMonth() + 1;
        }
        state.yearStart = Math.floor(state.viewYear / 12) * 12;
        renderDisplay();

        function renderDisplay() {
            $display.val(toFrench(state.selected));
            $clear.prop('hidden', !state.selected);
        }

        function commit(d) {
            state.selected = d;
            $value.val(toIso(d)).trigger('change');
            if (d) { state.viewYear = d.y; state.viewMonth = d.mo || state.viewMonth; }
            renderDisplay();
            close();
        }

        function open() {
            if (state.selected) { state.viewYear = state.selected.y; state.viewMonth = state.selected.mo || state.viewMonth; }
            state.yearStart = Math.floor(state.viewYear / 12) * 12;
            $popup.prop('hidden', false);
            $btn.attr('aria-expanded', 'true');
            render();
            setTimeout(bindOutside, 0);
        }

        function close() {
            $popup.prop('hidden', true);
            $btn.attr('aria-expanded', 'false');
            $(document).off('.parabdDp');
        }

        function bindOutside() {
            $(document).on('click.parabdDp', function (e) {
                if (!$(e.target).closest($root).length) close();
            }).on('keydown.parabdDp', function (e) {
                if (e.key === 'Escape') close();
            });
        }

        function goPrev() {
            if (state.view === 'day') { state.viewMonth--; if (state.viewMonth < 1) { state.viewMonth = 12; state.viewYear--; } }
            else if (state.view === 'month') { state.viewYear--; }
            else { state.yearStart -= 12; }
            render();
        }
        function goNext() {
            if (state.view === 'day') { state.viewMonth++; if (state.viewMonth > 12) { state.viewMonth = 1; state.viewYear++; } }
            else if (state.view === 'month') { state.viewYear++; }
            else { state.yearStart += 12; }
            render();
        }

        function isHighlighted(d) {
            var s = state.selected;
            if (!s) return false;
            if (state.view === 'day') return s.precision !== 'year' && s.y === state.viewYear && s.mo === state.viewMonth && s.d === d;
            if (state.view === 'month') return s.precision !== 'year' && s.y === state.viewYear && s.mo === d;
            return s.y === d;
        }

        function render() {
            var html = '';
            if (state.view === 'day') {
                var first = firstDayDow(state.viewYear, state.viewMonth);
                var total = daysInMonth(state.viewYear, state.viewMonth);
                html += '<div class="parabd-dp-header"><button type="button" class="parabd-dp-nav parabd-dp-prev" aria-label="Mois précédent">‹</button>';
                html += '<button type="button" class="parabd-dp-title">' + MONTHS[state.viewMonth - 1] + ' ' + state.viewYear + '</button>';
                html += '<button type="button" class="parabd-dp-nav parabd-dp-next" aria-label="Mois suivant">›</button></div>';
                html += '<div class="parabd-dp-dow">' + DOW.map(function (l) { return '<span>' + l + '</span>'; }).join('') + '</div>';
                html += '<div class="parabd-dp-grid parabd-dp-grid-days">';
                for (var i = 0; i < first; i++) html += '<span class="parabd-dp-empty"></span>';
                for (var day = 1; day <= total; day++) html += '<button type="button" class="parabd-dp-cell' + (isHighlighted(day) ? ' is-selected' : '') + '" data-day="' + day + '">' + day + '</button>';
                var filled = (first + total) % 7;
                if (filled) for (var k = 0; k < 7 - filled; k++) html += '<span class="parabd-dp-empty"></span>';
                html += '</div>';
                html += renderFooter('Valider « ' + MONTHS[state.viewMonth - 1] + ' ' + state.viewYear + ' »', 'month');
            } else if (state.view === 'month') {
                html += '<div class="parabd-dp-header"><button type="button" class="parabd-dp-nav parabd-dp-prev" aria-label="Année précédente">‹</button>';
                html += '<button type="button" class="parabd-dp-title">' + state.viewYear + '</button>';
                html += '<button type="button" class="parabd-dp-nav parabd-dp-next" aria-label="Année suivante">›</button></div>';
                html += '<div class="parabd-dp-grid parabd-dp-grid-months">';
                for (var mo = 1; mo <= 12; mo++) html += '<button type="button" class="parabd-dp-cell' + (isHighlighted(mo) ? ' is-selected' : '') + '" data-month="' + mo + '">' + MONTHS_SHORT[mo - 1] + '</button>';
                html += '</div>';
                html += renderFooter('Valider l’année ' + state.viewYear, 'year');
            } else {
                html += '<div class="parabd-dp-header"><button type="button" class="parabd-dp-nav parabd-dp-prev" aria-label="Période précédente">‹</button>';
                html += '<span class="parabd-dp-title">' + state.yearStart + '–' + (state.yearStart + 11) + '</span>';
                html += '<button type="button" class="parabd-dp-nav parabd-dp-next" aria-label="Période suivante">›</button></div>';
                html += '<div class="parabd-dp-grid parabd-dp-grid-years">';
                for (var yi = 0; yi < 12; yi++) {
                    var year = state.yearStart + yi;
                    html += '<button type="button" class="parabd-dp-cell' + (isHighlighted(year) ? ' is-selected' : '') + '" data-year="' + year + '">' + year + '</button>';
                }
                html += '</div>';
                html += '<div class="parabd-dp-footer"><button type="button" class="parabd-dp-clear">Effacer</button></div>';
            }
            $popup.html(html);
        }

        function renderFooter(validateLabel, validatePrecision) {
            var html = '<div class="parabd-dp-footer"><button type="button" class="parabd-dp-validate">' + validateLabel + '</button><button type="button" class="parabd-dp-clear">Effacer</button></div>';
            $popup.data('validate-precision', validatePrecision);
            return html;
        }

        $display.on('focus click', function (e) { e.preventDefault(); if ($popup.prop('hidden')) open(); });
        $btn.on('click', function () { if ($popup.prop('hidden')) open(); else close(); });
        $clear.on('click', function () { commit(null); });

        $popup.on('click', '.parabd-dp-prev', goPrev)
            .on('click', '.parabd-dp-next', goNext)
            .on('click', '.parabd-dp-title', function () {
                if (state.view === 'day') state.view = 'month';
                else if (state.view === 'month') { state.view = 'year'; state.yearStart = Math.floor(state.viewYear / 12) * 12; }
                render();
            })
            .on('click', '[data-day]', function () {
                var day = +$(this).data('day');
                commit({ y: state.viewYear, mo: state.viewMonth, d: day, precision: 'day' });
            })
            .on('click', '[data-month]', function () {
                state.viewMonth = +$(this).data('month');
                state.view = 'day';
                render();
            })
            .on('click', '[data-year]', function () {
                state.viewYear = +$(this).data('year');
                state.view = 'month';
                render();
            })
            .on('click', '.parabd-dp-validate', function () {
                var precision = $popup.data('validate-precision');
                if (precision === 'year') commit({ y: state.viewYear, mo: null, d: null, precision: 'year' });
                else if (precision === 'month') commit({ y: state.viewYear, mo: state.viewMonth, d: null, precision: 'month' });
            })
            .on('click', '.parabd-dp-clear', function () { commit(null); });
    }

    $(function () {
        $('.parabd-datefield').each(function () { buildWidget($(this)); });
    });
})(jQuery);
