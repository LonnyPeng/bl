/**
 * Create an overlay for popup windows
 *
 * @param PlainObject|float opts field(opacity = 0.2, onshow = null, onclose = null, inSpeed? = 0, outSpeed? = 0)
 * @param Function onshow
 * @param Function onclose
 * @return void
 * @example
 *	$.overlay()
 *	$.overlay(0.2)
 *	$.overlay(onshow);
 *	$.overlay(onshow, onclose)
 *	$.overlay(0.2, onshow, onclose)
 *  $.overlay(opts)
 */
$.overlay = function(opts, onshow, onclose) {
    $.overlay._onshowHandler = null;
    $.overlay._oncloseHandler = null;
    $.overlay._options = {};

    if ($('#overlay').length > 0) {
        $.overlay._remove();
    }

    if (typeof opts === 'function') {
        $.overlay._onshowHandler = opts;
        opts = {};
        if (typeof onshow === 'function') {
            $.overlay._oncloseHandler = onshow;
        }
    } else if (typeof opts === 'number') {
        opts.opacity = opts;
        if (typeof onshow === 'function') {
            $.overlay._onshowHandler = onshow;
        }
        if (typeof onclose === 'function') {
            $.overlay._oncloseHandler = onclose;
        }
    } else if (typeof opts !== 'object') {
        opts = {};
    }

    opts = $.extend({}, $.overlay.defaults, opts || {});
    $.overlay._options = opts;

    $("body").append('<div class="overlay" id="overlay"></div>');

    $('#overlay').css({'position': 'fixed', 'opacity': 0, 'left': 0, 'top': 0, 'height': '100%', 'width': '100%'});
    if (opts.animate) {
        $('#overlay').animate({opacity: opts.opacity}, opts.animate.startDuration, function() {
            $.overlay._onshowHandler && $.overlay._onshowHandler.call();
        });
    } else {
        $('#overlay').css('opacity', opts.opacity);
        $.overlay._onshowHandler && $.overlay._onshowHandler.call();
    }
};

$.overlay.defaults = {
    opacity: 0.2,
    animate: false
};

$.overlay._remove = function(noAnimate) {
    if ($.overlay._options && $.overlay._options.animate && noAnimate !== true) {
        $('#overlay').animate({opacity: 0}, $.overlay._options.animate.endDuration, function() {
            $('#overlay').remove();
        });
    } else {
        $('#overlay').remove();
    }
    $.overlay._oncloseHandler && $.overlay._oncloseHandler.call();
    $.overlay._onshowHandler = null;
    $.overlay._oncloseHandler = null;
};

/**
 * Popup window-like layer via HTML, DOMDocument or $(DOMDocument)
 *
 * @param DOMDocument|$(DOMDocument)|string elem
 * @param PlainObject|string opts field(title = '', overlay = {}, dragable = true, closable = true, onshow = null, onclose = null)
 * @param Function onshow
 * @param Function onclose
 * @return void
 * @example
 *	$.popup(elem|html|jElem);
 *	$.popup(elem, opts);
 *	$.popup(elem, title);
 *	$.popup(elem, onshow, onclose)
 *	$.popup(elem, opts, onshow, onclose);
 *	$.popup(elem, title, onshow, onclose)
 */
