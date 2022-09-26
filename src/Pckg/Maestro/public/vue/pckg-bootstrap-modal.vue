<template>
    <div class="modal fade"
         :class="isVisible ? 'in display-block' : ''"
         tabindex="-1"
         role="dialog"
         :id="id"
         :data-backdrop="closable ? 'true' : 'static'"
         :data-keyboard="closable ? 'true' : 'false'"
         @click.self="closeIfClosable">
        <div v-if="isVisible"
             class="modal-dialog"
             :class="[size ? 'modal-' + size : '']">
            <button v-if="closable"
                    type="button" class="close" data-dismiss="modal" aria-label="Close" @click="closeIfClosable">
                <i class="fas fa-times-circle" aria-hidden="true"></i>
            </button>
            <div class="modal-content">
                <div class="modal-header" v-if="$slots.header || $slots.headerOut">
                    <span class="modal-title" v-if="$slots.header">
                        <slot name="header"></slot>
                    </span>
                    <slot name="headerOut" v-if="$slots.headerOut"></slot>
                </div>
                <div class="modal-body">
                    <slot name="body" v-if="$slots.body"></slot>
                    <slot></slot>
                </div>
                <div class="modal-footer" v-if="$slots.footer">
                    <button v-if="!$slots.footer && dismissable" type="button" class="btn btn-default"
                            :class="$slots.footer ? 'pull-left' : ''"
                            data-dismiss="modal"
                            @click="close">Close
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
        id: {
            default: null
        },
        visible: {
            default: false
        },
        size: {
            default: 'md'
        },
        closable: {
            type: Boolean,
            default: true
        },
    },
    data: function () {
        return {
            isVisible: this.visible
        };
    },
    watch: {
        visible: {
            handler: function (visible) {
                this.isVisible = visible;
            },
            immediate: true,
        },
        isVisible: function (newVal, oldVal) {
            if (newVal) {
                this.open();
            } else {
                this.close();
            }
        }
    },
    methods: {
        closeIfClosable() {
            if (this.closable && this.dismissable) {
                this.shortClose();
            }
        },
        shortClose() {
            this.isVisible = false;
        },
        open() {
            if (!this.isVisible) {
                this.isVisible = true;
                return;
            }

            this.$emit('open');
            $dispatcher.$emit('modal:opened');
            this.$emit('opened');
        },
        close() {
            if (this.isVisible) {
                this.isVisible = false;
                return;
            }

            this.$emit('close');
            $dispatcher.$emit('modal:closed');
            this.$emit('closed');
        },
    },
    beforeDestroy() {
        if (this.isVisible) {
            this.close();
        }
    }
}
</script>
