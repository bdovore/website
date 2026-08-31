(function ($) {
    'use strict';

    function displayStepNumber(dataStep, hasDuplicates) {
        if (hasDuplicates) return dataStep;
        return dataStep === 1 ? 1 : dataStep - 1;
    }

    function showStep(form, step) {
        form.find('.parabd-step').attr('hidden', true);
        var section = form.find('.parabd-step[data-step="' + step + '"]');
        section.removeAttr('hidden');
        section.find('.parabd-step-number').text(displayStepNumber(step, !!form.data('has-duplicates')));
        form.attr('data-current-step', step);
    }

    function enhanceButtons(context) {
        $(context || document).find('.parabd-page button:not(.parabd-icon-delete):not(.parabd-gallery-button):not(.parabd-gallery-thumb), .parabd-page .button, .parabd-page summary:not(.parabd-copy-toggle)').addClass('ui-button ui-widget ui-state-default ui-corner-all');
    }

    function initGalleries() {
        $('.parabd-gallery[data-parabd-carousel]').each(function () {
            var gallery = $(this);
            var slides = gallery.find('.parabd-gallery-slide');
            var count = slides.length;
            if (count < 2) return;

            var thumbnails = gallery.find('.parabd-gallery-thumb');
            var status = gallery.find('.parabd-gallery-status');
            var current = 0;
            var touchStartX = null;

            function showSlide(index) {
                current = (index + count) % count;
                slides.removeClass('is-active').attr('aria-hidden', 'true');
                slides.eq(current).addClass('is-active').removeAttr('aria-hidden');
                thumbnails.removeClass('is-active').removeAttr('aria-current');
                thumbnails.eq(current).addClass('is-active').attr('aria-current', 'true');
                status.text((current + 1) + ' / ' + count);
            }

            gallery.addClass('is-enhanced');
            showSlide(0);
            gallery.find('.parabd-gallery-previous').on('click', function () { showSlide(current - 1); });
            gallery.find('.parabd-gallery-next').on('click', function () { showSlide(current + 1); });
            thumbnails.on('click', function () { showSlide(parseInt($(this).attr('data-slide'), 10)); });
            gallery.on('keydown', function (event) {
                if (event.key === 'ArrowLeft') { event.preventDefault(); showSlide(current - 1); }
                if (event.key === 'ArrowRight') { event.preventDefault(); showSlide(current + 1); }
            });
            gallery.on('touchstart', '.parabd-gallery-stage', function (event) {
                touchStartX = event.originalEvent.touches[0].clientX;
            }).on('touchend', '.parabd-gallery-stage', function (event) {
                if (touchStartX === null) return;
                var distance = event.originalEvent.changedTouches[0].clientX - touchStartX;
                touchStartX = null;
                if (Math.abs(distance) < 45) return;
                showSlide(current + (distance < 0 ? 1 : -1));
            });
        });
    }

    function renderDuplicates(rows) {
        if (!rows.length) return '<p>Aucun doublon probable trouvé. Vous pouvez poursuivre la saisie.</p>';
        var html = '';
        $.each(rows, function (_, row) {
            var title = $('<div>').text(row.level + ' — ' + row.TITLE).html();
            html += '<div class="parabd-duplicate ' + row.level.toLowerCase() + '"><strong>' + title + '</strong> (' + row.score + ' %)<br>' +
                '<a target="_blank" rel="noopener" href="' + $.bdovore.URL + 'parabd/fiche?id=' + row.ID_ITEM + '">Consulter la fiche</a></div>';
        });
        return html;
    }

    function duplicateParams(form) {
        return {
            title: $('#parabd-title').val(),
            type_id: $('#parabd-type').find(':selected').data('id') || '',
            manufacturer: form.find('[name="manufacturer"]').val(),
            publisher: form.find('[name="publisher"]').val(),
            release_date: form.find('[name="release_date"]').val(),
            width_mm: form.find('[name="width_mm"]').val(),
            height_mm: form.find('[name="height_mm"]').val(),
            depth_mm: form.find('[name="depth_mm"]').val(),
            author_id: form.find('[name^="authors["][name$="][id]"]').first().val() || 0,
            series_id: form.find('[name="series_id"]').val(),
            tome_id: form.find('[name="tome_id"]').val(),
            identifiers: JSON.stringify([{
                scheme: form.find('[name="identifier_scheme"]').val(),
                issuer: form.find('[name="identifier_issuer"]').val(),
                value: form.find('[name="identifier_value"]').val()
            }])
        };
    }

    function updateIdentifierIssuer(form) {
        var external = form.find('[name="identifier_scheme"]').val() === 'EXTERNAL_DB';
        form.find('.parabd-identifier-issuer').prop('hidden', !external)
            .find('input').prop('disabled', !external).prop('required', external);
    }

    function renumber(container) {
        container.children('.parabd-repeat-row').each(function (index) {
            $(this).find('[name]').each(function () { this.name = this.name.replace(/\[\d+\]/, '[' + index + ']'); });
        });
    }

    function checkDuplicates(form, callback) {
        var output = $('#parabd-duplicates').text('Recherche en cours…');
        var buttons = form.find('.parabd-next, #parabd-check-duplicates').prop('disabled', true);
        $.getJSON(form.data('search-url'), duplicateParams(form)).done(function (response) {
            if (!response.ok) {
                output.html('<p class="parabd-error">' + $('<div>').text(response.error.message).html() + '</p>');
                return;
            }
            var rows = response.data.candidates || [];
            output.html(renderDuplicates(rows));
            $('#duplicate-reviewed').val('1');
            form.data('has-duplicates', rows.length > 0);
            if (callback) callback(rows);
        }).fail(function () {
            output.html('<p class="parabd-error">La recherche de doublons a échoué.</p>');
        }).always(function () { buttons.prop('disabled', false); });
    }

    function validateSection(section) {
        var missing = section.find('[required]').filter(function () { return !this.value; });
        if (missing.length) {
            if (missing[0].reportValidity) missing[0].reportValidity();
            else missing[0].focus();
            return false;
        }
        if (parseInt(section.data('step'), 10) === 1) {
            var form = section.closest('form');
            var file = form.find('[name="visual"]')[0];
            var hasFile = file && file.files && file.files.length;
            if (!hasFile && !form.find('[name="visual_url"]').val()) {
                form.find('.parabd-image-error').text('Choisissez un fichier ou indiquez une URL pour le visuel principal.');
                form.find('[name="visual_url"]').focus();
                return false;
            }
            form.find('.parabd-image-error').empty();
        }
        $('#parabd-form-error').empty();
        return true;
    }

    function initReferenceAutocomplete(context) {
        $(context || document).find('.parabd-reference-input').each(function () {
            var input = $(this);
            if (input.hasClass('ui-autocomplete-input')) return;
            var hidden = input.siblings('input[type="hidden"]');
            var status = input.siblings('.parabd-reference-status');
            var row = input.closest('.parabd-repeat-row');
            var authorRole = row.length && row.hasClass('parabd-repeat-row-author')
                ? row.find('select[name$="[role]"]')
                : $();
            var selectedValue = '';
            input.autocomplete({
                minLength: 2,
                source: function (request, response) {
                    $.getJSON(input.data('source'), request).done(function (rows) {
                        response($.map(rows || [], function (row) {
                            var display = '#' + row.id + ' — ' + row.label;
                            return {label: display, value: display, id: row.id, plainLabel: row.label, defaultRole: row.default_role || ''};
                        }));
                    }).fail(function () { response([]); });
                },
                select: function (event, ui) {
                    hidden.val(ui.item.id);
                    if (authorRole.length) authorRole.val(ui.item.defaultRole).prop('required', true).trigger('change');
                    selectedValue = ui.item.value;
                    status.text('Sélection : #' + ui.item.id + ' — ' + ui.item.plainLabel).addClass('selected');
                }
            }).on('input', function () {
                if (input.val() !== selectedValue) {
                    hidden.val('');
                    if (authorRole.length) authorRole.val('').prop('required', false).trigger('change');
                    status.text('Sélectionnez une proposition pour associer l’ID et le libellé.').removeClass('selected');
                }
            });
        });
    }

    function initFreeAutocomplete() {
        $('.parabd-free-autocomplete').each(function () {
            var input = $(this);
            var minLength = parseInt(input.attr('data-min-length'), 10);
            if (isNaN(minLength)) minLength = 2;
            input.autocomplete({
                minLength: minLength,
                source: function (request, response) {
                    $.getJSON(input.data('source'), request).done(function (payload) {
                        response(payload.ok ? $.map(payload.data.suggestions || [], function (row) { return row.label; }) : []);
                    }).fail(function () { response([]); });
                }
            });
            if (minLength === 0) input.on('focus', function () { input.autocomplete('search', input.val()); });
        });
    }

    function initCatalogueAutocomplete() {
        var form = $('#parabd-catalog-search');
        var input = $('#parabd-q');
        if (!form.length || !input.length) return;
        var selectedText = input.val();
        input.autocomplete({
            minLength: 2,
            source: function (request, response) {
                $.getJSON(form.data('autocomplete-url'), request).done(function (payload) {
                    var rows = payload.ok ? payload.data.suggestions : [];
                    response($.map(rows || [], function (row) {
                        var prefix = row.id ? '#' + row.id + ' — ' : '';
                        return {
                            label: row.category + ' · ' + prefix + row.label,
                            value: row.label,
                            type: row.type,
                            id: row.id,
                            filterValue: row.label
                        };
                    }));
                }).fail(function () { response([]); });
            },
            select: function (event, ui) {
                form.find('[name="filter_type"]').val(ui.item.type);
                form.find('[name="filter_id"]').val(ui.item.id || '');
                form.find('[name="filter_value"]').val(ui.item.filterValue);
                selectedText = ui.item.value;
                window.setTimeout(function () { form.submit(); }, 0);
            }
        }).on('input', function () {
            if (input.val() !== selectedText) form.find('[name^="filter_"]').val('');
        });
    }

    function initQuickCollectionActions() {
        $('.parabd-collection-actions').on('click', '.parabd-quick-copy', function (event) {
            event.preventDefault();
            var link = $(this);
            var container = link.closest('.parabd-collection-actions');
            if (container.data('busy')) return;
            if (link.data('action') === 'remove' && !window.confirm(link.data('confirm') || 'Retirer cet objet de votre wishlist ?')) return;
            container.data('busy', true).addClass('loading');
            container.find('.parabd-quick-copy').attr('aria-disabled', 'true');
            var payload = {
                csrf_token: container.data('csrf-token'),
                item_id: container.data('item-id')
            };
            if (link.data('action') === 'remove') payload.copy_id = link.data('copy-id');
            else {
                payload.state = link.data('state');
                if (link.data('copy-id')) payload.copy_id = link.data('copy-id');
            }
            container.find('.parabd-quick-status').text('Enregistrement…');
            $.post(link.data('url'), payload, null, 'json').done(function (response) {
                if (response.ok) window.location.reload();
                else container.find('.parabd-quick-status').text(response.error.message);
            }).fail(function (xhr) {
                container.find('.parabd-quick-status').text(xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error.message : 'Enregistrement impossible.');
            }).always(function () {
                container.data('busy', false).removeClass('loading');
                container.find('.parabd-quick-copy').removeAttr('aria-disabled');
            });
        });
    }

    function initOwnedCopyRemoval() {
        $('.parabd-copy-form').on('click', '.parabd-remove-copy', function () {
            var button = $(this);
            var form = button.closest('.parabd-copy-form');
            if (!window.confirm('Supprimer cet exemplaire de votre collection ?')) return;
            button.prop('disabled', true);
            form.find('.parabd-form-status').text('Suppression…');
            $.post(button.data('url'), {
                csrf_token: form.find('[name="csrf_token"]').val(),
                copy_id: button.data('copy-id')
            }, null, 'json').done(function (response) {
                if (response.ok) window.location.reload();
                else form.find('.parabd-form-status').text(response.error.message);
            }).fail(function (xhr) {
                form.find('.parabd-form-status').text(xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error.message : 'Suppression impossible.');
            }).always(function () { button.prop('disabled', false); });
        });
    }

    $(function () {
        initGalleries();
        enhanceButtons();
        initReferenceAutocomplete();
        initFreeAutocomplete();
        initCatalogueAutocomplete();
        initQuickCollectionActions();
        initOwnedCopyRemoval();

        var form = $('#parabd-create-form');
        form.on('change', '[name="identifier_scheme"]', function () { updateIdentifierIssuer(form); });
        updateIdentifierIssuer(form);
        form.on('click', '.parabd-next', function () {
            var section = $(this).closest('.parabd-step');
            var current = parseInt(section.data('step'), 10);
            if (!validateSection(section)) return;
            if (current === 1) {
                checkDuplicates(form, function (rows) { showStep(form, rows.length ? 2 : 3); });
                return;
            }
            if (current === 2 && $('#duplicate-reviewed').val() !== '1') {
                $('#parabd-duplicates').html('<p class="parabd-error">La recherche doit être terminée avant de continuer.</p>');
                return;
            }
            showStep(form, current + 1);
        });
        form.on('click', '.parabd-prev', function () {
            var current = parseInt($(this).closest('.parabd-step').data('step'), 10);
            showStep(form, current === 3 && !form.data('has-duplicates') ? 1 : current - 1);
        });
        $('#parabd-title, #parabd-type').on('change input', function () { $('#duplicate-reviewed').val('0'); });
        var subtypeSelect = $('#parabd-subtype');
        var subtypePlaceholder = subtypeSelect.find('option').first().detach();
        var subtypeGroups = {};
        subtypeSelect.find('option[data-parent]').each(function () {
            var group = $(this).data('parent');
            (subtypeGroups[group] = subtypeGroups[group] || []).push(this);
        }).detach();
        $('#parabd-type').on('change', function () {
            var parent = $(this).find(':selected').data('id');
            subtypeSelect.empty().append(subtypePlaceholder);
            if (parent && subtypeGroups[parent]) {
                subtypeSelect.append(subtypeGroups[parent]);
            }
            subtypeSelect.val('');
        }).trigger('change');
        $('#parabd-check-duplicates').on('click', function () { checkDuplicates(form); });
        form.on('click', '.parabd-add-row', function () {
            var container = form.find('.parabd-repeat[data-repeat="' + $(this).data('target') + '"]');
            var row = container.children('.parabd-repeat-row').last().clone(false, false);
            row.find('.ui-helper-hidden-accessible').remove();
            row.find('input').val('').prop('checked', false);
            row.find('select').prop('selectedIndex', 0);
            row.find('select[name$="[role]"]').prop('required', false);
            row.find('.parabd-reference-status').text('Sélectionnez une proposition pour associer l’ID et le libellé.').removeClass('selected');
            row.find('.parabd-reference-input').removeClass('ui-autocomplete-input').removeAttr('autocomplete').removeAttr('aria-autocomplete').removeAttr('aria-controls');
            container.append(row); renumber(container); initReferenceAutocomplete(row); enhanceButtons(row);
        });
        form.on('click', '.parabd-remove-row', function () {
            var container = $(this).closest('.parabd-repeat');
            if (container.children('.parabd-repeat-row').length === 1) {
                var row = $(this).closest('.parabd-repeat-row');
                row.find('input').val('').prop('checked', false);
                row.find('select').prop('selectedIndex', 0);
                row.find('select[name$="[role]"]').prop('required', false);
                row.find('.parabd-reference-status').text('Sélectionnez une proposition pour associer l’ID et le libellé.').removeClass('selected');
            } else $(this).closest('.parabd-repeat-row').remove();
            renumber(container);
        });
        form.on('submit', function (event) {
            event.preventDefault();
            var button = form.find('[type="submit"]').prop('disabled', true);
            $('#parabd-form-error').empty();
            $.ajax({url: form.attr('action'), method: 'POST', data: new FormData(form[0]), processData: false, contentType: false, dataType: 'json'})
                .done(function (response) {
                    if (response.ok) window.location.href = response.data.redirect_url || ($.bdovore.URL + 'macollection/parabd');
                    else {
                        $('#parabd-form-error').text(response.error.message);
                        if (response.error.fields && response.error.fields.duplicate_candidates) {
                            $('#parabd-duplicates').html(renderDuplicates(response.error.fields.duplicate_candidates));
                            form.data('has-duplicates', true);
                            showStep(form, 2);
                        }
                    }
                }).fail(function (xhr) {
                    var error = xhr.responseJSON && xhr.responseJSON.error;
                    $('#parabd-form-error').text(error ? error.message : 'La création a échoué.');
                    if (error && error.fields && error.fields.duplicate_candidates) {
                        $('#parabd-duplicates').html(renderDuplicates(error.fields.duplicate_candidates));
                        form.data('has-duplicates', true);
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
            if (current.data('submit-name') === 'vote' && current.data('submit-value') === 'CONTEST' && !$.trim(current.find('[name="reason"]').val())) {
                current.find('.parabd-form-status').text('Expliquez brièvement votre opposition.');
                current.find('[name="reason"]').trigger('focus');
                return;
            }
            var payload = current.serializeArray();
            if (current.data('submit-name')) payload.push({name: current.data('submit-name'), value: current.data('submit-value')});
            $.post(current.attr('action'), payload, null, 'json').done(function (response) {
                if (response.ok) window.location.reload();
                else current.find('.parabd-form-status').text(response.error.message);
            }).fail(function (xhr) {
                current.find('.parabd-form-status').text(xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error.message : 'Enregistrement impossible.');
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

        $('.parabd-comment-revision').on('click', function () {
            var details = $('#discussion').prop('open', true);
            var revisionId = $(this).data('revision-id');
            var form = details.find('.parabd-discussion-form');
            form.find('[name="revision_id"]').val(revisionId);
            form.find('.parabd-comment-context').prop('hidden', false).find('span').text(revisionId);
            form.find('textarea').trigger('focus');
        });
        $('.parabd-clear-comment-context').on('click', function () {
            var form = $(this).closest('form'); form.find('[name="revision_id"]').val(''); $(this).closest('.parabd-comment-context').prop('hidden', true);
        });
        if (window.location.hash === '#discussion') $('#discussion').prop('open', true);
    });
})(jQuery);
