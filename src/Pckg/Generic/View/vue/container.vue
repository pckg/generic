<template>
    <div :id="id" :data-action-id="actionId" :class="actionClass" :style="actionStyle">
        <slot name="body">
            <component v-for="a in subactions" :action-id="a.id" :is="'pckg-' + a.type"></component>
        </slot>
    </div>
</template>

<script>
    export default {
        name: 'pckg-container',
        mixins: [pckgElement]
    }
</script>