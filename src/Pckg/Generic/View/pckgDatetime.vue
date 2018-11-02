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
            },
            options: {
                default: function () {
                    return {};
                }
            }
        },
        data: function () {
            let options = this.options;
            options.format = this.format;

            return {
                myOptions: options,
            };
        },
        watch: {
            options: function (options) {
                options.format = this.format;
                this.myOptions = options;
            }
        },
        methods: {
            initPicker: function () {
                var $this = this;
                console.log("Options", this.myOptions);
                $(this.$el).find('input').datetimepicker(this.myOptions).on('dp.change', function (ev) {
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