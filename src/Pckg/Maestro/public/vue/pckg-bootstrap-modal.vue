<template>
        <div :class="['modal fade', visible ? 'in' : '']" tabindex="-1" role="dialog" :id="id">
            <div class="modal-dialog" :class="[size ? 'modal-' + size : '']">
                <div class="modal-content">
                        <div class="modal-header" v-if="header">
                            <button v-if="dismissable" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><slot name="header"></slot><span v-html="header"></span></h4>
                        </div>
                        <slot name="body">
                            <div v-if="body" class="modal-body" v-html="body"></div>
                        </slot>
                    <div class="modal-footer" v-if="dismissable">
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
            visible: null,
            style: null,
            size: null
        },
        data: function () {
            return {
                _modal: null
            };
        },
        create: function () {
            this.$nextTick(function () {
                this._modal = $(this.$el).modal();

                if (this.visible) {
                    this._modal.modal('show');
                }
            });
        }
    }
</script>