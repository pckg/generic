<template>
    <div class="pckg-maestro-customize-filters-field-filter">

        <pckg-select v-model="myFilter.comp" :initial-options="initialOptions"
                     :initial-multiple="false" key="search" class="inline-block"></pckg-select>

        <div class="inline-block">

            <template v-if="fieldType == 'text'">
                <input type="text" class="form-control" v-model="myFilter.value"/>
            </template>
            <template v-else-if="fieldType == 'number'">
                <input type="number" class="form-control" v-model="myFilter.value"/>
            </template>
            <template v-else-if="fieldType == 'date'">
                <pckg-datetime-picker v-model="myFilter.value" :options="{format: 'YYYY-MM-DD HH:mm'}"></pckg-datetime-picker>
            </template>
            <template v-else-if="fieldType == 'datetime'">
                <pckg-datetime-picker v-model="myFilter.value" :options="{format: 'YYYY-MM-DD'}"></pckg-datetime-picker>
            </template>
            <template v-else-if="fieldType == 'time'">
                <pckg-datetime-picker v-model="myFilter.value" :options="{format: 'HH:mm'}"></pckg-datetime-picker>
            </template>
            <template v-else-if="fieldType == 'checkbox'">
                <input type="checkbox" class="form-control" value="1" v-model="myFilter.value"/>
            </template>
            <template v-else-if="fieldType == 'select'">
                <pckg-select v-model="myFilter.value" key="select-field" :refresh-url="filterUrl" :initial-refresh="true"
                             :initial-multiple="Array.isArray(myFilter.value)"></pckg-select>
            </template>
            <template v-else-if="fieldType == 'relation'">
                <pckg-select v-model="myFilter.value" key="select-relation" :refresh-url="filterUrl" :initial-refresh="true"
                             :initial-multiple="Array.isArray(myFilter.value)"></pckg-select>
            </template>
            <template v-else>
                <pckg-tooltip :content="'Field not supported yet'" icon="question-circle"></pckg-tooltip>
            </template>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'pckg-maestro-customize-filters-field-filter',
        props: {
            type: '',
            selection: {},
            filter: {}
        },
        watch: {
            type: function (newVal) {
                this.myType = newVal;
            },
            selection: function (newVal) {
                this.mySelection = newVal;
            },
            filter: function (newVal) {
                this.myFilter = newVal;
            },
            'myFilter.comp': function (newVal) {
                let arr = ['in', 'notIn'];
                if (arr.indexOf(newVal) >= 0) {
                    if (Array.isArray(this.myFilter.value)) {
                        return;
                    }
                    if (!this.myFilter.value) {
                        this.$emit('filter-value', []);
                        return;
                    }
                    this.$emit('filter-value', [this.myFilter.value]);
                    return;
                } else if (Array.isArray(this.myFilter.value)) {
                    this.$emit('filter-value', this.myFilter.value[0] || null);
                }
            }
        },
        data: function () {
            return {
                myFilter: this.filter,
                myType: this.type,
                mySelection: this.selection,
                /**
                 * All supported operators.
                 */
                operators: {
                    like: 'like',
                    notLike: 'not like',
                    equals: 'is',
                    notEquals: 'is not',
                    in: 'in',
                    notIn: 'not in',
                    more: '>',
                    less: '<',
                    moreOrEquals: '>=',
                    lessOrEquals: '<=',
                },
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
                    if (['email', 'text', 'edit', 'slug', 'hash', 'textarea', 'file', 'picture', 'json', 'pdf', 'geo', 'mysql'].indexOf(this.fetchFieldType) >= 0) {
                        return 'text';
                    }

                    if (['id', 'integer', 'order', 'decimal'].indexOf(this.fetchFieldType) >= 0) {
                        return 'number';
                    }

                    return this.fetchFieldType;
                }
            },
            filterUrl: function () {
                if (this.fieldType != 'select') {
                    return;
                }

                return utils.url('@dynamic.records.field.selectList.none', {
                    table: this.mySelection.dynamic_table_id,
                    field: this.mySelection.id
                });
            },
            selectOptions: function () {
                if (this.fieldType != 'select') {
                    return [];
                }

                if (this.myType == 'relation') {
                    return this.selection.values || [];
                }

                if (this.myType == 'field') {
                    return [];
                }

                return [];
            },
            fetchFieldType: function () {
                if (this.myType == 'field') {
                    return this.mySelection.fieldType.slug;
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

                if (fieldType == 'select') {
                    return this.onlyOperators(['in', 'notIn', 'equals', 'notEquals']);
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