$.popup = function(elem, opts, onshow, onclose) {
    if ($.popup._oldCard !== undefined && $.popup._oldCard) {
        return false;
        $('body').stop('effectCard');
        $.popup.remove(true);
    }

    $.popup._onshow = null;
    $.popup._onclose = null;
    $.popup._oldCard = null;
    $.popup._cloneCard = null;

    if ($('.popup-wrapper').length > 0) {
        $.popup.remove();
    }

    //var jqString = /^[\.|\#]\w+$/i;
    $.popup._isNeedSave = 1; // 0: no need save, 1: need save
    $.popup._object = null; // object
    if (typeof elem === 'object') {
        // document.getElement.ById()
        if (elem instanceof  $) {
            elem = elem.get(0);
        }
    } else if (typeof elem === 'string' && elem.substr(0, 1) !== '<' && $(elem).length > 0) {
        elem = $(elem).get(0);
    } else {
        $.popup._isNeedSave = 0;
    }
    $.popup._object = elem;

    if (opts && (typeof opts === 'object' || typeof opts === 'string')) {
        if (typeof opts.onshow === 'function') {
            $.popup._onshow = opts.onshow;
        }
        if (typeof opts.onclose === 'function') {
            $.popup._onclose = opts.onclose;
        }
        if (typeof onshow === 'function') {
            $.popup._onshow = onshow;
        }
        if (typeof onclose === 'function') {
            $.popup._onclose = onclose;
        }
    } else if (typeof opts === 'function') {
        $.popup._onshow = opts;
        if (typeof onshow === 'function') {
            $.popup._onclose = onshow;
        }
        opts = {};
    }

    if (typeof opts === 'string') {
        opts = {title: opts};
    }

    opts = $.extend({}, $.popup.defaults, opts || {});

    // js card
    var effectCard = [];
    if (opts.cardObject && $.browser.transform3D) {
        $.popup._oldCard = opts.cardObject;
        $.popup._cloneCard = opts.cardObject.clone();
        $.popup._cloneCard.css({width: $.popup._oldCard.width(), height: $.popup._oldCard.height()});
        var popupCard = $('<div id="popup-card"/>');
        var flipCon = $('<div class="popup-main-flip"></div>');
        if ($.popup._oldCard.is('li')) {
            var parentCard = $('<ul/>');
            parentCard.addClass($.popup._oldCard.parent('ul').attr('class'));
            $.popup._cloneCard = parentCard.append($.popup._cloneCard);
        }
        popupCard.append($.popup._cloneCard);
        popupCard.css({position: 'absolute', left: $.popup._oldCard.offset().left, top: $.popup._oldCard.offset().top});
        popupCard.appendTo('body');
        $.popup._cloneCard = popupCard;
        var targetPosition = $.getGoldPosition(popupCard.outerWidth(), popupCard.outerHeight());

        effectCard = [
            function() {
                $.popup._oldCard.css('visibility', 'hidden');
                $(".popup-wrapper").hide();

                popupCard.animate({top: targetPosition.top + $(window).scrollTop(), left: targetPosition.left + $(window).scrollLeft()}, 200, function() {
                    $(".popup-wrapper").addClass('popup-flip-container');
                     popupCard.addClass('popup-main-flip-card');
                    flipCon.append(popupCard);
                    $(".popup-wrapper").find('.popup-container').addClass('popup-main-flip-content').appendTo(flipCon);
                    $(".popup-wrapper").append(flipCon).show();
                    var _tTop = parseFloat($(".popup-wrapper").css('top'));
                    var _tLeft = parseFloat($(".popup-wrapper").css('left'));
                    popupCard.css({
                        top: targetPosition.top - _tTop,
                        left: targetPosition.left - _tLeft
                    });
                    _cardEffect();
                });
            },
            function() {
                //flipCon.addClass('active_flip');
                var flipCard = $(".popup-wrapper .popup-main-flip");
                $(".popup-wrapper").animate({transfrom3d: 180}, {step: function(now) {
                        var _t = 'rotateY(' + now + 'deg )';
                        flipCard.css({'-webkit-transform': _t, '-moz-transform' : _t, '-o-transform': _t, 'transform': _t});
                    },
                    duration: 1000
                });
                if (opts.overlay !== false) {
                    opts.overlay.animate = {
                        startDuration: 800,
                        endDuration: 400
                    };
                    $.overlay(opts.overlay);
                }
            }
        ];
    }

    var str = '<div class="popup-wrapper" id="popup-window">';
    str += '   <div class="popup-container">';
    str += '       <div class="popup-header">';
    str += '           <h3 class="popup-title"></h3>';
    str += '           <span class="popup-close"><i class="fa fa-close"></i></span>';
    str += '       </div>';
    str += '   <div class="popup-main">';
    //str += '       <div class="popup-message"></div>';
    str += '   </div>';
    str += '   <div class="popup-footer"></div>';
    str += '</div>';

    $("body").append(str);
    $(".popup-wrapper").css({position: 'fixed', left: '0', top: 0});

    if (opts.class_name !== null) {
        $(".popup-wrapper").addClass(opts.class_name);
    }

    // create title div
    if (opts.title !== null) {
        $(".popup-title").html(opts.title.length > 0 ? opts.title : '&nbsp;');
    } else {
        $(".popup-wrapper").find('.popup-title').remove();
    }

    if (opts.dragable === true) {
        $(".popup-wrapper").jqDrag('.popup-title');
        $(".popup-title").css({'cursor': 'move'});
    }

    if (opts.closable === true) {
        $(".popup-close").css('display', 'block');
    } else {
        $(".popup-close").remove();
    }

    // create content box
    $(".popup-main").html(elem);
    if (typeof elem === 'object') {
        elem.style.display = 'block';
    }

    if (effectCard.length) {
        $.popup._adjustPosition();
        $('body').queue('effectCard', effectCard);
        var _cardEffect = function() {
            $('body').dequeue('effectCard');
        };
        _cardEffect();
    } else {
        $.popup._adjustPosition();
        if (opts.overlay !== false) {
            $.overlay(opts.overlay);
        }
    }
    $.popup._init();
    $.popup._adjustPosition();
};

