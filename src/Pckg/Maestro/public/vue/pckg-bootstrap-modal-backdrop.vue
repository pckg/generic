<template>
    <div v-if="visible"
         class="modal-backdrop fade in"
         :data-modals="count"></div>
</template>

<script>
export default {
    data: function () {
        return {
            count: 0,
        };
    },
    methods: {
        plus() {
            this.count++;
        },
        minus() {
            this.count--;
        }
    },
    computed: {
        visible() {
            return this.count > 0;
        }
    },
    watch: {
        visible: {
            handler(visible) {
                if (visible) {
                    $('body').addClass('modal-opan');
                } else {
                    $('body').removeClass('modal-opan');
                }
            }
        }
    },
    created() {
        $dispatcher.$on('modal:opened', this.plus);
        $dispatcher.$on('modal:closed', this.minus);
    },
    beforeDestroy() {
        $dispatcher.$off('modal:opened', this.plus);
        $dispatcher.$off('modal:closed', this.minus);
    }
}
</script>
