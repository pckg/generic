var pckgBootstrapModalComponent = Vue.component('pckg-bootstrap-modal', {
    name: 'pckg-bootstrap-modal',
    template: '#pckg-bootstrap-modal',
    props: {
        header: null,
        body: null,
        dismissable: true,
        id: null,
        visible: false
    },
    data: function () {
        return {};
    },
    methods: {}
});