/**
 * Other extended jQuery functions
 */
$.extend({
    /**
     * Regular Expression
     */
    regexp: {
        email: /^[0-9a-z\-\_\.\+]{1,40}@([0-9a-z\-]{1,40}\.){1,4}[a-z]{2,5}$/i,
        image: /\.(gif|p?jpe?g|png|bmp)$/i,
        phone: /^[0-9\-\40]{0,40}$/i,
        date: /^\d{2}\/\d{2}\/\d{4}$/i
    },
    /**
     * @param boolean expr
     * @param string|DOMDocument|JDOM elem
     * @return boolean
     * @example
     *	$.check(!username.value, 'Invalid username', username);
     *  $.check(!username.value, 'Invalid username', function() {username.focus();});
     *	$.check(!username.value, function() {username.focus();});
     *	$.check(!username.value, function(elem) {elem.focus();}, username);
     */
    check: function(expr, msg, elem, tip) {
        var tip = tip ? tip : false;
        if (!expr) {
            return true;
        }

        var params;
        if (!msg || typeof msg == 'string') {
            if (true == tip && elem) {
                if ($(elem).data('tooltipStatus')) {
                    $(elem).data('tooltipStatus').close();
                }
                var tipStatus = $(elem).tooltip(msg, {
                    'class_name': "tips-error",
                    'hover_active': false,
                    'close_button': true,
                    'context_position': 'left top',
                    'target_position': "left bottom+10",
                    arrow_tip: {
                        direction: 'bottom',
                        offset: '25',
                        height: 10
                    }
                });
                tipStatus.show();
                $(elem).data('tooltipStatus', tipStatus);
                $(elem).focus();
                $(elem).on('click', function() {
                    tipStatus.close();
                });
            } else {
                msg && $.warning(msg);
            }
            params = Array.prototype.slice.call(arguments, 3);
        } else {
            params = Array.prototype.slice.call(arguments, 2);
            elem = msg;
        }

        if ($.isFunction(elem)) {
            elem.apply(null, params);
        } else if (elem && (elem.tagName || elem.jquery)) {
            if (true != tip) {
                elem.focus();
            }
        }
        return false;
    }
});
