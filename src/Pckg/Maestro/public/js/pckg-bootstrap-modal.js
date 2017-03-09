var pckgBootstrapModalComponent = Vue.component('pckg-bootstrap-modal', {
    name: 'pckg-bootstrap-modal',
    template: '#pckg-bootstrap-modal',
    props: {
        header: null,
        body: null,
        dismissable: true,
        id: null,
        visible: null,
        style: null,
        size: null
    },
    data: function () {
        return {
            _modal: null
        };
    },
    create: function () {
        this.$nextTick(function () {
            this._modal = $(this.$el).modal();

            if (this.visible) {
                this._modal.modal('show');
            }
        });
    }
});