<template>
    <a class="btn btn-xs" :class="btnClass" @click.prevent="toggle">
        <template v-if="value">{{ value | date }}<br />{{ value | time }}</template>
        <template v-else><i class="fa fa-times"></i></template>
    </a>
</template>

<script>
    export default {
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
                if (this.min == '2999-01-01 00:00:00') {
                    return this.value > this.max
                        ? 'btn-danger'
                        : 'btn-success';
                } else if (this.min == null) {
                    return this.value > this.max || this.value == this.min
                        ? 'btn-danger'
                        : 'btn-success';
                } else {
                    return this.value <= this.min
                        ? 'btn-danger'
                        : 'btn-success';
                }
            },
            brValue: function () {
                return this.noBrValue.split('').reverse().join('').replace(' ', '*').split('').reverse().join('').replace('*', '<br />');
            },
            noBrValue: function () {
                return this.value;
                return locale.datetime(this.value);
            },
        }
    }
</script>