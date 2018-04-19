/**
 * Initialize main VueJS app.
 */

/*var $router = new VueRouter({
 routes: []
 });*/

var $authStore = {
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

var $store = new Vuex.Store({
    state: {
        router: {
            urls: Pckg.router.urls || {}
        },
        translations: Pckg.translations || {}
    },
    modules: {
        auth: $authStore
    },
    actions: {},
    mutations: {},
    getters: {}
});

var $vue = new Vue({
    el: '#vue-app',
    $store,
    // router: $router,
    data: {
        alerts: [],
        modals: [],
        $authStore: $authStore
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