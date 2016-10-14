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
        max: null
    },
    methods: {
        toggle: function () {
            var url = '{{ url('
            dynamic.records.field.toggle
            ', {table: dynamic.getTable()}) }}';
            this.value = this.value <= this.min ? this.max : this.min;
            http.getJSON(utils.url(url, {
                    record: this.record,
                    field: this.field,
                    state: this.value == this.min ? 1 : 0
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