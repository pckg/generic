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
            }
        },
        methods: {
            initPicker: function () {
                var $this = this;
                $(this.$el).find('input').datetimepicker({
                    format: 'YYYY-MM-DD HH:mm'
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