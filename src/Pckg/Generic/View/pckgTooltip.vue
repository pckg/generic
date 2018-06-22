<template>
    <nobr v-if="content">
        <span
                class="tooltip-questionmark"
                :title="content"
                data-toggle="tooltip">
            <template v-if="icon && icon.length > 0"><i class="far" :class="'fa-' + icon"></i></template>
            <template v-else-if="icon"><i class="far fa-question-circle"></i></template>
        </span>
    </nobr>
</template>

<script>
    export default {
        name: 'pckg-tooltip',
        props: {
            content: {},
            icon: {
                default: '',
                type: String
            },
            visible: false,
            _tooltip: null
        },
        watch: {
            visible: function (newVal) {
                this.isVisible = newVal;
                this._tooltip.tooltip(this.isVisible ? 'show' : 'hide');
            }
        },
        data: function () {
            return {
                isVisible: this.visible
            };
        },
        mounted: function () {
            this.$nextTick(function () {
                this._tooltip = $(this.$el).find('[data-toggle="tooltip"]');
                this._tooltip.tooltip({'position': 'center top'});

                if (this.isVisible) {
                    this._tooltip.tooltip('show');
                }
            }.bind(this));
        }
    }
</script>