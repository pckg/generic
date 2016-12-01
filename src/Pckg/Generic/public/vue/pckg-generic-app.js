/**
 * Push generic functionality to vue.
 */
utils.pushToVue({
    el: '#vue-app',
    data: {
        alerts: [],
        modals: []
    },
    methods: {
        openModal: function (data) {
            this.modals.push(data);
            $('.modal.in').modal('hide');
            Vue.nextTick(function () {
                $('#' + data.id).modal('show');
            });
        }
    },
    ready: [
        function () {
            $('#main-row').animate({opacity: 1});
        }
    ],
    on: {}
});

/**
 * Transform object to function which returns object.
 */
var vueData = $vue.data;
$vue.data = function () {
    var data = {};
    $.each(vueData, function (key, val) {
        data[key] = val;
    });

    return data;
};

/**
 * Transform ready method.
 */
var vueReady = $vue.ready;
$vue.ready = function () {
    $.each(vueReady, function (key, val) {
        val();
    })
};

var on = $vue.on;

/**
 * Initialize main VueJS app.
 */
data.$root = utils.createVue('root', $vue);

/**
 * Attach listeners
 */
$.each(on, function(event, callback){
    console.log('registering ' + event);
    data.$root.$on(event, callback);
});