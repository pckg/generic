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

const pckgPlatformSettings = {
    data: function () {
        return {
            form: {},
            loading: false
        };
    },
    methods: {
        submitForm: function () {
            this.validateAndSubmit(function () {
                http.post($(this.$el).find('form').attr('action'), this.form, function (data) {
                    $dispatcher.$emit('notification:' + (data.success ? 'success' : 'error'), data.message || (data.success ? 'Settings saved, some changes require page refresh' : 'General error'));
                }, function (response) {
                    http.postError(response);

                    $.each(response.responseJSON.descriptions || [], function (name, message) {
                        this.errors.remove(name);
                        this.errors.add({field: name, msg: message});
                    }.bind(this));
                }.bind(this));
            }.bind(this));
        }
    },
    created: function () {
        if (this.initialFetch) {
            this.initialFetch();
        }
    }
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

            if (!translation) {
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

const pckgPaymentConfig = {
    props: {
        paymentMethod: {
            type: Object,
            required: true
        },
        company: {
            type: Object,
            required: true
        }
    },
    data: function () {
        return {
            myPaymentMethod: this.paymentMethod,
            myCompany: this.company,
        };
    },
    methods: {
        saveSettings: function () {
            http.post('/api/payment-methods/' + this.myPaymentMethod.key + '/companies/' + this.myCompany.id + '/settings', this.collectSettings(), function () {
                $dispatcher.$emit('notification:success', 'Settings saved');
            }.bind(this), function (response) {
                let errors = response.responseJSON.descriptions || [];

                $dispatcher.$emit('notification:error', 'Error saving settings');

                if (errors.length == 0) {
                    return;
                }

                $.each(errors, function (name, message) {
                    this.errors.remove(name);
                    this.errors.add({field: name, msg: message});
                }.bind(this));
            }.bind(this));
        },
        initialFetch: function () {
            http.getJSON('/api/payment-methods/' + this.myPaymentMethod.key + '/companies/' + this.myCompany.id + '/settings', function (data) {
                this.myPaymentMethod = data.paymentMethod;
            }.bind(this));
        }
    },
    watch: {
        paymentMethod: function (paymentMethod) {
            this.myPaymentMethod = paymentMethod;
        },
        company: function (company) {
            this.myCompany = company;
        },
        myCompany: function () {
            this.initialFetch();
        }
    },
    created: function () {
        this.initialFetch();
    }
};

const pckgPayment = {
    mixins: [pckgTranslations],
    props: {
        instalments: {
            type: Array
        },
        handler: {
            type: String
        },
        orders: {
            type: Array
        }
    },
    data: function () {
        let user = this.orders.length > 0 ? this.orders[0].user : {};

        return {
            formAction: '',
            state: null,
            error: null,
            handlerData: {},
            formData: {},
            myOrders: this.orders,
            myUser: user,
            mode: this.getMode(user),
            substep: null
        };
    },
    watch: {
        orders: {
            immediately: true,
            handler: function (newVal) {
                this.myOrders = newVal;
                this.myUser = newVal.length > 0 ? newVal.user : {};
                this.mode = this.getMode(this.myUser);
            }
        }
    },
    methods: {
        getMode: function (user) {
            return !user.name || user.name.length == 0 || !user.surname || user.surname.length == 0
                ? 'saveInfo'
                : 'pay';
        },
        handleSuccessResponse: function (data) {
            var t = this;
            if (data.redirect) {
                t.state = 'redirected';
                http.redirect(data.redirect);
                /*
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
                };*/
            } else if (data.modal) {
                t.state = data.modal;
                $dispatcher.$emit('payment-form:' + data.modal, data);
            } else if (!data.success) {
                t.state = 'error';
                $dispatcher.$emit('payment-form:error', data);
            }
            $dispatcher.$emit('payment-form:refresh-data');
        },
        handleErrorResponse: function (data) {
            this.state = 'error';
            $dispatcher.$emit('payment-form:error', 'Unknown error');
            $dispatcher.$emit('payment-form:refresh-data');
        },
        submitForm: function (data) {
            this.state = 'validating';
            $dispatcher.$emit('payment-form:validating');
            http.post(this.formAction, this.collectFormData(data), pckgPayment.methods.handleSuccessResponse, pckgPayment.methods.handleErrorResponse);
        },
        collectFormData: function () {
            return this.formData;
        },
        preFetch: function () {
        },
        initialFetch: function () {
            this.state = 'fetching';
            http.post(utils.url('@api.payment.init', {handler: this.handler}), {
                instalments: this.instalments.map(function (instalment) {
                    return instalment.id;
                })
            }, function (data) {
                this.formAction = data.formAction;
                this.formData = data.handlerData.formData;
                this.handlerData = data.handlerData;

                this.afterFetch(data);
                this.state = 'fetched';
                this.afterFetched(data);
            }.bind(this), function (response) {
                this.state = 'error';
                this.error = 'Error initializing payment';
                if (response.responseJSON && response.responseJSON.message) {
                    this.error = response.responseJSON.message;
                }
            }.bind(this));
        },
        afterFetch: function (data) {
        },
        afterFetched: function (data) {
        }
    },
    created: function () {
        this.preFetch();
        this.initialFetch();
    },
    computed: {
        total: function () {
            return this.instalments.reduce(function (sum, instalment) {
                return sum + instalment.price;
            }, 0.0);
        },
        order: function () {
            return this.myOrders[0] || null;
        }
    }
};

const pckgActionAnimation = {
    watch: {
        'action.settings.animation.effect': function (newVal) {
            /**
             * @T00D00 - remove all other effects - to get effect in effect :D
             */
            this.makeEffect(newVal);
        },
        'action.settings.animation.speed': function (newVal) {
            this.$el.classList.remove('slow', 'slower', 'fast', 'faster');
        }
    },
    methods: {
        makeEffect: function (effect, delay, speed) {
            this.$set(this.shared, 'animationStarted', true);
            this.$el.classList.add('animated', effect, delay || 'no-delay', speed || 'normal-speed');
            this.$el.classList.remove('animated-out');
        },
        onScroll: function () {
            if (this.shared.animationStarted) {
                return;
            }

            let topScroll = parseInt($(window).scrollTop());
            let topOffset = parseInt($(this.$el).offset().top);
            let clientHeight = document.documentElement.clientHeight;
            let perc = this.animationSettings.threshold || 80;

            if (topOffset > topScroll + (clientHeight * perc / 100)) {
                return;
            }

            if (this.shared.animationStarted) {
                return;
            }

            this.makeEffect(this.animationSettings.effect, this.animationSettings.delay, this.animationSettings.speed);
        },
        prepareAnimationSettings: function (defaults) {
            if (!defaults) {
                defaults = {};
            }
            let defs = {
                event: null,
                effect: null,
                delay: null,
                infinite: false,
                threshold: 80
            };
            $.each(defs, function (k, v) {
                if (Object.keys(defaults).indexOf(k) >= 0) {
                    return;
                }

                defaults[k] = v;
            });
            return defaults;
        }
    },
    computed: {
        animationSettings: function () {
            return this.prepareAnimationSettings(this.action ? (this.action.settings.animation || {}) : {});
        },
    },
    mounted: function () {
        let settings = this.animationSettings;

        if (!settings || !settings.event || !settings.effect) {
            return;
        }

        this.$el.classList.add('animated-out');

        $scroller.$on('scroll', this.onScroll);
        this.onScroll();
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

const pckgActionbuilderSettings = {
    mixins: [pckgFormValidator],
    props: {
        action: {
            type: Object,
            required: true
        }
    },
    methods: {
        config: function (key, def) {
            return $store.getters.config(key, def);
        },
        initialFetch: function () {
        }
    },
    created: function () {
        this.initialFetch();
    }
};

const pckgGoogleRecaptcha = {
    methods: {
        collectGoogleRecaptcha: function (data) {
            let $element = $(this.$el).find('.g-recaptcha-response');
            data = data || this.form;

            if ($element.length == 1) {
                data['g-recaptcha-response'] = $element.val();
            }

            return data;
        },
        recaptchaLoaded: function () {
            $(this.$el).find('[type=submit]').addClass('hidden');
        },
        recaptchaSolved: function () {
            $(this.$el).find('[type=submit]').removeClass('hidden');
        }
    },
    created: function () {
        $dispatcher.$on('form:google-recaptcha:loaded', this.recaptchaLoaded);
        $dispatcher.$on('form:google-recaptcha:solved', this.recaptchaSolved);
    },
    beforeDestroy: function () {
        $dispatcher.$off('form:google-recaptcha:loaded', this.recaptchaLoaded);
        $dispatcher.$off('form:google-recaptcha:solved', this.recaptchaSolved);
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

const pckgStaticComponent = {
    computed: {
        templateClass: function () {
            return this.$options.name;
        }
    }
};

const pckgFakeImage = {
    methods: {
        fetchMyImage: function (value) {
            if (!value) {
                this.myImage = null;
                return;
            }

            if (typeof value === 'string' && value.indexOf('<') < 0) {
                this.myImage = value;
                return;
            }

            if (value instanceof HTMLImageElement) {
                this.myImage = value.src;
                return;
            }

            let fake = $(value);
            if (fake.find('span').length > 0) {
                this.myImage = fake.find('span')[0].innerHTML;
                return;
            }

            this.myImage = value;
        }
    }
};

const pckgFinalComponent = {
    mixins: [pckgTranslations, pckgCdn],
    props: {
        action: {
            required: true
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

const pckgSmartComponent = {
    mixins: [pckgTranslations, pckgCdn, pckgTimeout, pckgStaticComponent],
    props: {
        actionId: {
            required: true
        }
    },
    data: function () {
        return {
            loading: false,
        };
    },
    computed: {
        action: function () {
            return $store.getters.actionById(this.actionId);
        },
        subactions: function () {
            if (!this.action) {
                return;
            }

            return $store.getters.actionChildren(this.actionId);
        },
        content: function () {
            return this.action.content;
        },
        listComponent: {
            get: function () {
                return (this.action ? this.action.template.list : null) || 'derive-list';
            }, set: function () {
            }
        },
        itemComponent: {
            get: function () {
                return (this.action ? this.action.template.item : null) || 'derive-item';
            }, set: function () {
            }
        },
    },
    methods: {
        getSlotActions: function (slot) {
            if (!Array.isArray(slot)) {
                slot = [slot];
            }
            return this.subactions.filter(function (item, i) {
                if (item.template && item.template.slot) {
                    return slot.indexOf(item.template.slot) >= 0;
                }

                let indexed = slot.indexOf(i) < 0 ? null : item;
                if (!indexed) {
                    return false;
                }

                if (item.template && item.template.slot && slot.indexOf(item.template.slot) <= 0) {
                    return false;
                }

                return true;
            });
        },
    },
    mounted: function () {
        $dispatcher.$on('pckg-action:' + this.action.id + ':itemTemplate-changed', function (newTemplate) {
            if (!newTemplate) {
                return;
            }
            this.itemComponent = newTemplate;
        }.bind(this));

        $dispatcher.$on('pckg-action:' + this.action.id + ':listTemplate-changed', function (newTemplate) {
            if (!newTemplate) {
                return;
            }
            this.listComponent = newTemplate;
        }.bind(this));

        /**
         * @T00D00 - only one component should listen?
         */
        $dispatcher.$on('pckg-action:' + this.action.id + ':listSubitemSelected', function (newItem) {
            /**
             * Categories > Offers > Packets
             * On category page we display offers and all packets. Click on offer reload packets.
             * Use url: api.$type.$id.$collection somehow dynamically
             */
            this.loading = true;
            let plural = newItem.type == 'offer' ? 'offers' : 'categories';
            let collection = newItem.type == 'offer' ? 'packets' : 'offers';
            http.getJSON('/api/' + plural + '/' + newItem.id + '/' + collection, function (data) {
                this['my' + collection] = data[collection];
                this.loading = false;
                this.$nextTick(function () {
                    $('html, body').animate({
                        scrollTop: $(this.$el).offset().top + 'px'
                    }, 333);
                }.bind(this));
            }.bind(this), function () {
                this.loading = false;
                $dispatcher.$emit('notification:error', 'Error fetching ' + collection);
            }.bind(this));

            return false;
        }.bind(this));

        $dispatcher.$on('listItemSelected', function (newItem) {

            /**
             * Categories > Offers > Packets
             * On category page we display offers and all packets. Click on offer reload packets.
             * Use url: api.$type.$id.$collection somehow dynamically
             */
            this.loading = true;
            let plural = newItem.type == 'category' ? 'categories' : 'offers';
            let collection = newItem.type == 'category' ? 'offers' : 'offers';
            http.getJSON('/api/' + plural + '/' + newItem.id + '/' + collection, function (data) {
                this['my' + collection] = data[collection];
                this.loading = false;
            }.bind(this), function () {
                this.loading = false;
                $dispatcher.$emit('notification:error', 'Error fetching ' + collection);
            }.bind(this));

        }.bind(this));
    },
};

const pckgSmartList = {
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
            itemComponent: this.action.template.item || 'derive-item',
            myAction: this.action
        };
    },
    mounted: function () {
        $dispatcher.$on('pckg-action:' + this.myAction.id + ':itemTemplate-changed', function (newTemplate) {
            if (!newTemplate) {
                return;
            }
            this.itemComponent = newTemplate;
        }.bind(this));
    },
    computed: {
        content: function () {
            return this.action.content;
        },
        subactions: function () {
            return $store.getters.actionChildren(this.action.id);
        },
        templateClass: function () {
            return 'derive-list ' + this.$options.name
                + ' v' + utils.ucfirst(this.$options.name.replace('derive-list-', '').replace('derive-list', 'default'))
                + ' v' + utils.ucfirst(this.itemComponent.replace('derive-item-', '').replace('derive-item', 'default'));
        }
    }
};

const pckgSmartItem = {
    mixins: [pckgTranslations, pckgCdn],
    props: {
        content: {
            required: true
        },
        action: {
            default: null,
        },
        index: {
            type: Number,
            default: 0
        },
        settings: {
            type: Object,
            default: function () {
                return {
                    perRow: 3,
                };
            }
        }
    },
    data: function () {
        return {
            templateRender: null,
            tpl: 'derive-item',
            myAction: this.action,
            myContent: typeof this.content == 'string' ? JSON.parse(this.content) : this.content,
        };
    },
    render: function (h) {
        if (!this.templateRender) {
            if (this.$options.template) {
                return this.$options.template;
            }

            return h('div', 'Loading ...');
        }

        return this.templateRender();
    },
    computed: {
        templateClass: function () {
            return 'derive-item ' + this.$options.name
                + ' v' + utils.ucfirst(this.$options.name.replace('derive-item-', '').replace('derive-item', 'default'));
        },
        perRow: function () {
            if (this.myAction) {
                return this.myAction.settings.perRow || 2;
            }

            return this.settings.perRow || 2;
        }
    },
    mounted: function () {
        this.registerActionListeners();
    },
    watch: {
        tpl: {
            immediate: true,
            handler: function (newVal, oldVal) {
                return;
                let template = $store.getters.resolveTemplate(newVal, this.$options.template);

                let res = typeof template === 'string' ? Vue.compile(template) : template;

                this.templateRender = res.render;

                // staticRenderFns belong into $options,
                // appearantly

                this.$options.staticRenderFns = [];

                // clean the cache of static elements
                // this is a cache of the results from the staticRenderFns
                this._staticTrees = [];

                // Fill it with the new staticRenderFns
                if (res.staticRenderFns) {
                    for (var i in res.staticRenderFns) {
                        //staticRenderFns.push(res.staticRenderFns[i]);
                        this.$options.staticRenderFns.push(res.staticRenderFns[i]);
                    }
                }
            }
        }
    },
    methods: {
        registerActionListeners: function () {
            if (!this.myAction) {
                return;
            }

            $dispatcher.$on('pckg-action:' + this.myAction.id + ':itemTemplate-changed', function (newTemplate) {
                this.tpl = newTemplate;
            }.bind(this));

            $dispatcher.$on('pckg-action:' + this.myAction.id + ':perRow-changed', function (newVal) {
                this.myAction.settings.perRow = newVal;
            }.bind(this));
        }
    }
};

const pckgActionAttrs = {
    computed: {
        actionClass: function () {
            if (!this.action) {
                return;
            }

            let typeSuffix = this.$options.name.replace('pckg-', '');
            if (this.action.type == 'container') {
                typeSuffix = this.action.settings.container && this.action.settings.container.length > 0
                    ? this.action.settings.container
                    : 'container';
            }

            if (this.action.settings.classes.length > 0) {
                typeSuffix = typeSuffix + ' ' + this.action.settings.classes.join(' ');
            }

            if (this.action.settings.bgVideo) {
                typeSuffix = typeSuffix + ' has-video-background';
            }

            let mapper = {
                'bgSize': 'bg-size',
                'bgRepeat': 'bg-repeat',
                'bgPosition': 'bg-position',
            };
            let mainClass = typeSuffix + ' __action-' + this.action.id;
            $.each(this.action.settings, function (slug, setting) {
                if (Object.keys(mapper).indexOf(slug) < 0) {
                    return;
                }

                if (!setting) {
                    return;
                }

                mainClass = mainClass + ' ' + mapper[slug] + '-' + setting;
            });

            if (this.genericMode == 'edit') {
                if (this.action.type == 'action') {
                    mainClass = mainClass + ' __pb-action';
                }

                if (this.action.active) {
                    mainClass = mainClass + ' --pb-active';
                }

                if (this.action.focus) {
                    mainClass = mainClass + ' --pb-focus';
                }
            }

            return mainClass.split(' ').filter(function (c) {
                return c.split('').reverse()[0] !== '-';
            }).join(' ');
        },
        actionStyle: function () {
            if (!this.action) {
                return;
            }
            let mapper = {
                'bgColor': 'background-color',
                'bgAttachment': 'background-attachment',
                'bgImage': 'background-image',
                'margin': 'margin', // @deprecated
                'padding': 'padding', // @deprecated
                'style': 'style',
            };

            let styles = [];
            $.each(mapper, function (slug, attr) {
                if (Object.keys(mapper).indexOf(slug) < 0) {
                    return;
                }

                let setting = this.action.settings[slug];

                if (!setting) {
                    return;
                }

                let value;
                if (slug == 'style') {
                    value = setting;
                } else if (slug == 'bgImage') {
                    value = attr + ': url(\'' + this.cdn(setting) + '\')';
                } else {
                    value = attr + ': ' + setting;
                }
                styles.push(value);
            }.bind(this));

            return styles.join('; ');
        }
    }
}

const pckgColorOptions = {
    computed: {
        designSettings: function () {
            return $store.state.settings.settings.design;
        },
        colorOptions: function () {
            let designSettings = this.designSettings;
            let colors = {};

            colors[designSettings.vars.colorP] = designSettings.vars.colorP;
            colors[designSettings.vars.colorS] = designSettings.vars.colorS;
            $.each(designSettings.scopes, function (scope, vars) {
                $.each(vars, function (i, color) {
                    if (i === 'title') {
                        return;
                    }
                    colors[color] = color;
                });
            });

            return Object.values(colors);
        }
    }
};

const pckgSheetManipulator = {
    methods: {
        getCssRule: function (ruleName) {
            let id = 'style-id-libraries-theme-main-vue-footer';

            ruleName = ruleName.toLowerCase();
            var result = null;
            var find = Array.prototype.find;

            find.call(document.styleSheets, styleSheet => {
                /*if ($(styleSheet).attr('id') !== id) {
                    return;
                }*/
                result = find.call(styleSheet.cssRules, cssRule => {
                    return cssRule instanceof CSSStyleRule
                        && cssRule.selectorText.toLowerCase() == ruleName;
                });
                return result != null;
            });
            return result;
        },
        deleteCssRule: function (ruleName) {
            let styleTag = document.getElementById('style-id-store');
            let sheet = styleTag.sheet ? styleTag.sheet : styleTag.styleSheet;

            $.each(sheet.cssRules, function (j, cssRule) {
                if (cssRule instanceof CSSStyleRule
                    && cssRule.selectorText.toLowerCase() == ruleName) {
                    sheet.deleteRule(j);
                }
            });
        }
    },
    computed: {
        cssSheet: function () {
            let css = {};
            $.each(this.actionBuilderCss, function (i, item) {
                if (!css[item.device]) {
                    css[item.device] = {};
                }
                if (!css[item.device][item.selector]) {
                    css[item.device][item.selector] = {};
                }
                $.each(item.css, function (attribute, value) {
                    css[item.device][item.selector][attribute] = value;
                });
            }.bind(this));

            /**
             * Our old rule needs to be deleted?
             */
            this.deleteCssRule('.__action-' + this.action.id);
            // let rule = this.getCssRule('.__action-' + this.action.id);

            let cssArr = [];
            $.each(this.mediaQueries, function (d, mediaQuery) {
                let selectorStyles = [];
                $.each(css[d] || {}, function (selector, attributes) {
                    let styles = [];
                    $.each(attributes, function (attribute, value) {
                        if (value.indexOf('--') === 0) {
                            value = 'var(' + value + ')';
                        }
                        styles.push(attribute + ': ' + value + ';');
                    });
                    selectorStyles.push(selector + ' { ' + styles.join(' ') + ' }');
                });
                if (selectorStyles.length === 0) {
                    return;
                }
                cssArr.push((!mediaQuery ? '' : (mediaQuery + ' { ')) + selectorStyles.join("\n") + (!mediaQuery ? '' : ' }'));
            });

            return cssArr;
        },
    }
};

const pckgComputedModel = function (name) {
    return {
        get: function () {
            return this.getCssAttribute(name);
        },
        set: function (value) {
            this.setCssAttribute(name, value);
        }
    }
};

const pckgComputedHelper = {
    computed: {
        selectedDevice: function () {
            return $store.state.actionbuilder.selectedDevice;
        },
        selectedSelector: function () {
            return $store.state.actionbuilder.selectedSelector;
        },
        actionBuilderCss: {
            get: function () {
                if (this.action) {
                    return this.action.settings.attributes;
                }

                return {};
            },
            set: function (value) {
                this.action.settings.attributes = value;
            }
        },
    },
    props: {
        action: {
            required: true
        }
    },
    data: function () {
        return {
            mediaQueries: {
                default: null, // all
                smallMobile: '@media only screen and (max-width: 479px)', // xxs
                mobile: '@media only screen and (min-width: 480px)', // xs
                tablet: '@media only screen and (min-width: 768px)', // sm
                laptop: '@media only screen and (min-width: 992px)', // md
                desktop: '@media only screen and (min-width: 1200px)', // lg
            },
        };
    },
    methods: {
        getItemMediaQuery: function (item) {
            return this.mediaQueries[item.device];
        },
        getCssAttribute: function (attribute) {
            let existing = this.getExistingItem();

            if (!existing) {
                return null;
            }

            /**
             * This solves issue with PHP JSON encode.
             */
            if (Array.isArray(existing.css)) {
                existing.css = {};
            }

            return existing.css[attribute] || null;
        },
        setCssAttribute: function (attribute, value) {
            let existing = this.getExistingItem();

            if (existing) {
                /**
                 * This solves issue with PHP JSON encode.
                 */
                if (Array.isArray(existing.css)) {
                    existing.css = {};
                }
                if (!value || value.length === 0) {
                    $vue.$delete(existing.css, attribute);

                    if (Object.keys(existing.css).length === 0) {
                        utils.splice(this.actionBuilderCss, existing);
                    }

                    $dispatcher.$emit('frontpage-deck:rebuildCss');
                    return;
                }

                $vue.$set(existing.css, attribute, value);
                $dispatcher.$emit('frontpage-deck:rebuildCss');
                return;
            }

            if (!value || value.length === 0) {
                $dispatcher.$emit('frontpage-deck:rebuildCss');
                return;
            }

            let css = {};
            css[attribute] = value;
            existing = {device: this.selectedDevice, selector: this.selectedSelector, css: css};
            this.actionBuilderCss.push(existing);
            $dispatcher.$emit('frontpage-deck:rebuildCss');
        },
        setOrToggleCssAttribute: function (attribute, value) {
            let current = this.getCssAttribute(attribute);
            this.setCssAttribute(attribute, current == value ? '' : value);
        },
        getExistingItem: function () {
            let existing = null;
            $.each(this.actionBuilderCss, function (i, item) {
                if (item.device != this.selectedDevice) {
                    return;
                }
                if (item.selector != this.selectedSelector) {
                    return;
                }

                existing = item;
                return false;
            }.bind(this));

            return existing;
        },
    }
};

const pckgElement = {
    mixins: [pckgCdn, pckgTimeout, pckgActionAttrs, pckgActionAnimation],
    props: {
        actionId: {
            default: null
        },
        hardAction: {
            default: null
        },
    },
    data: function () {
        return {
            shared: {}
        };
    },
    computed: {
        viewMode: function () {
            return $store.state.generic.viewMode;
        },
        genericMode: function () {
            return $store.state.generic.genericMode;
        },
        action: function () {
            return this.hardAction ? this.hardAction : (this.actionId ? $store.getters.actionById(this.actionId) : null);
        },
        content: function () {
            return this.action ? this.action.content : {};
        },
        id: function () {
            return !this.action ? null : (this.action.type + '-' + this.action.id);
        },
        subactions: function () {
            return $store.getters.actionChildren(this.actionId);
        }
    }
};

const pckgCookie = {
    mixins: [pckgTranslations],
    data: function () {
        return {
            visible: false,
            templateClass: this.$options.name,
            selectedCookies: ['system', 'media'],
            disabledCookies: ['system'],
            optionsShown: false
        };
    },
    created: function () {
        if (getCookie('zekom')) {
            return;
        }

        this.visible = true;
    },
    methods: {
        accept: function () {
            this.visible = false;
            setCookie('zekom', 1);
            $dispatcher.$emit('pckg-cookie:accepted');
            if (this.accepted) {
                this.accepted();
            }
        },
        cancel: function () {
            this.visible = false;
            if (this.canceled) {
                this.canceled();
            }
        },
        showOptions: function () {
            this.optionsShown = true;
        }
    },
    computed: {
        allCookies: function () {
            let cookies = {
                system: 'Essential', // basket, session, promo code, referral
                media: 'Media' // youtube, vimeo, gmaps
            };
            let groups = {
                system: 'Essential cookies',
                analytics: 'Performance & analytics cookies',
                advertising: 'Advertising & targeting cookies',
                chat: 'Support & chat cookies',
                media: 'Media cookies',
                other: 'Other cookies'
            };

            let all = [
                {
                    group: 'analytics',
                    config: 'google-remarketing-tag',
                },
                {
                    group: 'analytics',
                    config: 'google-analytics',
                },
                {
                    group: 'analytics',
                    config: 'sumo-me',
                },
                {
                    group: 'advertising',
                    config: 'google-conversion-page',
                },
                {
                    group: 'advertising',
                    config: 'facebook-conversion-pixel',
                },
                {
                    group: 'chat',
                    config: 'facebook-chat',
                },
                {
                    group: 'chat',
                    config: 'tawk-to',
                },
                {
                    group: 'other',
                    config: 'google-tag-manager',
                },
            ];
            $.each(all, function (i, one) {
                cookies[one.group] = groups[one.group];
            });

            return cookies;
        }
    }
};

const pckgPartialPlatformSettings = {
    mixins: [pckgTranslations, pckgCdn, pckgFormValidator],
    data: function () {
        return {
            mode: 'view',
            settings: $store.state.settings.settings,
            modules: $store.state.settings.modules,
            storeTime: null,
            companies: $store.state.settings.companies,
            themes: $store.state.settings.themes,
            stats: $store.state.settings.stats,
            paymentMethods: $store.state.settings.paymentMethods,
            timezones: $store.state.settings.timezones,
            fonts: $store.state.settings.fonts,
            storeDatetime: $store.state.settings.storeDatetime,
            saving: false,
            modal: null,
            advancedUser: false
        };
    },
    computed: {
        stateSettings: function () {
            return $store.state.settings.settings;
        },
        loaded: function () {
            return $store.state.settings.loaded;
        },
    },
    methods: {
        edit: function () {
            this.mode = 'edit';
        },
        save: function (section, emitReload) {
            let data = this.collectData(section) || {};
            this.saving = true;

            http.post('/api/comms/platform-settings', data, function (data) {
                if (!data.success) {
                    $dispatcher.$emit('notification:error', data.message || 'Something went wrong.');
                    return;
                }
                this.mode = 'view';
                this.saving = false;
                if (!emitReload) {
                    $dispatcher.$emit('notification:success', 'Settings saved');
                } else {
                    $dispatcher.$emit('frontpage-deck:openSettingsSavedModal');
                }
                this.errors.clear();
                if (this.saved) {
                    this.saved();
                }
            }.bind(this), function (response) {
                this.saving = false;
                http.postError(response);

                this.hydrateErrorResponse(response);

                if (response.responseJSON) {
                    $dispatcher.$emit('notification:error', 'Correct errors and re-submit form');
                    return;
                }

                $dispatcher.$emit('notification:error', 'Something went wrong.');
            }.bind(this));
        },
        cancel: function () {
            this.mode = 'view';
        }
    }
};
/*
export default {
    pckgSmartComponent,
    pckgTimeout,
    pckgActionAnimation,
    pckgActionAttrs,
    pckgCdn,
    pckgCleanRequest,
    pckgCookie,
    pckgDelimiters,
    pckgElement,
    pckgFakeImage,
    pckgFormValidator,
    pckgInterval,
    pckgLocale,
    pckgPartialPlatformSettings,
    pckgPayment,
    pckgPaymentConfig,
    pckgPlatformSettings,
    pckgSmartItem,
    pckgSmartList,
    pckgStaticComponent,
    pckgSync,
    pckgTranslations
};*/