var pckgBootstrapAlertComponent = Vue.component('pckg-bootstrap-alert', {
    name: 'pckg-bootstrap-alert',
    template: '#pckg-bootstrap-alert',
    props: {
        style: null,
        text: null,
        dismissable: {
            type: Boolean,
            default: true
        }
    },
    data: function () {
        return {};
    },
    methods: {}
});