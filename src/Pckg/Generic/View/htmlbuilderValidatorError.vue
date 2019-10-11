<template>
    <div class="htmlbuilder-validator-error animated fadeIn" v-if="isVisible">{{ getMessage }}</div>
</template>

<script>
    export default {
        name: 'htmlbuilder-validator-error',
        inject: ['$validator'],
        props: {
            name: {},
            message: {
                default: 'Error'
            },
            shown: {
                default: false,
                type: Boolean
            }
        },
        computed: {
            keyedErrors: function(){
                return collection.map(collection.keyBy(this.errors.items, 'field'), 'msg');
            },
            isVisible: function () {
                return !!this.keyedErrors[this.name];
            },
            getMessage: function () {
                return this.keyedErrors[this.name] || this.message;
            }
        },
    }
</script>