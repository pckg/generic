<template>
    <div class="pckg-datetime">
        <pckg-datetime-picker v-model="value" :options="myOptions"></pckg-datetime-picker>
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
                myValue: this.value,
            };
        },
        watch: {
            myValue: function (newValue) {
                this.$emit('input', myValue);
            },
            value: function (newValue) {
                this.myValue = newValue;
            },
            options: function (options) {
                options.format = this.format;
                this.myOptions = options;
            }
        }
    }
</script>