<template>
    <div class="htmlbuilder-validator-error" v-if="isVisible">{{ getMessage }}</div>
</template>

<script>
    export default {
        name: 'htmlbuilder-validator-error',
        props: {
            bag: {},
            name: {},
            message: {
                default: 'Error'
            },
            shown: {
                default: false,
                type: Boolean
            }
        },
        data: function () {
            return {
                myErrors: this.bag
            };
        },
        watch: {
            bag: function (errors) {
                this.myErrors = errors;
            }
        },
        computed: {
            isVisible: function () {
                return (this.myErrors && this.myErrors.has(this.name)) || this.shown;
            },
            getMessage: function () {
                return (this.myErrors && this.myErrors.first(this.name)) || this.message;
            }
        },
    }
</script>