<template>
    <div>
        {{ selection.fieldType ? selection.fieldType.slug : selection.dynamic_relation_type_id }}
    </div>
</template>

<script>
    export default {
        name: 'pckg-maestro-customize-filters-field-filter',
        props: {
            type: '',
            selection: {},
        },
        watch: {
            type: function (newVal) {
                this.myType = newVal;
            },
            selection: function (newVal) {
                this.mySelection = newVal;
            }
        },
        data: function () {
            return {
                myType: this.type,
                mySelection: this.selection
            }
        },
        methods: {},
        computed: {},
        created: function () {
        }
    };
</script>