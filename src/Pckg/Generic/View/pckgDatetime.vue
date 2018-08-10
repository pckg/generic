<template>
    <div class="pckg-datetime">
        <input type="text" v-model="value" class="form-control"/>
    </div>
</template>

<script>
    export default {
        name: 'pckg-datetime',
        props: {
            value: {
                default: '',
                type: String
            },
            format: {
                default: 'YYYY-MM-DD HH:mm'
            }
        },
        methods: {
            initPicker: function () {
                var $this = this;
                $(this.$el).find('input').datetimepicker({
                    format: this.format
                }).on('dp.change', function (ev) {
                    $this.$emit('input', $(this).val());
                });
            }
        },
        created: function () {
            this.$nextTick(function () {
                this.initPicker();
            }.bind(this));
        }
    }
</script>