/**
 * Drag
 *
 * @param String h object name
 * @returns void
 * @example
 *  $("#dd").jqDrag('.fff');
 *  $("#dd").jqDrag
 */
$.fn.jqDrag = function(h) {
    var current = this;
    return $(this).each(function() {
        h = (h) ? $(h, current) : current;
        h.bind('mousedown', {e: current}, function(v) {
            var d = v.data;
            $.fn.jqDrag.params = {};
            E = d.e;
            p = {};
            if (E.css('position') !== 'relative') {
                try {
                    E.position(p);
                } catch (e) {
                }
            }
            $.fn.jqDrag.params = {
                X: p.left || parseInt(E.css('left')) || 0,
                Y: p.top || parseInt(E.css('top')) || 0,
                W: parseInt(E.css('width')) || E[0].scrollWidth || 0,
                H: parseInt(E.css('height')) || E[0].scrollHeight || 0,
                pX: v.pageX,
                pY: v.pageY,
                currentObject: E
            };
            $(document).mousemove($.fn.jqDrag._drag).mouseup($.fn.jqDrag._stop);
        });
    });
};

$.fn.jqDrag.params = {};

$.fn.jqDrag._drag = function(v) {
    var M = $.fn.jqDrag.params;
    var leftPosition = M.X + v.pageX - M.pX;
    var topPosition = M.Y + v.pageY - M.pY;
    if (leftPosition < 0) {
        leftPosition = 0;
    }
    if (topPosition < 0) {
        topPosition = 0;
    }
    if ((leftPosition + M.W) > $(window).width()) {
        leftPosition = $(window).width() - M.W;
    }

    if ((topPosition + M.H) > $(window).height()) {
        topPosition = $(window).height() - M.H;
    }

    M.currentObject.css({
        left: leftPosition,
        top: topPosition
    });
    return false;
};

$.fn.jqDrag._stop = function() {
    $(document).unbind('mousemove', $.fn.jqDrag._drag).unbind('mouseup', $.fn.jqDrag._stop);
};

