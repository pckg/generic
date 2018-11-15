<template>
    <div :id="'row-' + action.id" :data-action-id="action.id" :class="actionClass" :style="actionStyle">
        <slot name="body">
            <component v-for="a in subactions" :action-id="a.id" :is="'pckg-' + a.type"></component>
        </slot>
    </div>
</template>

<script>
    export default {
        name: 'pckg-row',
        mixins: [pckgElement]
    }
</script>