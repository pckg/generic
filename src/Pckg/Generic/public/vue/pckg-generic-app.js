/**
 * Initialize main VueJS app.
 */

/*var $router = new VueRouter({
    routes: []
});*/

var store = new Vuex.Store({});

var $vue = new Vue({
    // router: $router,
    store,
    data: {
        alerts: [],
        modals: []
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

$vue.$mount('#vue-app');