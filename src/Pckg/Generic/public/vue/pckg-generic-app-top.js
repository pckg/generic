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

var pckgPlatformSettings = {
    data: function () {
        return {
            form: {}
        };
    },
    methods: {
        submitForm: function () {
            this.validateAndSubmit(function () {
                http.post($(this.$el).find('form').attr('action'), this.form, function (data) {
                    $dispatcher.$emit('notification:' + (data.success ? 'success' : 'error'), data.message || (data.success ? 'Settings saved' : 'General error'));
                }, function (response) {
                    http.postError(response);

                    $.each(response.responseJSON.descriptions || [], function (name, message) {
                        this.errors.remove(name);
                        this.errors.add(name, message);
                    }.bind(this));
                }.bind(this));
            }.bind(this));
        }
    },
    created: function () {
        this.initialFetch();
    }
};

var pckgCdn = {
    methods: {
        cdn: function (file) {
            if (!file) {
                return file;
            }

            if (!Pckg || !Pckg.config || !Pckg.config.cdn || !Pckg.config.cdn.host) {
                return file;
            }

            return 'https://' + Pckg.config.cdn.host + file;
        }
    }
};

var pckgTranslations = {
    methods: {
        __: function (key, data) {
            var translation = $store.state.translations[key] || null;

            if (!translation) {
                return key;
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

var pckgPayment = {
    mixins: [pckgTranslations],
    props: {
        instalments: {
            type: Array
        },
        handler: {
            type: String
        }
    },
    data: function () {
        return {
            formAction: '',
            state: null
        };
    },
    methods: {
        handleSuccessResponse: function (data) {
            var t = this;
            if (data.redirect) {
                t.state = 'redirected';
                $.magnificPopup.open({
                    items: {
                        src: data.redirect,
                        type: 'iframe'
                    }
                });
                $.magnificPopup.instance.close = function () {
                    if (!confirm("Do you want to cancel payment?")) {
                        return;
                    }

                    t.state = 'canceled';
                    $dispatcher.$emit('payment-form:canceled');
                    $.magnificPopup.proto.close.call(this);
                };
            } else if (data.modal) {
                t.state = data.modal;
                $dispatcher.$emit('payment-form:' + data.modal, data.message);
            } else if (!data.success) {
                t.state = 'error';
                $dispatcher.$emit('payment-form:error', data.message);
            }
        },
        handleErrorResponse: function (data) {
            this.state = 'error';
            $dispatcher.$emit('payment-form:error', 'Unknown error');
        },
        submitForm: function (data) {
            this.state = 'validating';
            $dispatcher.$emit('payment-form:validating');
            http.post(this.formAction, this.collectFormData(data), pckgPayment.methods.handleSuccessResponse, pckgPayment.methods.handleErrorResponse);
        },
        collectFormData: function () {
            return {};
        },
        preFetch: {},
        initialFetch: function () {
            this.state = 'fetching';
            http.post(utils.url('@api.payment.init', { handler: this.handler }), {
                instalments: this.instalments.map(function (instalment) {
                    return instalment.id;
                })
            }, function (data) {
                this.formAction = data.formAction;

                this.afterFetch(data);
                this.state = 'fetched';
            }.bind(this));
        },
        afterFetch: function (data) {
        }
    },
    created: function(){
        this.preFetch();
        this.initialFetch();
    }
};

var pckgFormValidator = {
    methods: {
        validateAndSubmit: function (submit, invalid) {
            console.log('validating');
            this.$validator.validateAll().then(function (ok) {
                if (ok) {
                    console.log('form valid');
                    submit();
                    return;
                }

                console.log('form invalid', ok);
                var element = $(this.$el).find('.htmlbuilder-validator-error').first();
                if (element && typeof globalScrollTo == Function) {
                    globalScrollTo(element);
                }
                if (invalid) {
                    invalid();
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