$.popup.defaults = {
    title: null,
    overlay: {},
    dragable: true,
    closable: true,
    onshow: null,
    onclose: null,
    cardObject: null,
    class_name: null
};

/**
 * CAL position
 *
 * @returns void|bolean
 */
$.popup._adjustPosition = function() {
    if ($(".popup-wrapper:visible").length < 1) {
        return false;
    }
    var popHeight = $(".popup-wrapper").outerHeight();
    var popWidth = $(".popup-wrapper").outerWidth();

    var t = $.getGoldPosition(popWidth, popHeight);
    $('.popup-wrapper').css({'top': t.top, 'left': t.left});
    if (popHeight > $(window).height()) {
        t.top = $(window).scrollTop();
        $('.popup-wrapper').css({'top': t.top, 'left': t.left, 'position': 'absolute'});
    } else {
        $('.popup-wrapper').css({'position': 'fixed'});
    }
    $(".popup-wrapper").find('img').each(function() {
        if ($(this).data('onload')) {
            return false;
        }
        $(this).data('onload', 1);
        var tt = new Image();
        tt.onload = function() {
            $.popup._adjustPosition();
        };
        tt.src = $(this).attr('src');
    });
};

/**
 * Bind event and call 'onshow' function when the popup window has been creating
 */
$.popup._init = function() {
    if ($(".popup-close").length > 0) {
        $('.popup-close').one('click', function() {
            $.popup.remove('close');
        });
        $("body").one('click', '#overlay', function() {
            $.popup.remove('close');
        });
    }
    if ($.isFunction($.popup._onshow)) {
        $.popup._onshow.call();
        $.popup._onshow = null;
    }
};

/**
 * Remove popWindow and call 'onclose' function
 *
 * @returns vodi
 */
$.popup.remove = function(noAnimate) {
    $(".popup-close").off();
    var onReadClose = function() {
        if ($.popup._isNeedSave) {
            $.popup._object.style.display = 'none';
            document.getElementsByTagName('body')[0].appendChild($.popup._object);
            $.popup._object = null;
        }
    }
    $('body').queue('effectCard', []);
    if ($.popup._cloneCard && $.browser.transform3D && noAnimate !== true) {
        var _cardEffect = [
            function() {
                var flipCard = $(".popup-wrapper .popup-main-flip");
                $(".popup-wrapper").animate({transfrom3d: 0}, {step: function(now) {
                        var _ts = 'rotateY(' + now + 'deg )';
                        flipCard.css({'-webkit-transform': _ts, '-moz-transform' : _ts, '-o-transform': _ts, 'transform': _ts});
                    },
                    duration: 400,
                    complete: function() {
                        _t();
                    }
                });
            },
            function() {
                var _tTop = parseFloat($(".popup-wrapper").css('top'));
                var _tLeft = parseFloat($(".popup-wrapper").css('left'));
                var _tCardTop = parseFloat($.popup._cloneCard.css('top'));
                var _tCardLeft = parseFloat($.popup._cloneCard.css('left'));
                $.popup._cloneCard.removeClass('popup-main-flip-card').appendTo('body').css({
                    left: _tLeft + $(window).scrollLeft() + _tCardLeft,
                    top: _tTop + _tCardTop + $(window).scrollTop()
                });
                onReadClose();
                $(".popup-wrapper").remove();
                $.overlay._remove();
                _t();
            },
            function() {
                $.popup._cloneCard.animate({
                    left: $.popup._oldCard.offset().left,
                    top: $.popup._oldCard.offset().top
                }, 400, function() {
                    $.popup._oldCard.css('visibility', 'visible');
                    $.popup._cloneCard.remove();
                    $.popup._cloneCard = null;
                    $.popup._oldCard = null;
                });
            }
        ];

        $('body').queue('effectCard', _cardEffect);
        var _t = function() {
            $('body').dequeue('effectCard');
        };
        _t();
    } else if ($.popup._cloneCard && $.browser.transform3D && noAnimate === true) {
        $.popup._cloneCard.remove();
        $.popup._oldCard.css('visibility', 'visible');
        $.popup._cloneCard = null;
        $.popup._oldCard = null;
        onReadClose();
        $(".popup-wrapper").remove();
    } else {
        $.overlay._remove(noAnimate);
        onReadClose();
        $(".popup-wrapper").remove();
        $.popup._cloneCard = null;
        $.popup._oldCard = null;
    }

    if ($.isFunction($.popup._onclose)) {
        $.popup._onclose.call();
        $.popup._onclose = null;
    }
};

