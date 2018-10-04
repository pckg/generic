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