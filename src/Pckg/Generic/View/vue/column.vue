<template>
    <div :id="'column-' + action.id" :data-action-id="action.id">
        <slot name="body"></slot>
    </div>
</template>

<script>
    export default {
        name: 'pckg-column',
        mixins: [pckgElement]
    }
</script>