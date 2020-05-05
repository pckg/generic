<template>
    <section class="display-block --section">
        <template v-for="elements in groupedActions">
            <component v-for="action in elements"
                       :action-id="action.id"
                       :is="'pckg-' + action.type"
                       :key="action.id"></component>
        </template>
    </section>
</template>

<script>
    export default {
        computed: {
            groupedActions: function () {
                return [
                    $store.getters.groupedRootElements.header,
                    $store.getters.groupedRootElements.content,
                    $store.getters.groupedRootElements.footer
                ];
            }
        }
    }
</script>