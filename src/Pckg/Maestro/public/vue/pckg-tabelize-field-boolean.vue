<template>
    <a @click.prevent="toggle" href="#" class="pckg-tabelize-field-boolean" :class="onClass">
        <i class="fa" :class="iconClass"></i>
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
            onClass: function () {
                return this.value > 0 ? 'on' : 'off';
            },
            iconClass: function () {
                return 'fa-toggle-' + this.onClass;
            },
        }
    }
</script>