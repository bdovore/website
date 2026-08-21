(function ($) {
    'use strict';

    var editorState = null;
    var unsavedMessage = "Des changements n'ont pas été enregistrés. Quitter quand même ?";

    function updateEditorState() {
        if (!editorState) return;
        var dirty = editorState.initialDirty || editorState.form.serialize() !== editorState.initialSnapshot;
        editorState.dirty = dirty;
        editorState.button.prop('disabled', !dirty).toggleClass('is-dirty', dirty);
    }

    function initEditorState(form) {
        if (!form.length || form.data('create') === 1 || form.data('dirty-tracking-initialized')) return;
        form.data('dirty-tracking-initialized', true);
        editorState = {
            form: form,
            button: form.find('.parabd-admin-save-button'),
            initialSnapshot: form.serialize(),
            initialDirty: form.data('initial-dirty') === 1,
            dirty: false,
            submitting: false,
            allowLeave: false
        };
        form.on('input.parabdEditorDirty change.parabdEditorDirty', ':input', updateEditorState);
        updateEditorState();

        $(document).off('click.parabdEditorDirty', 'a[href]').on('click.parabdEditorDirty', 'a[href]', function (event) {
            if (!editorState.dirty || editorState.submitting || editorState.allowLeave || event.ctrlKey || event.metaKey || event.shiftKey || event.which === 2) return;
            var link = $(this);
            var href = link.attr('href') || '';
            if (link.attr('target') === '_blank' || href.charAt(0) === '#') return;
            if (!window.confirm(unsavedMessage)) {
                event.preventDefault();
                event.stopImmediatePropagation();
                return;
            }
            editorState.allowLeave = true;
        });

        $('.parabd-admin-page').off('submit.parabdEditorDirty', 'form:not(#parabd-admin-editor)').on('submit.parabdEditorDirty', 'form:not(#parabd-admin-editor)', function (event) {
            if (!editorState.dirty || editorState.allowLeave) return;
            if (!window.confirm(unsavedMessage)) {
                event.preventDefault();
                event.stopImmediatePropagation();
                return;
            }
            editorState.allowLeave = true;
        });

        $(window).off('beforeunload.parabdEditorDirty').on('beforeunload.parabdEditorDirty', function (event) {
            if (!editorState.dirty || editorState.submitting || editorState.allowLeave) return;
            event.originalEvent.returnValue = unsavedMessage;
            return unsavedMessage;
        });
    }

    function enhanceButtons(context) {
        $(context || document).find('.parabd-admin-page button, .parabd-admin-page .button, .parabd-admin-page summary').addClass('ui-button ui-widget ui-state-default ui-corner-all');
    }

    function initReferences(context) {
        $(context || document).find('.parabd-admin-reference').each(function () {
            var input = $(this);
            if (input.hasClass('ui-autocomplete-input')) return;
            var hidden = input.siblings('input[type="hidden"]');
            var status = input.siblings('.parabd-reference-status');
            var selectedValue = input.val();
            input.autocomplete({
                minLength: 2,
                source: function (request, response) {
                    $.getJSON(input.data('source'), request).done(function (rows) {
                        response($.map(rows || [], function (row) {
                            return {label: '#' + row.id + ' — ' + row.label, value: row.label, id: row.id, plainLabel: row.label};
                        }));
                    }).fail(function () { response([]); });
                },
                select: function (event, ui) {
                    hidden.val(ui.item.id).trigger('change'); selectedValue = ui.item.value;
                    status.text('#' + ui.item.id + ' — ' + ui.item.plainLabel).addClass('selected');
                }
            }).on('input', function () {
                if (input.val() !== selectedValue) { hidden.val('').trigger('change'); status.text('Sélectionnez une proposition.').removeClass('selected'); }
            });
        });
    }

    function renumber(container) {
        container.children('.parabd-repeat-row').each(function (index) {
            $(this).find('[name]').each(function () { this.name = this.name.replace(/\[\d+\]/, '[' + index + ']'); });
        });
    }

    function updateIdentifierIssuers(context) {
        $(context || document).find('.parabd-identifier-issuer').each(function () {
            var issuer = $(this);
            var row = issuer.closest('.parabd-repeat-row');
            var external = row.find('select[name$="[scheme]"]').val() === 'EXTERNAL_DB';
            issuer.prop('hidden', !external).find('input').prop('disabled', !external).prop('required', external);
        });
    }

    $(function () {
        enhanceButtons(document); initReferences(document); updateIdentifierIssuers(document);

        $('.parabd-repeat[data-repeat="identifiers"]').on('change', 'select[name$="[scheme]"]', function () {
            updateIdentifierIssuers($(this).closest('.parabd-repeat-row').parent());
        });

        $('#parabd-admin-type').on('change', function () {
            var parent = $(this).find(':selected').data('id');
            $('#parabd-admin-subtype option').each(function () {
                if (!$(this).val()) return $(this).prop('hidden', false);
                $(this).prop('hidden', parseInt($(this).data('parent'), 10) !== parseInt(parent, 10));
            });
            var selected = $('#parabd-admin-subtype option:selected');
            if (selected.val() && selected.prop('hidden')) $('#parabd-admin-subtype').val('');
        }).trigger('change');

        initEditorState($('#parabd-admin-editor'));

        $('.parabd-toggle-media-form').on('click', function () {
            var button = $(this);
            var panel = $('#' + button.attr('aria-controls'));
            var expanded = button.attr('aria-expanded') === 'true';
            if (expanded) {
                var mediaForm = document.getElementById('parabd-admin-media-form');
                if (mediaForm) mediaForm.reset();
                panel.find('.parabd-image-error').empty();
            }
            panel.prop('hidden', expanded);
            button.attr('aria-expanded', expanded ? 'false' : 'true');
            button.find('.parabd-toggle-media-label').text(expanded ? 'Ajouter un visuel' : 'Annuler');
            if (!expanded) panel.find('input:visible, select:visible').first().trigger('focus');
        });

        $('.parabd-add-row').on('click', function () {
            var container = $('.parabd-repeat[data-repeat="' + $(this).data('target') + '"]');
            var row = container.children('.parabd-repeat-row').last().clone(false, false);
            row.find('.ui-helper-hidden-accessible').remove();
            row.find('input').val('').prop('checked', false);
            row.find('select').prop('selectedIndex', 0);
            row.find('.parabd-reference-status').text('Sélectionnez une proposition.').removeClass('selected');
            row.find('.parabd-admin-reference').removeClass('ui-autocomplete-input').removeAttr('autocomplete').removeAttr('aria-autocomplete').removeAttr('aria-controls');
            container.append(row); renumber(container); initReferences(row); updateIdentifierIssuers(container); enhanceButtons(row); updateEditorState();
        });

        $('.parabd-repeat').on('click', '.parabd-remove-row', function () {
            var container = $(this).closest('.parabd-repeat');
            if (container.children('.parabd-repeat-row').length === 1) {
                var row = $(this).closest('.parabd-repeat-row'); row.find('input').val('').prop('checked', false); row.find('select').prop('selectedIndex', 0);
            } else $(this).closest('.parabd-repeat-row').remove();
            renumber(container); updateIdentifierIssuers(container); updateEditorState();
        });

        $('#parabd-admin-editor').on('submit', function (event) {
            var form = $(this); var error = form.find('.parabd-image-error').empty();
            var file = form.find('[name="visual"]')[0];
            if (form.data('create') === 1 && !(file && file.files && file.files.length) && !form.find('[name="visual_url"]').val()) {
                event.preventDefault(); error.text('Choisissez un fichier ou indiquez une URL pour le visuel principal.'); return;
            }
            var primary = form.find('[name="primary_media_id"]:checked').val();
            if (primary && form.find('[name="media_hidden[' + primary + ']"]').prop('checked')) {
                event.preventDefault(); error.text('Le visuel principal ne peut pas être masqué.'); return;
            }
            if (editorState) editorState.submitting = true;
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
