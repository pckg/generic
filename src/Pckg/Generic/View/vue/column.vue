<template>
    <div :id="'column-' + action.id" :data-action-id="action.id" :class="actionClass" :style="actionStyle">
        <slot name="body">
            <component v-for="a in subactions" :action-id="a.id" :is="'pckg-' + a.type" :key="a.id"></component>
        </slot>
    </div>
</template>

<script>
    export default {
        name: 'pckg-column',
        mixins: [pckgElement]
    }
</script>