/**
 * window.alert like function
 *
 * @param string msg It supports HTML.
 * @param Function submitHandler
 * @param PlainObject opts field(title = '', overlay = true, dragable = true, closable = true, submit = 'OK', focus = 'submit')
 * @return void
 * @example
 *	$.alert(msg, submitHandler);
 *	$.alert(msg, opts|title)
 *	$.alert(msg, submitHandler, opts|title)
 */
$.alert = function(msg, submitHandler, opts) {
    if (typeof submitHandler === 'string') {
        opts = $.extend({}, opts, {title: submitHandler});
    } else if (typeof submitHandler === 'object') {
        opts = submitHandler;
    }

    if (typeof opts === 'string') {
        opts = {title: opts};
    }
    opts = $.extend({}, $.alert.defaults, opts);
    msg = '<div class="popup-message">' + msg + '</div>';
    $.popup(msg, opts,
            function() {
                $('.popup-wrapper').attr('id', 'popup-alert');
                $('.popup-footer').html('<button class="popup-button-submit button">' + opts.submit + '</button');
                $(".popup-footer").find(".popup-button-submit").focus();
                $(".popup-button-submit").one('click', function() {
                    $.popup.remove();
                });
            },
            function() {
                $(".popup-button-submit").off();
                if ($.isFunction(submitHandler)) {
                    submitHandler.call();
                }
            }
    );
};

$.alert.defaults = {
    title: '',
    overlay: {},
    dragable: true,
    closable: false,
    submit: 'Yes',
    focus: 'submit'
};

/**
 * window.confirm like function
 *
 * @param string msg It supports HTML.
 * @param Function submitHandler
 * @param Function cancelHandler
 * @param PlainObject opts field(title = '', overlay = true, dragable = true, closable = true, submit = 'Submit', cancel = 'Cancel', focus = 'cancel')
 * @return void
 * @example
 *	$.confirm(msg, submitHandler);
 *	$.confirm(msg, submitHandler, cancelHandler);
 *	$.confirm(msg, submitHandler, cancelHandler, opts);
 *	$.confirm(msg, submitHandler, opts);
 *	$.confirm(msg, submitHandler, title);
 */
$.confirm = function(msg, submitHandler, cancelHandler, opts) {
    if (typeof cancelHandler !== 'function') {
        if (typeof cancelHandler === 'string') {
            opts = {title: cancelHandler};
        } else if (typeof cancelHandler === 'object') {
            opts = cancelHandler;
        }
    }
    if (typeof opts === 'string') {
        opts = {title: opts};
    }

    opts = $.extend({}, $.confirm.defaults, opts);
    msg = '<div class="popup-message">' + msg + '<div>';
    $.popup(msg, opts,
            function() {
                $('.popup-wrapper').attr('id', 'popup-confirm');
                $(".popup-footer").html('<button class="popup-button-submit button">' + opts.submit + '</button><button class="popup-button-cancel button">' + opts.cancel + '</button>');
                $(".popup-footer").find(".popup-button-cancel").focus();
                $(".popup-button-submit").one('click', function() {
                    $.popup.remove();
                    if ($.isFunction(submitHandler)) {
                        submitHandler.call();
                    }
                });
                $(".popup-button-cancel").one('click', function() {
                    $.popup.remove();
                    if ($.isFunction(cancelHandler)) {
                        cancelHandler.call();
                    }
                });
                $(".popup-close").one('click', function() {
                    if ($.isFunction(cancelHandler)) {
                        cancelHandler.call();
                    }
                });
            },
            function() {
                $(".popup-button-submit").off();
                $(".popup-button-cancel").off();
                $(".popup-close").off();
            }
    );
};

