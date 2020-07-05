const synced = window !== window.top && window.location.hostname === window.parent.location.hostname;
const $store = !synced
    ? (new Vuex.Store({
        state: {
            router: {
                urls: Pckg.router.urls || {}
            },
            translations: Pckg.translations || {},
        },
        modules: Pckg.vue.stores,
    }))
    : window.parent.$store;

const router = new VueRouter({
    mode: 'history',
    routes: Pckg.router.vueUrls || []
});

router.beforeEach(function(to, from, next) {
    /**
     * When redirecting from non-vue to vue.
     */
    if (from.matched.length === 0 && to.matched.length > 0 && from.fullPath !== '/') {
        next(false);
        http.redirect(to.fullPath);
        return;
    }

    /**
     * Auth guard.
     */
    let redirected = false;
    if (to.meta.tags) {
        $.each(to.meta.tags, function(i, routeTag){
            if ($store.getters.userHasTag(routeTag)) {
                return;
            }
            next(Pckg.auth.user ? '/' : '/login');
            redirected = true;
            return false;
        })
    }

    if (redirected) {
        return;
    }

    next();
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

if (!synced) {
    window._$store = $store;
} else {
    window.parent.$store.subscribe(function (mutation, parentState) {
        $store.replaceState(parentState);
    });
}

let s = window === window.top ? $store : window.parent.$store;
if ($('header.a-header').length > 0) {
    new Vue({
        el: 'header.a-header',
        s,
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
    s,
    router,
    data: function () {
        return {
            localBus: new Vue(),
            inIframe: window !== window.top
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
    },
    updated: function () {
        this.$nextTick(function () {
            $dispatcher.$emit('vue:updated');
        });
    }
});
