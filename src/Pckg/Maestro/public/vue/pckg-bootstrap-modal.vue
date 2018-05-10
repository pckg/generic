<template>
    <div class="modal fade" tabindex="-1" role="dialog" :id="id">
        <div class="modal-dialog" :class="[size ? 'modal-' + size : '']">
            <div class="modal-content">
                <div class="modal-header">
                    <button v-if="dismissable" type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">
                        <slot name="header"></slot>
                    </h4>
                </div>
                <slot name="body">
                    <div class="modal-body">
                        <slot></slot>
                    </div>
                </slot>
                <div class="modal-footer">
                    <slot name="footer"></slot>
                    <button v-if="dismissable" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'pckg-bootstrap-modal',
        props: {
            header: null,
            body: null,
            dismissable: true,
            id: null,
            visible: {
                type: Boolean,
                default: false
            },
            size: null
        },
        watch: {
            visible: function (newVal) {
                this.handleModal();
            }
        },
        methods: {
            handleModal: function () {
                $(this.$el).modal(this.visible ? 'show' : 'hide');
            }
        },
        data: function () {
            return {
                _modal: null
            };
        }
    }
</script>