$.confirm.defaults = {
    title: '',
    overlay: {},
    dragable: true,
    closable: false,
    submit: '是',
    cancel: '否',
    focus: 'cancel'
};

/**
 * Extend to jQuery.confirm
 *
 * @param string msg It supports HTML.
 * @param Function yesHandler
 * @param Function noHandler
 * @param Function cancelHandler
 * @param PlainObject opts field(title = '', overlay = true, dragable = true, closable = true, yes = 'Yes', no = 'No', cancel = 'Cancel', focus = 'cancel')
 * @return void
 * @example
 *	$.confirms(msg, yesHandler);
 *	$.confirms(msg, yesHandler, noHandler);
 *	$.confirms(msg, yesHandler, noHandler, cancelHandler);
 *	$.confirms(msg, yesHandler, noHandler, cancelHandler, opts);
 *	$.confirms(msg, yesHandler, opts);
 *	$.confirms(msg, yesHandler, title);
 */
$.confirms = function(msg, yesHandler, noHandler, cancelHandler, opts) {
    if (typeof noHandler !== 'function' && noHandler !== null) {
        if (typeof noHandler === 'string') {
            opts = {title: noHandler};
        } else if (typeof noHandler === 'object') {
            opts = noHandler;
        }
    }

    if (typeof cancelHandler !== 'function' && cancelHandler !== null) {
        if (typeof cancelHandler === 'string') {
            opts = {title: noHandler};
        } else if (typeof cancelHandler === 'object') {
            opts = cancelHandler;
        }
    }

    if (typeof opts === 'string') {
        opts = {title: opts};
    }

    opts = $.extend({}, $.confirms.defaults, opts);
    msg = '<div class="popup-message">' + msg + '<div>';

    $.popup(msg, opts,
            function() {
                var str = '';
                str = '<button class="popup-button-yes button">' + opts.yes + '</button>';
                str += '<button class="popup-button-no button">' + opts.no + '</button>';
                str += '<button class="popup-button-cancel button">' + opts.cancel + '</button>';
                $(".popup-footer").html(str);
                $(".popup-footer").find(".popup-button-" + opts.focus).focus();
                $('.popup-button-yes').one('click', function() {
                    $.popup.remove();
                    $.isFunction(yesHandler) && yesHandler.call();
                });
                $('.popup-button-no').one('click', function() {
                    $.popup.remove();
                    $.isFunction(noHandler) && noHandler.call();
                });
                $(".popup-button-cancel").one('click', function() {
                    $.popup.remove();
                    $.isFunction(cancelHandler) && cancelHandler.call();
                });
                $(".popup-close").one('click', function() {
                    $.isFunction(cancelHandler) && cancelHandler.call();
                });
            },
            function() {
                $('.popup-button-yes').off();
                $('.popup-button-no').off();
                $('.popup-button-cancel').off();
                $('.popup-close').off();
            }
    );
};

$.confirms.defaults = {
    title: '',
    overlay: {},
    dragable: true,
    closable: false,
    yes: 'Yes',
    no: 'No',
    cancel: 'Cancel',
    focus: 'cancel'
};

/**
 * popup status tip
 *
 * @param string msg
 * @param float closable Default value is true
 * @return void
 * @example
 *	$.status('Loading...');
 *	$.status('Operate successfully', true);
 */
