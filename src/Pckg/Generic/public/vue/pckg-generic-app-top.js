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
            this.validateAndSubmit(function(){
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
        cdn: function(file) {
            if (!file) {
                return file;
            }

            if (!Pckg || !Pckg.config || !Pckg.config.cdn || !Pckg.config.cdn.host) {
                return file;
            }

            return 'https://' + Pckg.config.cdn.host + file;
        },
        imageCache: function(pic, type, arg) {
            return pic && pic.length > 0
                ? '/cache/img/' + type + '/' + arg + pic
                : null;
        },
        mediaImage: function(pic, folder) {
            return pic && pic.length > 0
                ? '/storage/uploads/' + folder + '/' + pic
                : null;
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
                    return;
                }

                console.log('error', ok);
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

var pckgSmartList = {
    mixins: [pckgTranslations, pckgCdn],
    props: {
        contents: {
            type: Array
        },
        action: {
            type: Object,
            required: true
        }
    },
    data: function () {
        return {
            itemComponent: 'derive-item'
        };
    },
    mounted: function () {
        $dispatcher.$on('pckg-action:' + this.action.id + ':itemTemplate-changed', function (newTemplate) {
            this.itemComponent = newTemplate;
        }.bind(this));
    }
};

var pckgSmartItem = {
    mixins: [pckgTranslations, pckgCdn],
    props: {
        content: {},
        action: {},
    },
    data: function () {
        return {
            templateRender: null,
            tpl: 'derive-item'
        };
    },
    render: function (h) {
        console.log('rendering derive item');
        if (!this.templateRender) {
            console.log('no render');
            if (this.$options.template) {
                console.log('but option');
                return this.$options.template;
            }

            return h('div', 'Loading ...');
        }

        return this.templateRender();
    },
    mounted: function () {
        /**
         * This is code that listens for event to be triggered from page builder.
         */
        $dispatcher.$on('pckg-action:' + this.action.id + ':template-changed', function (newTemplate) {
            console.log('changed', newTemplate);
            this.tpl = newTemplate;
        }.bind(this));
    },
    watch: {
        tpl: {
            immediate: true,
            handler: function (newVal, oldVal) {
                return;
                console.log('resolving template', newVal, oldVal, this.$options.template);
                let template = $store.getters.resolveTemplate(newVal, this.$options.template);

                console.log('resolved template', template, typeof template);
                let res = typeof template === 'string' ? Vue.compile(template) : template;

                this.templateRender = res.render;

                // staticRenderFns belong into $options,
                // appearantly

                this.$options.staticRenderFns = [];

                console.log('res', res);
                // clean the cache of static elements
                // this is a cache of the results from the staticRenderFns
                this._staticTrees = [];

                // Fill it with the new staticRenderFns
                if (res.staticRenderFns) {
                    for (var i in res.staticRenderFns) {
                        console.log('staticRenderFns', i);
                        //staticRenderFns.push(res.staticRenderFns[i]);
                        this.$options.staticRenderFns.push(res.staticRenderFns[i]);
                    }
                }
            }
        }
    }
};
