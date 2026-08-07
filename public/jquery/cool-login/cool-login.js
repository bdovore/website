// Login Form

$(function() {
    var button = $('#loginButton');
    var box = $('#loginBox');
    var form = $('#loginForm');
    function toggleLoginBox() {
        box.toggle();
        button.toggleClass('active');
        button.attr('aria-expanded', box.is(':visible') ? 'true' : 'false');
    }
    button.mouseup(function() {
        toggleLoginBox();
    });
    button.keydown(function(login) {
        if (login.which === 13 || login.which === 32) {
            login.preventDefault();
            toggleLoginBox();
        }
    });
    box.mouseup(function() {
        return false;
    });
    $(this).mouseup(function(login) {
        if (!$(login.target).closest('#loginContainer').length) {
            button.removeClass('active');
            button.attr('aria-expanded', 'false');
            box.hide();
        }
    });
});
