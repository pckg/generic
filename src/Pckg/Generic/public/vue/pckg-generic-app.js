data.$root = new Vue({
    el: '#vue-app',
    data: function () {
        return {
            alerts: [],
            modals: []
        };
    },
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