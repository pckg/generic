var pckgTabelizeFieldOrder = Vue.component('pckg-tabelize-field-order', {
    template: '#pckg-tabelize-field-order',
    data: function () {
        return {
            _changeTimeout: null
        };
    },
    props: {
        field: null,
        record: null,
        value: null,
        table: null,
        url: null
    },
    watch: {
        value: function () {
            clearTimeout(this._changeTimeout);
            this._changeTimeout = setTimeout(function () {
                this.saveData();
            }.bind(this), 1000);
        }
    },
    methods: {
        saveData: function () {
            http.getJSON(utils.url(this.url, {
                    record: this.record,
                    field: this.field,
                    order: this.value,
                    table: this.table.id
                }), function (data) {
                }.bind(this)
            );
        }
    }
});