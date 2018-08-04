<template>
    <div class="modal fade" tabindex="-1" role="dialog" :id="id">
        <div class="modal-dialog" :class="[size ? 'modal-' + size : '']">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="closeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        <slot name="header"></slot>
                    </h4>
                </div>
                <div class="modal-body" v-if="$slots.body">
                    <slot name="body"></slot>
                </div>
                <div class="modal-footer">
                    <slot name="footer"></slot>
                    <button v-if="dismissable" type="button" class="btn btn-default" data-dismiss="modal" @click="closeModal">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'pckg-bootstrap-modal',
        props: {
            dismissable: {
                type: Boolean,
                default: true
            },
            id: null,
            visible: {
                type: Boolean,
                default: false
            },
            size: null
        },
        data: function(){
            return {
                _modal: null
            };
        },
        watch: {
            visible: function (newVal) {
                setTimeout(function(){
                    this.handleModal();
                }.bind(this), 10);
            }
        },
        methods: {
            handleModal: function () {
                $(this.$el).modal(this.visible ? 'show' : 'hide');
            },
            closeModal: function(){
                this.$emit('close');
            }
        },
        mounted: function(){
            $(this.$el).on('hidden.bs.modal', this.closeModal);
        }
    }
</script>