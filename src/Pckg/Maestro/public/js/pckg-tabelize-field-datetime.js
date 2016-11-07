var pckgTabelizeFieldDatetime = Vue.component('pckg-tabelize-field-datetime', {
    template: '#pckg-tabelize-field-datetime',
    data: function () {
        return {};
    },
    props: {
        field: null,
        record: null,
        value: null,
        min: null,
        max: null,
        table: null,
        url: null
    },
    methods: {
        toggle: function () {
            this.value = this.value <= this.min ? this.max : this.min;
            http.getJSON(utils.url(this.url, {
                    record: this.record,
                    field: this.field,
                    state: this.value == this.min ? 1 : 0,
                    table: this.table.id
                }), function (data) {
                }.bind(this)
            );
        }
    },
    computed: {
        btnClass: function () {
            return this.value <= this.min ? 'btn-danger' : 'btn-success';
        }
    }
});