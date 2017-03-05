/**
 * Initialize main VueJS app.
 */
var $vue = new Vue({
    el: '#vue-app',
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