<template>
    <div :id="id" :class="actionClass" :style="actionStyle" v-pagebuilder>
        <pckg-action-bg :action="action"></pckg-action-bg>
        <frontpage-action-outline :action="action" v-if="hasOutline"></frontpage-action-outline>
        <component v-for="a in subactions" :action-id="a.id" :is="a.component" :key="a.id"></component>
    </div>
</template>

<script>
    export default {
        name: 'pckg-container',
        mixins: [pckgElement]
    }
</script>