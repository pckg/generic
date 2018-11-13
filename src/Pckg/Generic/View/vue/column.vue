<template>
    <div :id="'column-' + action.id" :data-action-id="action.id" :class="actionClass" :style="actionStyle">
        <slot name="body"></slot>
    </div>
</template>

<script>
    export default {
        name: 'pckg-column',
        mixins: [pckgElement]
    }
</script>