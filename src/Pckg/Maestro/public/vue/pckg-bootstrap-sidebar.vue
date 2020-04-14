<template>
    <div v-if="visible">
        <div class="__rest animated fadeIn"></div>
        <div class="backend-sidebar-panel box-shadow scope-bg-system padding-sm animated slideInRight">
            <div class="modal-header" v-if="$slots.header || $slots.headerOut">
                <slot name="header"></slot>
                <a class="pull-right" href="#" @click.prevent="$emit('close')"><i class="fal fa-fw fa-times"></i></a>
            </div>
            <div class="modal-body" v-if="$slots.body">
                <slot name="body"></slot>
            </div>
            <div class="modal-footer" v-if="$slots.footer">
                <slot name="footer"></slot>
            </div>
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