$.status = function(msg, closable) {
    if ($("#popup-status").length > 0) {
        $.status.remove(function() {
            $.status(msg, closable);
        });
        return false;
    }
    if (typeof closable === 'boolean') {
        $.status.closable = closable;
    }
    var str = '<div class="tip-wrapper" id="popup-status">';
    str += '<div class="tip-container">';
    str += '<div class="tip-message"></div>';
    str += '<span class="tip-close">Close</span>';
    str += '</div></div>';
    $("body").append(str);
    $("#popup-status .tip-message").html(msg);
    $("#popup-status").css({top: '-' + ($("#popup-status").outerHeight() + 2) + 'px', left: '60%', position: 'fixed'});
    if ($.status.closable === true) {
        $("#popup-status").addClass('tip-closable-yes');
        $("#popup-status .tip-close").one('click', function() {
            $.status.remove();
        });
    } else {
        $("#popup-status").addClass('tip-closable-no');
        $("#popup-status .tip-close").remove();
    }
    $.status._adjustPosition();
    $("#popup-status").animate({top: 0}, 200);
};

$.status.closable = true;

$.status._adjustPosition = function() {
    if ($("#popup-status").length < 1) {
        return false;
    }
    var popWidth = $("#popup-status").outerWidth();
    var windowWidth = $(window).width();

    var leftPosition = (windowWidth - popWidth) / 2;

    // fix left hide
    if (leftPosition < 0) {
        leftPosition = 0;
    }

    $('#popup-status').css({left: leftPosition});
};

/**
 * remove the status tip
 *
 * @param Function callback
 * @return void
 */
$.status.remove = function(callback) {
    $("#popup-status").stop(true);
    $("#popup-status").animate({height: '0'}, 200, null, function() {
        $("#popup-status .tip-close").off();
        $("#popup-status").remove();
        $.isFunction(callback) && callback.call();
    });
};

/**
 * popup warning tip
 *
 * @param string msg
 * @param float delay Default value is 5
 */
$.warning = function(msg, delay) {
    if ($.warning.autoClose) {
        clearTimeout($.warning.autoClose);
    }
    if ($("#popup-warning").length > 0) {
        $.warning.remove(function() {
            $.warning(msg, delay);
        });
        return false;
    }

    if (parseFloat(delay) > 0) {
        $.warning.delay = parseFloat(delay);
    } else if (typeof delay !== 'undefined') {
        $.warning.delay = 0;
    }
    var str = '<div class="tip-wrapper" id="popup-warning">';
    str += '<div class="tip-container">';
    str += '<div class="tip-message"></div>';
    str += '</div></div>';
    $("body").append(str);
    $("#popup-warning .tip-message").html(msg);
    $("#popup-warning").css({top: '-' + ($("#popup-warning").outerHeight() + 2) + 'px', right: 0, position: 'fixed'});
    $("#popup-warning").animate({top: '0'}, 200);
    if ($.warning.delay > 0) {
        $.warning.autoClose = setTimeout(function() {
            $.warning.remove();
        }, $.warning.delay * 1000);
    }
};

$.warning.delay = 5;

$.warning.remove = function(callback) {
    $("#popup-warning").stop(true);
    $("#popup-warning").animate({height: '0'}, 100, null, function() {
        $("#popup-warning").remove();
        $.isFunction(callback) && callback.call();
    });
};

/**
 * Get div gold position
 *
 * @param float _width
 * @param float _height
 * @returns json;
 */
$.getGoldPosition = function(_width, _height) {
    var t = {
        left: 0,
        top: 0
    };
    var windowHeight = $(window).height();
    var windowWidth = $(window).width();

    var topPosition = ((windowHeight- _height) * 0.382);
    var leftPosition = (windowWidth - _width) / 2;

    // fix top or left hide
    if (topPosition < 0) {
        topPosition = (windowHeight - _height) / 3;
    }

    // fix top or left hide
    if (topPosition < 0) {
        topPosition = 0;
    }
    if (leftPosition < 0) {
        leftPosition = 0;
    }

    // fix right or bottom hide
    var hideHeight = (topPosition + _height) - windowHeight;
    if (topPosition > 0 && hideHeight > 0) {
        topPosition = (topPosition - hideHeight) / 2;
    }

    t.left = leftPosition;
    t.top = topPosition;

    return t;
};

$(document).ready(function() {
    $(window).resize(function() {
        $.popup._adjustPosition();
        $.status._adjustPosition();
    });

    // press ESC button
    $(document).keydown(function(event) {
        if (event.keyCode === 27 && $(".popup-close:visible").length > 0) {
            $(".popup-close").trigger('click');
        }
    });
});
