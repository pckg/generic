<template>

    <pckg-loader v-if="loading" :loading="loading"></pckg-loader>

    <component v-else :is="listComponent" :action-id="action.id" :class="templateClass"></component>

</template>

<script>

    export default {
        mixins: [CommsHub.component]
    }

</script>
