<template>
    <div :id="'wrapper-' + action.id" :data-action-id="action.id">
        <slot name="body"></slot>
    </div>
</template>

<script>
    export default {
        name: 'pckg-wrapper',
        mixins: [pckgElement]
    }
</script>