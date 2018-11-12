<template>
    <div :id="'container-' + action.id" :data-action-id="action.id">
        <slot name="body"></slot>
    </div>
</template>

<script>
    export default {
        name: 'pckg-container',
        mixins: [pckgElement]
    }
</script>