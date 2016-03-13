$(function() {
    $('body').on('click', '.show-password', function() {
        var pass = $(this).parent().find('input');
        if( pass.data('show') ) {
            pass.attr('type', 'password').data('show', false);
        } else  {
            pass.attr('type', 'text').data('show', true);
        }
    });
});