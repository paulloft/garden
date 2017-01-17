$(function(){
    $('select[name="driver"]').change(function(){
        var value = $(this).val();
        $('.block-settings').addClass('hidden');
        $('.block-settings.'+value).removeClass('hidden');
    }).change();
});