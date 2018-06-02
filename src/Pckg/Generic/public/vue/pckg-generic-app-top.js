/**
 * Register main Vue event dispatcher
 * @type {Vue}
 */
var $dispatcher = new Vue();

var data = data || {};

var props = props || {};

var pckgDelimiters = {
    delimiters: ['${', '}']
};

var pckgTranslations = {
    methods: {
        __: function (key, data) {
            var translation = $store.state.translations[key];

            if (!data) {
                return translation;
            }

            $.each(data, function(key, val){
                translation = translation.replace('{{ ' + key + ' }}', val);
            });

            return translation;
        }
    }
};

var pckgFormValidator = {
    methods: {
        validateAndSubmit: function (submit, invalid) {
            console.log('validating');
            this.$validator.validateAll().then(function (ok) {
                if (ok) {
                    console.log('ok');
                    submit();
                } else {
                    console.log('error', ok);
                    var element = $(this.$el).find('.htmlbuilder-validator-error').first();
                    globalScrollTo(element);
                    if (invalid) {
                        invalid();
                    }
                }
            }.bind(this));
        }
    }
};

var pckgSync = {
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

var pckgTimeout = {
    methods: {
        timeout: function (name, callback, timeout, object) {
            if (typeof object == 'undefined') {
                object = this;
            }

            this.removeTimeout(name, object);

            this.setTimeout(name, callback, timeout, object);
        },
        removeTimeout: function (name, object) {
            if (object['_pckgTimeout' + name]) {
                clearTimeout(this['_pckgTimeout' + name]);
            }
        },
        setTimeout: function (name, callback, timeout, object) {
            object['_pckgTimeout' + name] = setTimeout(callback, timeout);
        }
    }
};

var pckgInterval = {
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

var pckgCleanRequest = {
    methods: {
        cleanRequest: function (callback, name) {
            if (this['_pckgCleanRequest' + name]) {
                this['_pckgCleanRequest' + name].abort();
            }

            this['_pckgCleanRequest' + name] = callback();
        }
    }
};