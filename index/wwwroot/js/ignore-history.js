window.onload = function () {
    //set url
    var url = {
        getInfo: function (url) {
            var params = {};
            url = url.split("?");
            if (url[1] != undefined) {
                $(url[1].split("&")).each(function (key, value) {
                    value = value.split("=");
                    if (value[1] != undefined) {
                        params[value[0]] = value[1];
                    } else {
                        params[value[0]] = "";
                    }
                });
            }

            return {
                'host': url[0],
                'params': params,
            };
        },
        getUrl: function (urlInfo) {
            var url = "";

            if (urlInfo.host != undefined) {
                url += urlInfo.host;
            }

            if (urlInfo.params != undefined) {
                url += "?";
                for(key in urlInfo.params) {
                    if (!/\?$/.test(url)) {
                        url += "&";
                    }
                    url += key + "=" + urlInfo.params[key];
                }
            }

            return url;
        }
    };

    $('#js-submit-ignore').submit(function (event) {
        if (event && event.preventDefault) {
            event.preventDefault();
        }

        var link = window.location.href;
        var linkInfo = url.getInfo(link);

        $(this).find('select, input').each(function () {
            linkInfo.params[this.name] = this.value;
        });

        link = url.getUrl(linkInfo);

        if (link && /^#|javascript/i.test(link) === false) {
            if (history.replaceState) {
                history.replaceState(null, document.title, link.split('#')[0] + '#');
                location.replace('');
            } else {
                location.replace(link);
            }
        }

        return false;
    });

    $('a, .js-button-igonre').click(function (event) {
        if (event && event.preventDefault) {
            event.preventDefault();
        }

        var link = this.href;

        if (link && /^#|javascript/i.test(link) === false) {
            if (history.replaceState) {
                history.replaceState(null, document.title, link.split('#')[0] + '#');
                location.replace('');
            } else {
                location.replace(link);
            }
        }

        return false;
    });
}