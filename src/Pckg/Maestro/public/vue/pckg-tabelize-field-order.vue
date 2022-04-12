<template>
    <input type="number" step="1" v-model="value" class="form-control"/>
</template>

<script>
    export default {
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
                http.post(utils.url('/dynamic/records/field/[table]/[field]/[record]/order/[order]', {
                        record: this.record,
                        field: this.field,
                        order: this.value,
                        table: this.table.id
                    }), {}, function (data) {
                    }.bind(this)
                );
            }
        }
    }
</script>
