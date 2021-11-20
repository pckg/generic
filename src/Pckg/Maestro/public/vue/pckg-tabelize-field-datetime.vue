<template>
    <div>
        <a href="#" @click.prevent="toggle" class="text-center" :title="formattedValue" :min="min" :max="max" :value="value">
            <i :class="'fa-' + iconClass"></i>
        </a>
    </div>
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
            iconClass: function () {
                const now = moment();
                const end = '2999-01-01 00:00:00';
                let max = this.max ? moment(this.max) : null;
                let min = this.min ? moment(this.min) : null;

                if (!this.value) {
                    if (this.min === end) {
                        // never closed by default
                        return 'square fal clr-success 0';
                    } else if (this.min) {
                        // closes at some date possibly now?
                        return 'square fas clr-error 1';
                    }

                    if (max) {
                        return 'square fas clr-error 2';
                    }

                    return 'square fas clr-success 3';
                }

                if (now.isSameOrAfter(this.value)) {
                    if (this.min === end) {
                        // closed in past
                        return 'square fas clr-error 4';
                    } else {
                        // published in past
                        return 'square fas clr-success 7';
                    }
                } else {
                    if (this.min === end) {
                        // closed in future
                        return 'square fal clr-error 5';
                    } else {
                        // published in future
                        return 'square fal clr-success 8';
                    }
                }
            },
            brValue: function () {
                return this.noBrValue.split('').reverse().join('').replace(' ', '*').split('').reverse().join('').replace('*', '<br />');
            },
            noBrValue: function () {
                return this.value;
                return locale.datetime(this.value);
            },
            formattedValue: function () {
                return locale.datetime(this.value);
            }
        }
    }
</script>
