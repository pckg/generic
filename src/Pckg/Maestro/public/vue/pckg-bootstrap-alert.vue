<template>
    <div :class="['fade in alert', 'alert-' + style, dismissable ? 'alert-dismissable' : '']" role="alert">
        <button v-if="dismissable" type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        ${ text }
    </div>
</template>

<script>
    export default {
        mixins: [pckgDelimiters],
        name: 'pckg-bootstrap-alert',
        props: {
            style: {
                type: String,
                default: 'success'
            },
            text: {
                type: String,
                default: ''
            },
            dismissable: {
                type: Boolean,
                default: true
            }
        },
        data: function () {
            return {};
        },
        methods: {}
    }
</script>