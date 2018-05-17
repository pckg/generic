/**
 * Initialize main VueJS app.
 */

/*var $router = new VueRouter({
 routes: []
 });*/

Pckg.vue.stores.auth = {
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
            http.getJSON('/api/auth/user', function (data) {
                state.user = data.user;
            }.bind(this));
        }
    }
};

Pckg.vue.stores.basket = {
    state: {
        basketOrder: {orders: []},
        dimensions: Pckg.data.dimensions,
    },
    mutations: {
        prepareBasket: function (state) {
            http.getJSON('/api/basket', function (data) {
                // temp
                $.each(data.orders, function (i, order) {
                    $.each(order.packets, function (j, packet) {
                        data.orders[i].packets[j].profile = {};
                        data.orders[i].packets[j].profile.setting = order.profile;
                    });
                });

                state.basketOrder = data.order;
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
    },
    mounted: function () {
        $store.commit('prepareUser');
    }
});
}

const $vue = new Vue({
    el: '#vue-app',
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
        }
    }
});