(function ($) {
    'use strict';

    function showStep(form, step) {
        form.find('.parabd-step').attr('hidden', true);
        form.find('.parabd-step[data-step="' + step + '"]').removeAttr('hidden');
    }

    function renderDuplicates(rows) {
        if (!rows.length) return '<p>Aucun doublon probable trouvé.</p>';
        var html = '';
        $.each(rows, function (_, row) {
            html += '<div class="parabd-duplicate ' + row.level.toLowerCase() + '"><strong>' + $('<div>').text(row.level + ' — ' + row.TITLE).html() + '</strong> (' + row.score + ' %)<br><a target="_blank" href="' + window.location.pathname.replace(/parabd\/create$/, 'parabd/fiche') + '?id=' + row.ID_ITEM + '">Consulter la fiche</a></div>';
        });
        return html;
    }

    $(function () {
        var form = $('#parabd-create-form');
        form.on('click', '.parabd-next', function () {
            var section = $(this).closest('.parabd-step');
            var current = parseInt(section.data('step'), 10);
            var missing = section.find('[required]').filter(function () { return !this.value; });
            if (missing.length) {
                if (missing[0].reportValidity) missing[0].reportValidity();
                else missing[0].focus();
                return;
            }
            if (current === 2 && $('#duplicate-reviewed').val() !== '1') {
                $('#parabd-duplicates').html('<p class="parabd-error">Lancez la recherche avant de continuer.</p>');
                return;
            }
            showStep(form, current + 1);
        });
        form.on('click', '.parabd-prev', function () {
            showStep(form, parseInt($(this).closest('.parabd-step').data('step'), 10) - 1);
        });
        $('#parabd-type').on('change', function () {
            var parent = $(this).find(':selected').data('id');
            $('#parabd-subtype option').each(function () {
                var optionParent = $(this).data('parent');
                $(this).toggle(!optionParent || optionParent === parent);
            });
            $('#parabd-subtype').val('');
        }).trigger('change');
        $('#parabd-check-duplicates').on('click', function () {
            var params = {
                title: $('#parabd-title').val(),
                manufacturer: form.find('[name="manufacturer"]').val(),
                release_date: form.find('[name="release_date"]').val(),
                width_mm: form.find('[name="width_mm"]').val(),
                height_mm: form.find('[name="height_mm"]').val(),
                depth_mm: form.find('[name="depth_mm"]').val(),
                author_id: form.find('[name="author_id"]').val(),
                series_id: form.find('[name="series_id"]').val(),
                tome_id: form.find('[name="tome_id"]').val(),
                identifiers: JSON.stringify([{scheme: form.find('[name="identifier_scheme"]').val(), issuer: form.find('[name="identifier_issuer"]').val(), value: form.find('[name="identifier_value"]').val()}])
            };
            $('#parabd-duplicates').text('Recherche en cours…');
            $.getJSON(form.data('search-url'), params).done(function (response) {
                if (!response.ok) { $('#parabd-duplicates').text(response.error.message); return; }
                var rows = response.data.candidates;
                $('#parabd-duplicates').html(renderDuplicates(rows));
                $('#duplicate-reviewed').val('1');
            }).fail(function () { $('#parabd-duplicates').text('La recherche a échoué.'); });
        });
        form.on('submit', function (event) {
            event.preventDefault();
            var button = form.find('[type="submit"]').prop('disabled', true);
            $('#parabd-form-error').empty();
            $.ajax({url: form.attr('action'), method: 'POST', data: new FormData(form[0]), processData: false, contentType: false, dataType: 'json'})
                .done(function (response) {
                    if (response.ok) window.location.href = form.attr('action').replace(/\/create$/, '/fiche') + '?id=' + response.data.item_id;
                    else {
                        $('#parabd-form-error').text(response.error.message);
                        if (response.error.fields && response.error.fields.duplicate_candidates) {
                            $('#parabd-duplicates').html(renderDuplicates(response.error.fields.duplicate_candidates));
                            showStep(form, 2);
                        }
                    }
                }).fail(function (xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error.message : 'La création a échoué.';
                    $('#parabd-form-error').text(message);
                    if (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.fields && xhr.responseJSON.error.fields.duplicate_candidates) {
                        $('#parabd-duplicates').html(renderDuplicates(xhr.responseJSON.error.fields.duplicate_candidates));
                        showStep(form, 2);
                    }
                }).always(function () { button.prop('disabled', false); });
        });
        $('.parabd-json-form button[name]').on('click', function () {
            $(this).closest('form').data('submit-name', this.name).data('submit-value', this.value);
        });
        $('.parabd-json-form').on('submit', function (event) {
            event.preventDefault();
            var current = $(this);
            if (current.data('confirm') && !window.confirm(current.data('confirm'))) return;
            var payload = current.serializeArray();
            if (current.data('submit-name')) payload.push({name: current.data('submit-name'), value: current.data('submit-value')});
            $.post(current.attr('action'), payload, null, 'json').done(function (response) {
                if (response.ok) window.location.reload();
                else current.find('.parabd-form-status').text(response.error.message);
            });
        });
        $('.parabd-file-form').on('submit', function (event) {
            event.preventDefault();
            var current = $(this);
            $.ajax({url: current.attr('action'), method: 'POST', data: new FormData(current[0]), processData: false, contentType: false, dataType: 'json'})
                .done(function (response) {
                    if (response.ok) window.location.reload();
                    else current.find('.parabd-form-status').text(response.error.message);
                }).fail(function (xhr) {
                    current.find('.parabd-form-status').text(xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error.message : 'Envoi impossible.');
                });
        });
    });
})(jQuery);
