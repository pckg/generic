/**
 * Initialize main VueJS app.
 */

/*var $router = new VueRouter({
 routes: []
 });*/

const $authStore = {
    state: {
        user: {
            test: 'yes'
        }
    },
    getters: {
        user: function (state) {
            return state.user;
        }
    },
    mutations: {
        prepareUser: function (state) {
            http.get('/api/auth/user', function (data) {
                state.user = data.user;
            }.bind(this));
        }
    }
};

const $basketStore = {
    state: {
        orders: [],
        dimensions: [],
    },
    mutations: {
        prepareBasket: function (state) {
            http.get('/api/basket', function (data) {
                // temp
                $.each(data.orders, function (i, order) {
                    $.each(order.packets, function (j, packet) {
                        data.orders[i].packets[j].profile = {};
                        data.orders[i].packets[j].profile.setting = order.profile;
                    });
                });

                state.orders = data.orders;
                state.dimensions = data.dimensions;
            }.bind(this));
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
    modules: {
        auth: $authStore,
        basket: $basketStore
    },
    actions: {},
    mutations: {},
    getters: {}
});

new Vue({
    el: 'nav.header',
    $store,
    computed: {
        basket: function () {
            return $store.state.basket;
        }
    },
    mounted: function () {
        $store.commit('prepareBasket');
        $store.commit('prepareUser');
    }
});

const $vue = new Vue({
    el: '#vue-app',
    // router: $router,
    data: {
        alerts: [],
        modals: [],
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
        }
    }
});