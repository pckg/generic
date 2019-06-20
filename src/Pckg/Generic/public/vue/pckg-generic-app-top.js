/**
 * Register main Vue event dispatcher.
 * Dispatcher is shared with parent window so we transmit all events between iframes and host.
 *
 * @type {Vue}
 */
const $dispatcher = window === window.top || window.location.hostname !== window.parent.location.hostname
    ? (new Vue())
    : window.parent._$dispatcher;

/**
 * Allow iframes to have access to same dispatcher.
 */
if (window === window.top) {
    window._$dispatcher = $dispatcher;
}

var $scroller = new Vue({
    methods: {
        onScroll: function (e) {
            $scroller.$emit('scroll', e);
        }
    },
    created: function () {
        $(window).on('scroll', this.onScroll);
    }
});

var data = data || {};

var props = props || {};

const pckgDelimiters = {
    delimiters: ['${', '}']
};

const dynamicEvents = {
    created: function () {
        $.each(this.triggers, function (method, events) {
            $.each(Array.isArray(events) ? events : [events], function (i, event) {
                this.$parent._data.localBus.$on(event, this[method]);
            }.bind(this));
        }.bind(this));
    },
    beforeDestroy: function () {
        $.each(this.triggers, function (method, events) {
            $.each(Array.isArray(events) ? events : [events], function (i, event) {
                this.$parent._data.localBus.$off(event, this[method]);
            }.bind(this));
        }.bind(this));
    },
};

const pckgCdn = {
    methods: {
        cdn: function (file) {
            if (!file) {
                return file;
            }

            if (file.indexOf('http://') === 0 || file.indexOf('https://') === 0 || file.indexOf('//') === 0) {
                return file;
            }

            if (!Pckg || !Pckg.config || !Pckg.config.cdn || !Pckg.config.cdn.host) {
                return file;
            }

            return 'https://' + Pckg.config.cdn.host + file;
        },
        imageCache: function (pic, type, arg) {
            return pic && pic.length > 0
                ? '/cache/img/' + type + '/' + arg + pic
                : null;
        },
        mediaImage: function (pic, folder) {
            return pic && pic.length > 0
                ? '/storage/uploads/' + (folder ? folder + '/' : '') + pic
                : null;
        }
    }
};

const pckgTranslations = {
    methods: {
        __: function (key, data) {
            var translation = $store.state.translations[key] || null;

            if (Object.keys($store.state.translations).indexOf(key) === -1) {
                if ($store && $store.getters.isAdmin) {
                    return '__' + key;
                }

                return key.split('.').reverse()[0];
            }

            if (!data) {
                return translation;
            }

            $.each(data, function (key, val) {
                translation = translation.replace('{{ ' + key + ' }}', val);
            });

            return translation;
        }
    }
};

const pckgFormValidator = {
    methods: {
        validateAndSubmit: function (submit, invalid) {
            this.$validator.validateAll().then(function (ok) {
                if (ok) {
                    submit();
                    return;
                }

                var element = $(this.$el).find('.htmlbuilder-validator-error').first();
                if (element && typeof globalScrollTo == Function) {
                    globalScrollTo(element);
                }
                if (invalid) {
                    invalid();
                }
            }.bind(this));
        },
        hydrateErrorResponse: function (response) {
            /**
             * Clear existing errors.
             */
            this.errors.clear();

            /**
             * Skip if no JSON response.
             */
            if (!response.responseJSON) {
                return;
            }

            /**
             * Populate errors.
             */
            $.each(response.responseJSON.descriptions || [], function (name, message) {
                this.errors.remove(name);
                this.errors.add({field: name, msg: message});
            }.bind(this));
        }
    }
};

const pckgSync = {
    methods: {
        single: function (name, request) {
            if (typeof object == 'undefined') {
                object = this;
            }

            if (object['_pckgSync' + name]) {
                object['_pckgSync' + name].abort();
            }

            object['_pckgSync' + name] = request;
        }
    }
};

const pckgLocale = {
    methods: {
        locale: function () {
            return locale;
        }
    }
};

const pckgTimeout = {
    methods: {
        timeout: function (name, callback, timeout, object) {
            if (typeof object == 'undefined') {
                object = this;
            }

            this.removeTimeout(name, object);

            this.setTimeout(name, callback, timeout, object);
        },
        removeTimeout: function (name, object) {
            if (typeof object == 'undefined') {
                object = this;
            }

            if (object['_pckgTimeout' + name]) {
                clearTimeout(this['_pckgTimeout' + name]);
            }
        },
        setTimeout: function (name, callback, timeout, object) {
            if (typeof object == 'undefined') {
                object = this;
            }

            object['_pckgTimeout' + name] = setTimeout(callback, timeout);
        }
    }
};

const pckgInterval = {
    methods: {
        interval: function (name, callback, interval, object) {
            if (typeof object == 'undefined') {
                object = this;
            }

            this.removeInterval(name, object);

            this.setInterval(name, callback, interval, object);
        },
        removeInterval: function (name, object) {
            if (object['_pckgInterval' + name]) {
                clearInterval(this['_pckgInterval' + name]);
            }
        },
        setInterval: function (name, callback, interval, object) {
            object['_pckgInterval' + name] = setInterval(callback, interval);
        }
    }
};

const pckgCleanRequest = {
    methods: {
        cleanRequest: function (callback, name) {
            if (this['_pckgCleanRequest' + name]) {
                this['_pckgCleanRequest' + name].abort();
            }

            this['_pckgCleanRequest' + name] = callback();
        }
    }
};

const pckgNativeEvents = {
    methods: {
        onNative: function (element, event, callback) {
            $(element).on(event, callback.bind(this));
        },
        offNative: function (element, callback) {
            $(element).off(callback);
        }
    }
};
