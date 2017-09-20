/**
 * datepicker
 */
function datepicker() {
    $('input.datepicker').each(function() {
        var options = $(this).data('options') || {};
        options.show_icon = false;
        if (typeof options.pair == 'string') {
            options.pair = $(options.pair);
        }
        $(this).Zebra_DatePicker(options);
    });
}
datepicker();
