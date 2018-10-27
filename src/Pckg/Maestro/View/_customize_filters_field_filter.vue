<template>
    <div class="pckg-maestro-customize-filters-field-filter">

        <pckg-select v-model="operator" :initial-options="initialOptions"
                     :initial-multiple="initialMultiple"></pckg-select>

        <input type="text" class="form-control" v-model="search"/>

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
                mySelection: this.selection,
                operator: 'like',
                /**
                 * All supported operators.
                 */
                operators: {
                    like: 'like',
                    notLike: 'not like',
                    equals: 'equals',
                    notEquals: 'notEquals',
                    in: 'in',
                    notIn: 'notIn',
                    more: 'more',
                    less: 'less',
                    moreOrEquals: 'more or equals',
                    lessOrEquals: 'less or equals',
                    between: 'between'
                },
                search: null
            }
        },
        methods: {},
        computed: {
            fieldType: function () {
                if (selection.fieldType) {
                    return selection.fieldType;
                }
            },
            initialOptions: function () {
                /**
                 * @T00D00 - filter them by type
                 */
                return this.operators;
            },
            initialMultiple: function () {
                /**
                 * @T00D00 - set by type and operator
                 */
                return false;
            }
        },
        created: function () {
        }
    };
</script>