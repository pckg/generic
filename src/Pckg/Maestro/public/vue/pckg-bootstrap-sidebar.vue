<template>
    <div v-if="visible">
        <div class="__rest animated fadeIn"></div>
        <div class="backend-sidebar-panel box-shadow scope-bg-system padding-sm animated slideInRight">
            <a class="pull-right" href="#" @click.prevent="$emit('close')"><i class="fal fa-fw fa-times"></i></a>
            <slot name="header"></slot>
            <slot></slot>
            <slot name="body"></slot>
            <slot name="footer"></slot>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'pckg-bootstrap-sidebar',
        props: {
            visible: {
                default: false
            },
        }
    }
</script>