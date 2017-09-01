var pckgBootstrapAlertComponent = Vue.component('pckg-bootstrap-alert', {
    mixins: [pckgDelimiters],
    name: 'pckg-bootstrap-alert',
    template: '#pckg-bootstrap-alert',
    props: {
        style: {
            type: String,
            default: 'success'
        },
        text: {
            type: String,
            default: ''
        },
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