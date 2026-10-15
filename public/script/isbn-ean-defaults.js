/* Apply series defaults only in creation forms; keep manual choices editable. */
(function ($) {
    $(function () {
        $('[data-isbn-ean-default-url]').each(function () {
            var checkbox = $(this);
            var serieId = $(checkbox.attr('data-serie-id') || []);
            var albumId = $(checkbox.attr('data-album-id') || []);
            var request = null;
            var revision = 0;

            function applyDefault() {
                var currentRevision = ++revision;
                if (request) request.abort();
                var tome = parseInt(albumId.val(), 10) || 0;
                var serie = parseInt(serieId.val(), 10) || 0;
                if (!tome && !serie) {
                    checkbox.prop('checked', false);
                    return;
                }
                request = $.getJSON(checkbox.attr('data-isbn-ean-default-url'), tome ? {id_tome: tome} : {id_serie: serie});
                request.done(function (data) {
                    if (revision === currentRevision) checkbox.prop('checked', data.sans_isbn_ean === true);
                });
            }

            serieId.add(albumId).on('change', applyDefault);
            $(checkbox.attr('data-serie-input') || []).add($(checkbox.attr('data-album-input') || [])).on('autocompleteselect', function () {
                // The existing autocomplete writes the selected ID after this event.
                setTimeout(applyDefault, 0);
            });
            checkbox.on('change', function () {
                // A late response must not overwrite a choice made by the administrator.
                revision++;
            });
        });
    });
})(jQuery);
