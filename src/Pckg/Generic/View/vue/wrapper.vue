<template>
    <section :id="id" :class="actionClass" :style="actionStyle" v-pagebuilder>
        <pckg-action-bg :action="action"></pckg-action-bg>
        <component v-for="a in subactions" :action-id="a.id" :is="a.component" :key="a.id"></component>
    </section>
</template>

<script>
    export default {
        name: 'pckg-wrapper',
        mixins: [pckgElement]
    }
</script>