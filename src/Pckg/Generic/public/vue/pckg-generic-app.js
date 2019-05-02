/**
 * Initialize main VueJS app.
 */

Pckg.vue.stores.auth = {
    state: {
        user: Pckg.auth.user || {}
    },
    getters: {
        user: function (state) {
            return state.user;
        },
        isLoggedIn: function (state) {
            return state.user.id > 0 ? true : false;
        },
        isAdmin: function (state) {
            return state.user.user_group_id == 1;
        }
    },
    mutations: {
        prepareUser: function (state, params) {
            http.getJSON('/api/auth/user', function (data) {
                state.user = data.user;
                $dispatcher.$emit('auth:user:' + (data.user.id > 0 ? 'in' : 'out'));
                if (params && params.callback) {
                    params.callback();
                }
            });

            $store.commit('prepareAddresses', {order: params && params.order || null});
        },
        logoutUser: function (state, callback) {
            http.getJSON('/logout', function () {
                $store.commit('prepareUser', {callback: callback});
            });
        }
    }
};

Pckg.vue.stores.template = {
    state: {
        /**
         * This is something that should be cached per app level.
         */
        templates: Pckg.vue.templates,
    },
    getters: {

        resolveTemplate: function (state) {
            console.log('resolving', state);
            return function (template, opt) {
                console.log('Template store: resolving template: ' + template, state.templates);

                if (state.templates && Object.keys(state.templates).indexOf(template) >= 0) {
                    console.log('overriden template');
                    return state.templates[template];
                }

                if (false && opt) {
                    console.log('option');
                    return opt;
                }

                /**
                 * @T00D00 - all available templates should be packed in some template manager?
                 *         - we can load templates that have .vue or .vue.twig file endings?
                 */
                let template1 = '<ul class="categories-list-module vSidebar">\n' +
                    '    <li v-for="category in categories">\n' +
                    '        <a :href="category.url"\n' +
                    '           @click.prevent="emit(\'derive-categories-list:load-category\', category.id)">{{ category.title }}</a>\n' +
                    '        <ul v-if="category.offers && category.offers.length > 0">\n' +
                    '            <li v-for="offer in category.offers">\n' +
                    '                <a :href="offer.url"\n' +
                    '                   @click.prevent="emit(\'derive-categories-list:load-offer\', offer.id)">{{ offer.title }}</a>\n' +
                    '            </li>\n' +
                    '        </ul>\n' +
                    '    </li>\n' +
                    '</ul>';

                let template2 = '<div><div v-for="category in categories">{{ category.title }}</div></div>';
                console.log('1 if sidebar, 2 if not', template);

                return template == 'Derive/Offers:categories/list-vSidebar' ? template1 : template2;
            };
        }
    }
};

const router = new VueRouter({
    mode: 'history',
    routes: Pckg.router.vueUrls || []
});

router.afterEach(function (to, from) {
    if (typeof ga === 'undefined') {
        return;
    }

    if (from && to.path == from.path) {
        return;
    }

    ga('set', 'page', to.path);
    ga('send', 'pageview');
});

const $store = new Vuex.Store({
    state: {
        router: {
            urls: Pckg.router.urls || {}
        },
        translations: Pckg.translations || {}
    },
    modules: Pckg.vue.stores,
});

if ($('header.c-header').length > 0) {
    new Vue({
        el: 'header.c-header',
        $store,
        router,
        computed: {
            basket: function () {
                return $store.state.basket;
            }
        }
    });
}

const $vue = new Vue({
    el: '#vue-app',
    $store,
    router,
    data: function () {
        return {
            localBus: new Vue()
        };
    },
    mixins: [pckgDelimiters],
    methods: {
        openModal: function (data) {
            this.modals.push(data);
            $('.modal.in').modal('hide');
            Vue.nextTick(function () {
                $('#' + data.id).modal('show');
            });
        },
        emit: function (event) {
            $dispatcher.$emit(event);
        }
    },
    computed: {
        '$store': function () {
            return $store;
        },
        basket: function () {
            return $store.state.basket;
        }
    }
});