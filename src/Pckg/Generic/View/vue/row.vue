<template>
    <div v-if="hasMediaGrid"
         :id="id"
         :class="actionClass"
         :style="actionStyle"
         v-pagebuilder
         v-media-grid>
        <pckg-action-bg :action="action"></pckg-action-bg>
        <component v-for="a in subactions" :action-id="a.id" :is="a.component" :key="a.id"></component>
    </div>
    <div v-else :id="id"
         :class="actionClass"
         :style="actionStyle"
         v-pagebuilder>
        <pckg-action-bg :action="action"></pckg-action-bg>
        <component v-for="a in subactions" :action-id="a.id" :is="a.component" :key="a.id"></component>
    </div>
</template>

<script>
    export default {
        name: 'pckg-row',
        mixins: [pckgElement],
        computed: {
            hasMediaGrid: function () {
                return ['mediaGrid', 'mediaGridNumbered'].indexOf(this.action.settings.rowType) >= 0;
            }
        }
    }
</script>