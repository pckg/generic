<template>
    <div :id="'row-' + action.id" :data-action-id="action.id">
        <slot name="body"></slot>
    </div>
</template>

<script>
    export default {
        name: 'pckg-row',
        mixins: [pckgElement]
    }
</script>