/**
 * Initialize main VueJS app.
 */

/*var $router = new VueRouter({
 routes: []
 });*/

Pckg.vue.stores.auth = {
    state: {
        user: Pckg.auth.user || {}
    },
    getters: {
        user: function (state) {
            return state.user;
        },
        isLoggedIn: function(state){
            return state.user.id > 0 ? true : false;
        }
    },
    mutations: {
        prepareUser: function (state, callback) {
            http.getJSON('/api/auth/user', function (data) {
                state.user = data.user;
                $dispatcher.$emit('auth:user:' + (data.user.id > 0 ? 'in' : 'out'));
                if (callback) {
                    callback();
                }
            });

            $store.commit('prepareAddresses');
        },
        logoutUser: function (state, callback) {
            http.getJSON('/logout', function () {
                $store.commit('prepareUser', callback);
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

const $store = new Vuex.Store({
    state: {
        router: {
            urls: Pckg.router.urls || {}
        },
        translations: Pckg.translations || {}
    },
    modules: Pckg.vue.stores,
    actions: {},
    mutations: {},
    getters: {}
});

if ($('nav.header').length > 0) {
    new Vue({
        el: 'nav.header',
        $store,
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
    // router: $router,
    data: {
        alerts: [],
        //$authStore: $authStore,
        //$basketStore: $basketStore
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
    }
});