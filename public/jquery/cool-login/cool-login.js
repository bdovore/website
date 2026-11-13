/* Connexion progressive : les liens et formulaires restent utilisables sans JS. */
$(function () {
    var button = $('#loginButton');
    var box = $('#loginBox');
    if (!button.length || !box.length) return;
    var modern = box.hasClass('header-account-panel') && $('#header').hasClass('bdo-header');
    if (!modern) return;
    $('#header').addClass('header-enhanced');
    var form = box.find('form[data-floating-login]');
    var menuButton = $('#headerMenuButton');
    var menu = $('#searchtable');
    var backdrop = $('<div class="header-account-backdrop" hidden aria-hidden="true"></div>').appendTo('body');
    var mobile = window.matchMedia('(max-width: 760px)');
    var previousOverflow = null;
    var pending = false;

    function updateViewport() {
        var opened = !box.prop('hidden');
        $('#header').toggleClass('header-account-open', opened);
        box.attr('aria-modal', opened && mobile.matches ? 'true' : 'false');
        if (opened && mobile.matches) {
            if (previousOverflow === null) {
                previousOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
            }
            var viewport = window.visualViewport;
            if (viewport) {
                box.css({ maxHeight: Math.max(100, viewport.height - 16) + 'px', bottom: Math.max(0, window.innerHeight - viewport.height - viewport.offsetTop) + 'px' });
            }
        } else {
            if (previousOverflow !== null) {
                document.body.style.overflow = previousOverflow;
                previousOverflow = null;
            }
            box.css({ maxHeight: '', bottom: '' });
        }
    }
    function setMenu(open) {
        menu.toggleClass('is-open', open);
        menuButton.attr({ 'aria-expanded': String(open), 'aria-label': open ? 'Fermer le menu' : 'Ouvrir le menu' });
    }
    function closeBox(restoreFocus) {
        box.prop('hidden', true);
        backdrop.prop('hidden', true);
        button.removeClass('active').attr('aria-expanded', 'false');
        updateViewport();
        if (restoreFocus) button.focus();
    }
    function openBox() {
        setMenu(false);
        box.prop('hidden', false);
        backdrop.prop('hidden', false);
        button.addClass('active').attr('aria-expanded', 'true');
        updateViewport();
        // Le clavier mobile s'ouvre seulement lorsque l'utilisateur choisit un champ.
        if (mobile.matches) box.find('.header-account-close').focus();
        else box.find('input:visible:enabled, a:visible, button:visible').not('.header-account-close').first().focus();
    }
    button.on('click', function (event) {
        event.preventDefault();
        if (box.prop('hidden')) openBox();
        else closeBox(true);
    });
    box.find('.header-account-close').on('click', function () { closeBox(true); });
    box.find('.header-profile-link').on('click', function () { closeBox(false); });
    backdrop.on('click', function () { closeBox(true); });
    $(document).on('click', function (event) {
        if (!box.prop('hidden') && !$(event.target).closest('#loginContainer').length) closeBox(false);
    });
    menuButton.on('click', function () {
        closeBox(false);
        setMenu(menuButton.attr('aria-expanded') !== 'true');
    });
    $(document).on('keydown', function (event) {
        if (event.which === 27) {
            if (!box.prop('hidden')) closeBox(true);
            else if (menuButton.attr('aria-expanded') === 'true') { setMenu(false); menuButton.focus(); }
        }
        if (event.which === 9 && mobile.matches && !box.prop('hidden')) {
            var controls = box.find('a:visible, button:visible:enabled, input:visible:enabled');
            var first = controls[0], last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    $(window).on('resize', updateViewport);
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', updateViewport);
        window.visualViewport.addEventListener('scroll', updateViewport);
    }
    box.find('.header-password-toggle').on('click', function () {
        var input = $('#user_password');
        var show = input.attr('type') === 'password';
        input.attr('type', show ? 'text' : 'password');
        $(this).attr({ 'aria-pressed': String(show), 'aria-label': show ? 'Masquer le mot de passe' : 'Afficher le mot de passe' });
        $(this).find('i').toggleClass('fa-eye', !show).toggleClass('fa-eye-slash', show);
    });
    if (!form.length) return;
    var errorBox = $('#header-login-error');
    var pseudoField = $('#header-pseudo-field');
    var pseudoInput = $('#header-user-pseudo');
    var submit = form.find('button[type="submit"]');
    function showError(message) {
        errorBox.text(message).prop('hidden', false);
        box[0].scrollTop = 0;
    }
    $('#user_login').on('input', function () {
        pseudoField.prop('hidden', true);
        pseudoInput.prop({ disabled: true, required: false }).val('');
        errorBox.prop('hidden', true).text('');
    });
    form.on('submit', function (event) {
        event.preventDefault();
        if (pending) return;
        var data = form.serialize() + '&submitLoginConnect=1';
        var identifier = $('#user_login').val();
        pending = true;
        errorBox.prop('hidden', true).text('');
        submit.prop('disabled', true).text('Connexion…');
        form.attr('aria-busy', 'true');
        form.find('input:not([type="hidden"])').prop('readOnly', true);
        function handleResponse(result) {
            if (result && result.authenticated === true) {
                $('#user_password').val('');
                window.location.reload();
                return;
            }
            if (result && result.needs_pseudo === true && identifier === $('#user_login').val()) {
                pseudoField.prop('hidden', false);
                pseudoInput.prop({ disabled: false, required: true });
                showError(result.message || 'Précisez le pseudo de votre collection pour continuer.');
                pseudoInput.focus();
            } else {
                showError(result && typeof result.message === 'string' ? result.message : 'La connexion est momentanément indisponible. Réessayez.');
            }
        }
        $.ajax({ url: form.attr('action'), type: 'POST', data: data, dataType: 'json', timeout: 20000,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).done(handleResponse).fail(function (xhr) {
            var result = xhr.responseJSON;
            if (!result) { try { result = $.parseJSON(xhr.responseText); } catch (ignore) {} }
            if (xhr.status === 429) result = { message: 'Trop de tentatives. Patientez quelques minutes avant de réessayer.' };
            handleResponse(result);
        }).always(function () {
            pending = false;
            submit.prop('disabled', false).text('Se connecter');
            form.removeAttr('aria-busy');
            form.find('input:not([type="hidden"])').prop('readOnly', false);
        });
    });
});
