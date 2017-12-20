<template>
    <div class="htmlbuilder-validator-error" v-if="shown">{{ message }}</div>
</template>

<script>
    export default {
        name: 'htmlbuilder-validator-error',
        props: {
            message: {
                default: 'Error',
                required: true
            },
            shown: {
                default: false,
                type: Boolean,
                required: true
            }
        }
    }
</script>