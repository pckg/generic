<template>
    <div class="htmlbuilder-validator-error" v-if="isVisible">{{ getMessage }}</div>
</template>

<script>
    export default {
        name: 'htmlbuilder-validator-error',
        props: {
            errors: {},
            name: {},
            message: {
                default: 'Error'
            },
            shown: {
                default: false,
                type: Boolean
            }
        },
        data: function(){
            return {
                myErrors: this.errors
            };
        },
        watch: {
            errors: function(newVal){
                this.myErrors = newVal;
            }
        },
        computed: {
            isVisible: function () {
                return (this.errors && this.errors.has(this.name)) || this.shown;
            },
            getMessage: function () {
                return (this.errors && this.errors.first(this.name)) || this.message;
            }
        },
    }
</script>