<template>
    <component :is="tag" v-if="content">
        <span
                class="tooltip-questionmark"
                :title="content"
                data-toggle="tooltip">
            <span v-if="text">{{ text }}</span>
            <template v-if="icon && icon.length > 0"><i class="far" :class="'fa-' + icon"></i></template>
            <template v-else-if="icon"><i class="far fa-question-circle"></i></template>
        </span>
    </component>
</template>

<script>
    export default {
        name: 'pckg-tooltip',
        props: {
            tag: {
                default: 'nobr',
            },
            content: {},
            text: {},
            icon: {
                default: '',
                type: String
            },
            visible: false
        },
        watch: {
            visible: function (newVal) {
                this.isVisible = newVal;
                this._tooltip.tooltip(this.isVisible ? 'show' : 'hide');
            }
        },
        data: function () {
            return {
                isVisible: this.visible,
                _tooltip: null
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