<template>
    <div :id="id" :data-action-id="actionId" :class="actionClass" :style="actionStyle">
        <slot name="body"></slot>
    </div>
</template>

<script>
    export default {
        name: 'pckg-container',
        mixins: [pckgElement]
    }
</script>