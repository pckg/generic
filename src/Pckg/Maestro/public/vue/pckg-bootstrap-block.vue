<template>
    <div class="c-pckg-bootstrap-block animated fadeIn" :class="visible ? 'display-block' : 'hidden'" :id="id">
        <div class="display-block" v-if="$slots.header || $slots.headerOut">
            <button v-if="closable" type="button" class="close" data-dismiss="modal" aria-label="Close"
                    @click="closeModal">
                <i class="far fa-times" aria-hidden="true"></i>
            </button>
            <h4 class="modal-title" v-if="$slots.header">
                <slot name="header"></slot>
            </h4>
            <slot name="headerOut" v-if="$slots.headerOut"></slot>
            <hr />
        </div>
        <div class="display-block" v-if="$slots.body">
            <slot name="body"></slot>
        </div>
        <div class="display-block" v-if="$slots.footer">
            <button v-if="!$slots.footer && dismissable" type="button" class="btn btn-default"
                    :class="$slots.footer ? 'pull-left' : ''"
                    data-dismiss="modal"
                    @click="closeModal">Close
            </button>
            <slot name="footer"></slot>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        visible: {
            type: Boolean,
            default: false
        }
    }
}
</script>