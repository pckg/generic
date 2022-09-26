<template>

    <pckg-loader v-if="loading" :loading="loading"></pckg-loader>

    <component v-else :is="listComponent" :class="templateClass"></component>

</template>

<script>

    export default {
        mixins: [CommsHubUse.static]
    }

</script>
