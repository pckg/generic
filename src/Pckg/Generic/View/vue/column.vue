<template>
    <div :id="id" :class="actionClass" :style="actionStyle" v-pagebuilder>
        <pckg-action-bg :action="action"></pckg-action-bg>
        <component v-for="a in subactions" :action-id="a.id" :is="a.component" :key="a.id"></component>
    </div>
</template>

<script>
    export default {
        name: 'pckg-column',
        mixins: [pckgElement]
    }
</script>