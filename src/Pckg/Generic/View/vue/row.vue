<template>
    <div :id="id" :class="actionClass" :style="actionStyle" @click.self="componentClicked($event)" @mouseenter="componentEnter($event)" @mouseleave="componentLeave($event)">
        <slot name="body">
            <pckg-action-bg :action="action"></pckg-action-bg>
            <frontpage-action-outline :action="action" v-if="action.active"></frontpage-action-outline>
            <component v-for="a in subactions" :action-id="a.id" :is="a.component" :key="a.id"></component>
        </slot>
    </div>
</template>

<script>
    export default {
        name: 'pckg-row',
        mixins: [pckgElement]
    }
</script>