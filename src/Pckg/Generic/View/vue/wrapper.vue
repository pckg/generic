<template>
    <div :id="id" :class="actionClass" :style="actionStyle" @click="componentClicked($event)">
        <slot name="body">
            <frontpage-action-outline :action="action" v-if="action.outline"></frontpage-action-outline>
            <component v-for="a in subactions" :action-id="a.id" :is="'pckg-' + a.type" :key="a.id"></component>
        </slot>
    </div>
</template>

<script>
    export default {
        name: 'pckg-wrapper',
        mixins: [pckgElement]
    }
</script>