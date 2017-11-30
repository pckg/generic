<template>
    <nobr v-if="content">&nbsp;<span
                class="tooltip-questionmark"
                :title="content"
                data-toggle="tooltip">
            <template v-if="icon.length > 0"><i class="fa" :class="'fa-' + icon"></i></template><template v-else>?</template>
        </span>
    </nobr>
</template>

<script>
    export default {
        mixins: [pckgDelimiters],
        name: 'pckg-tooltip',
        props: {
            content: {},
            icon: {
                default: '',
                type: String
            }
        },
        mounted: function () {
            $(this.$el).find('[data-toggle="tooltip"]').tooltip({'position': 'auto top'});
        }
    }
</script>