Vue.filter('number', function (price) {
    return parseInt(price) == parseFloat(price) ? parseInt(price) : parseFloat(price);
});

Vue.filter('price', function (price, decimals) {
    return locale.price(price, decimals);
});

Vue.filter('roundPrice', function (price, decimals) {
    return locale.roundPrice(price, decimals);
});

Vue.filter('number', function (price, decimals) {
    return locale.number(price, decimals);
});

Vue.filter('roundNumber', function (price, decimals) {
    return parseInt(price) == parseFloat(price) ? parseInt(price) : parseFloat(price).toFixed(decimals || 2);
});

Vue.filter('date', function (date, format) {
    return locale.date(date, format);
});

Vue.filter('time', function (date, format) {
    return locale.time(date, format);
});

Vue.filter('datetime', function (date, format) {
    return locale.datetime(date, format);
});

Vue.filter('timespan', function (timespan) {
    let format = timespan.split(' ')[1] || null;

    if (!format) {
        return;
    }

    let num = parseInt(timespan.split(' ')[0]);
    let duration = moment.duration(num, format);

    let mapper = {
        minute: 'mm',
        hour: 'hh',
        day: 'dd'
    };

    let f = null;
    $.each(mapper, function (k, v) {
        if (format.indexOf(k) >= 0) {
            f = v;
            return false;
        }
    });

    if (!f) {
        return;
    }

    return moment.localeData().relativeTime(num, true, f, false);
});

Vue.filter('ucfirst', function (string) {
    return utils.ucfirst(string);
});

Vue.filter('nl2br', function (string) {
    if (!string) {
        return '';
    }

    return utils.nl2br(nl2br);
});

Vue.filter('html2text', function (html) {
    if (!html) {
        return '';
    }

    let span = document.createElement('span');
    span.innerHTML = html;
    return span.textContent || span.innerText;
});

Vue.directive('outer-click', {
    bind: function (el, binding, vnode) {
        $dispatcher.$on('body:click', function (e) {
            if ($(e.target).closest(el).is($(el))) {
                return;
            }

            binding.value(e);
        });
    },
    unbind: function (el, binding, vnode) {
        $dispatcher.$off('body:click', function (e) {
            if ($(e.target).closest(el).is($(el))) {
                return;
            }

            binding.value(e);
        });
    }
});

Vue.directive('popup-image', {
    bind: function (el) {
        $(el).magnificPopup({type: 'image'});
    }
});

Vue.directive('router-link', {
    bind: function (el, binding, vnode) {

        return;
        el.addEventListener('click', function ($event) {
            let path = '/' + el.href.split('/').slice(3).join('/');
            let managed = false;

            /**
             * Check if we can render router-view
             */
            if (true || vnode.context.$router.currentRoute.name) {
                $.each(vnode.context.$router.options.routes, function (i, route) {
                    if (route.path !== path) {
                        return;
                    }

                    vnode.context.$router.push({path: path});
                    $event.preventDefault();
                    $event.stopPropagation();
                    managed = true;
                    return false;
                });
            }

            return !managed;
        });

    }
});
