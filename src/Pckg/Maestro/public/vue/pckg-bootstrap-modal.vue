<template>
    <div v-if="visible"
         class="modal fade"
         :class="visible ? 'in display-block' : ''"
         tabindex="-1"
         role="dialog"
         :id="id"
         :data-backdrop="closable ? 'true' : 'static'"
         :data-keyboard="closable ? 'true' : 'false'">
        <div class="modal-dialog" :class="[size ? 'modal-' + size : '']">
            <div class="modal-content">
                <div class="modal-header" v-if="$slots.header || $slots.headerOut">
                    <button v-if="closable" type="button" class="close" data-dismiss="modal" aria-label="Close" @click="closeModal">
                        <i class="far fa-times" aria-hidden="true"></i>
                    </button>
                    <h4 class="modal-title" v-if="$slots.header">
                        <slot name="header"></slot>
                    </h4>
                    <slot name="headerOut" v-if="$slots.headerOut"></slot>
                </div>
                <div class="modal-body" v-if="$slots.default">
                    <slot></slot>
                </div>
                <div class="modal-body" v-else-if="$slots.body">
                    <slot name="body"></slot>
                </div>
                <div class="modal-footer" v-if="$slots.footer">
                    <button v-if="!$slots.footer && dismissable" type="button" class="btn btn-default"
                            :class="$slots.footer ? 'pull-left' : ''"
                            data-dismiss="modal"
                            @click="closeModal">Close
                    </button>
                    <slot name="footer"></slot>
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
                default: false
            },
            size: null,
            closable: {
                type: Boolean,
                default: true
            },
        },
        data: function () {
            return {
                _modal: null
            };
        },
        watch: {
            visible: function (newVal) {
                this.$nextTick(function () {
                    this.handleModal();
                }.bind(this));
            }
        },
        methods: {
            handleModal: function () {
                this.$nextTick(function () {
                    $(this.$el).modal(this.visible ? 'show' : 'hide');
                    $(window).resize();
                }.bind(this));
            },
            closeModal: function () {
                this.$emit('close');
            },
            closedModal: function () {
                this.$emit('closed');
            },
            modalOpened: function () {
                $(window).resize();
            },
            modalOpening: function () {
                $(window).resize();
            }
        },
        mounted: function () {
            $(this.$el).on('hide.bs.modal', function () {
                this.closeModal();
            }.bind(this));
            $(this.$el).on('hidden.bs.modal', function () {
                this.closedModal();
            }.bind(this));
            $(this.$el).on('shown.bs.modal', this.modalOpened);
            $(this.$el).on('show.bs.modal', this.modalOpening);
            if (this.visible) {
                this.$nextTick(function () {
                    setTimeout(function () {
                        this.handleModal();
                    }.bind(this), 100);
                }.bind(this));
            }
        }
    }
</script>