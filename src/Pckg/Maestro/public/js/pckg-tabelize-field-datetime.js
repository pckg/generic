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
            console.log('min', this.min, 'max', this.max);
            var state = null
            if (this.min == '2999-01-01 00:00:00') {
                if (this.value <= this.max) {
                    this.value = this.min;
                    state = 0;
                } else {
                    this.value = this.max;
                    state = 1;
                }
            } else if (this.min == null) {
                if (this.value <= this.max) {
                    this.value = this.min;
                    state = 0;
                } else {
                    this.value = this.max;
                    state = 1;
                }
            } else {
                if (this.value <= this.min) {
                    this.value = this.max;
                    state = 1;
                } else {
                    this.value = this.min;
                    state = 0;
                }
            }
            http.getJSON(utils.url(this.url, {
                    record: this.record,
                    field: this.field,
                    state: state,
                    table: this.table.id
                }), function (data) {
                }.bind(this)
            );
        }
    },
    computed: {
        btnClass: function () {
            console.log('min', this.min, 'max', this.max);

            if (this.min == '2999-01-01 00:00:00') {
                return this.value > this.max ? 'btn-danger' : 'btn-success';
            } else if (this.min == null) {
                return this.value > this.max ? 'btn-danger' : 'btn-success';
            } else {
                return this.value <= this.min ? 'btn-danger' : 'btn-success';
            }
        }
    }
});