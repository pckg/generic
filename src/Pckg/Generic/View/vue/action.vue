<template>
    <div :id="'action-' + action.id" :data-action-id="action.id" :class="actionClass" :style="actionStyle">
        <slot name="body"></slot>
    </div>
</template>

<script>
    export default {
        name: 'pckg-action',
        mixins: [pckgElement]
    }
</script>