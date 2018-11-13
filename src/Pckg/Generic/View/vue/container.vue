<template>
    <div :id="'container-' + action.id" :data-action-id="action.id" :class="actionClass" :style="actionStyle">
        <slot name="body"></slot>
    </div>
</template>

<script>
    export default {
        name: 'pckg-container',
        mixins: [pckgElement]
    }
</script>