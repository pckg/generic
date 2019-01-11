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

const dynamicEvents = {
    created: function () {
        $.each(this.triggers, function (method, events) {
            $.each(Array.isArray(events) ? events : [events], function (i, event) {
                console.log('listening to ' + event + ' with ' + method);
                this.$parent._data.localBus.$on(event, this[method]);
            }.bind(this));
        }.bind(this));
    },
    beforeDestroy: function () {
        $.each(this.triggers, function (method, events) {
            $.each(Array.isArray(events) ? events : [events], function (i, event) {
                console.log('un-listening to ' + event + ' with ' + method);
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
                    $dispatcher.$emit('notification:' + (data.success ? 'success' : 'error'), data.message || (data.success ? 'Settings saved' : 'General error'));
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
            mode: this.getMode(user)
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
            }.bind(this), function (response) {
                this.state = 'error';
                this.error = 'Error initializing payment';
                if (response.responseJSON && response.responseJSON.message) {
                    this.error = response.responseJSON.message;
                }
            }.bind(this));
        },
        afterFetch: function (data) {
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

const pckgFormValidator = {
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

var pckgLocale = {
    methods: {
        locale: function () {
            return locale;
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

const pckgSmartComponent = {
    mixins: [pckgTranslations, pckgCdn, pckgTimeout],
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
        listComponent: function () {
            return (this.action ? this.action.template.list : null) || 'derive-list';
        },
        itemComponent: function () {
            return (this.action ? this.action.template.item : null) || 'derive-item';
        },
        templateClass: function () {
            return this.$options.name;
        }
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
            console.log('item template changed', newTemplate);
            if (!newTemplate) {
                return;
            }
            this.itemComponent = newTemplate;
        }.bind(this));

        $dispatcher.$on('pckg-action:' + this.action.id + ':listTemplate-changed', function (newTemplate) {
            console.log('list template changed', newTemplate);
            if (!newTemplate) {
                return;
            }
            this.listComponent = newTemplate;
        }.bind(this));

        /**
         * @T00D00 - only one component should listen?
         */
        $dispatcher.$on('pckg-action:' + this.action.id + ':listSubitemSelected', function (newItem) {
            console.log('called in pckg-generic-app-top.js');
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
            itemComponent: this.action.template.item || 'derive-item',
            myAction: this.action
        };
    },
    mounted: function () {
        $dispatcher.$on('pckg-action:' + this.myAction.id + ':itemTemplate-changed', function (newTemplate) {
            console.log('smart list item template changed', newTemplate);
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

var pckgSmartItem = {
    mixins: [pckgTranslations, pckgCdn],
    props: {
        content: {
            required: true
        },
        action: {
            type: Object
        },
        index: {
            type: Number,
            default: 0
        }
    },
    mounted: function () {
        if (!this.myAction) {
            return;
        }

        $dispatcher.$on('pckg-action:' + this.myAction.id + ':itemTemplate-changed', function (newTemplate) {
            this.tpl = newTemplate;
        }.bind(this));

        $dispatcher.$on('pckg-action:' + this.myAction.id + ':perRow-changed', function (newVal) {
            this.myAction.settings.perRow = newVal;
        }.bind(this));
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
            return this.myAction.settings.perRow || 3;
        }
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
    }
};

const pckgActionAttrs = {
    computed: {
        actionClass: function () {
            if (!this.action) {
                return;
            }
            let typeSuffix = this.$options.name.replace('pckg-', '');
            if (this.action.type == 'container' && this.action.settings.container != 'container') {
                typeSuffix = typeSuffix + ' ' + this.action.settings.container;
            }

            if (this.action.settings.width.length > 0) {
                typeSuffix = typeSuffix + ' ' + this.action.settings.width.join(' ');
            }

            if (this.action.settings.offset.length > 0) {
                typeSuffix = typeSuffix + ' ' + this.action.settings.offset.join(' ');
            }

            if (this.action.settings.scopes.length > 0) {
                typeSuffix = typeSuffix + ' ' + this.action.settings.scopes.join(' ');
            }

            if (this.action.settings.class) {
                typeSuffix = typeSuffix + ' ' + this.action.settings.class;
            }

            if (this.action.settings.bgVideo) {
                typeSuffix = typeSuffix + ' has-video-background';
            }

            let mapper = {
                'bgSize': 'bg-size',
                'bgRepeat': 'bg-repeat',
                'bgPosition': 'bg-position',
            };
            let mainClass = typeSuffix;
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
                    mainClass = mainClass + ' pb-action';
                }
                if (this.action.active) {
                    mainClass = mainClass + ' ' + 'pb-active-action';
                }
            }

            return mainClass;
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

var pckgElement = {
    mixins: [pckgCdn, pckgTimeout, pckgActionAttrs],
    props: {
        actionId: {
            default: null
        },
        hardAction: {
            default: null
        },
    },
    methods: {
        componentClicked: function ($event) {
            console.log('componentClicked');
            if (this.genericMode != 'edit') {
                console.log('not edit');
                return;
            }

            $event.preventDefault();
            $event.stopPropagation();

            if ($(this.$el).find('.mce-content-body').length > 0) {
                console.log('already initialized, single');
                return false;
            }

            this.timeout('componentClicked', function () {
                $dispatcher.$emit('pckg-frontpage:selectAction', this.action);
            }.bind(this), 333);

            return false;
        },
        componentDblClicked: function ($event) {
            console.log('componentDblClicked');
            if (this.genericMode != 'edit') {
                console.log('not edit');
                return;
            }

            this.removeTimeout('componentClicked');
            $event.preventDefault();
            $event.stopPropagation();

            if ($(this.$el).find('.bind-content').length == 0) {
                console.log('no content');
                return;
            }

            if ($(this.$el).find(this.id + '.mce-content-body').length > 0) {
                console.log('already initialized');
                return;
            }

            console.log('initializing');

            initTinymce(this.id + ' .bind-content', {
                menubar: false,
                inline: true,
                //theme: 'inlite',
                content_css: null,
                toolbar: (function () {
                    let toolbar = tinyMceConfig.toolbar.slice(0);
                    if (toolbar[0].indexOf('save') !== 0) {
                        toolbar[0] = 'save cancel close | ' + toolbar[0];
                    }
                    return toolbar;
                })(),
                save_onsavecallback: function (editor) {
                    let content = this.content;
                    content.content = editor.getContent();
                    $store.commit('setActionContent', {action: this.action, content: content});

                    http.post(utils.url('@pckg.generic.pageStructure.content', {content: this.content.id}), {content: this.content}, function (data) {
                    }.bind(this));

                    editor.destroy();
                }.bind(this),
                init_instance_callback: function (editor) {
                    editor.execCommand('mceFocus', false);
                }.bind(this)
            });
            console.log('initialized');

            //$dispatcher.$emit('pckg-frontpage:editContent', this.action);

            return false;
        },
        componentEnter: function (e) {
            //console.log('componentEnter');
            if (this.genericMode != 'edit') {
                //console.log('not edit');
                return;
            }
            $store.commit('setActionFocus', {actionId: this.action.id, focus: true});
        },
        componentLeave: function (e) {
            //console.log('componentLeave');
            if (this.genericMode != 'edit') {
                //console.log('not edit');
                return;
            }
            $store.commit('setActionFocus', {actionId: this.action.id, focus: false});
        }
    },
    computed: {
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
