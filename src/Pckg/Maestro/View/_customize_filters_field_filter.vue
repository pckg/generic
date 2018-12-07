<template>
    <div class="pckg-maestro-customize-filters-field-filter">

        <pckg-select v-model="operator" :initial-options="initialOptions"
                     :initial-multiple="false"></pckg-select>

        <template v-if="fieldType == 'text'">
            <input type="text" class="form-control" v-model="search"/>
        </template>
        <template v-else-if="fieldType == 'number'">
            <input type="number" class="form-control" v-model="search"/>
        </template>
        <template v-else-if="fieldType == 'date'">
            <pckg-datetime v-model="search" format="YYYY-MM-DD HH:mm"></pckg-datetime>
        </template>
        <template v-else-if="fieldType == 'datetime'">
            <pckg-datetime v-model="search" format="YYYY-MM-DD"></pckg-datetime>
        </template>
        <template v-else-if="fieldType == 'time'">
            <pckg-datetime v-model="search" format="HH:mm"></pckg-datetime>
        </template>
        <template v-else-if="fieldType == 'checkbox'">
            <input type="checkbox" class="form-control" value="1" v-model="search"/>
        </template>
        <template v-else-if="fieldType == 'select'">
            <pckg-select v-model="search"></pckg-select>
        </template>
        <template v-else>
            <pckg-tooltip :content="'Field not supported yet'" icon="question-circle"></pckg-tooltip>
        </template>
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
                    notIn: 'not in',
                    more: '> - more than',
                    less: '< - less than',
                    moreOrEquals: '>= - more than or equals',
                    lessOrEquals: '<= - less than or equals',
                },
                search: null
            }
        },
        methods: {
            removeOperators: function (remove) {
                let operators = {};
                $.each(this.operators, function (key, title) {
                    if (remove.indexOf(key) >= 0) {
                        return;
                    }

                    operators[key] = title;
                });
                return operators;
            },
            onlyOperators: function (only) {
                let operators = {};
                $.each(this.operators, function (key, title) {
                    if (only.indexOf(key) < 0) {
                        return;
                    }

                    operators[key] = title;
                });
                return operators;
            },
        },
        computed: {
            selectionOperators: function () {
                let type = this.fieldType;
            },
            fieldType: function () {
                if (this.myType == 'field') {
                    if (['email', 'text', 'edit', 'slug', 'hash', 'textarea', 'file', 'picture', 'json', 'pdf', 'geo', 'mysql'].indexOf(this.selection.fieldType.slug) >= 0) {
                        return 'text';
                    }

                    if (['id', 'integer', 'order', 'decimal'].indexOf(this.selection.fieldType.slug) >= 0) {
                        return 'number';
                    }

                    return this.selection.fieldType.slug;
                }
            },
            initialOptions: function () {
                let fieldType = this.fieldType;
                if (fieldType == 'number') {
                    return this.removeOperators(['notIn', 'in', 'like', 'notLike']);
                }

                if (fieldType == 'text') {
                    return this.onlyOperators(['equals', 'notEquals', 'like', 'notLike']);
                }

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