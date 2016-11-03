var pckgTabelizeFieldBoolean = Vue.component('pckg-tabelize-field-boolean', {
    template: '#pckg-tabelize-field-boolean',
    data: function () {
        return {};
    },
    props: {
        field: null,
        record: null,
        value: null,
        table: null,
        url: null
    },
    methods: {
        toggle: function () {
            this.value = this.value > 0 ? 0 : 1;
            http.getJSON(utils.url(this.url, {
                    table: this.table.id,
                    record: this.record,
                    field: this.field,
                    state: this.value > 0 ? 1 : 0
                }), function (data) {
                }.bind(this)
            );
        }
    },
    computed: {
        btnClass: function () {
            return this.value > 0 ? 'btn-success' : 'btn-danger';
        },
        iconClass: function () {
            return this.value > 0 ? 'fa-check' : 'fa-times';
        }
    }
});