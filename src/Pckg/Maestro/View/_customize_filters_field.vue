<template>
    <div class="pckg-maestro-customize-filters-field">

        <!-- field or relation is selected -->
        <pckg-select v-model="selected"
                     :initial-options="options"
                     :initial-multiple="false"
                     class="field-relation inline-block" key="field-relation"></pckg-select>

        <template v-if="selection">
            <template v-if="isField">

                <!-- when field is selected we display field comparators and input for value -->
                <pckg-maestro-customize-filters-field-filter v-if="selected"
                                                             type="field"
                                                             :selection="selection"
                                                             :filter="myFilter"
                                                             @filter-value="setFilterValue($event)"
                                                             key="field-filter" class="inline-block"></pckg-maestro-customize-filters-field-filter>

            </template>
            <template v-else-if="isRelation">
                <i v-if="customizeRelation" class="fa fa-cog" @click.prevent="customizeRelation = false"></i>
                <i v-else class="fa fa-cogs" @click.prevent="customizeRelation = true"></i>

                <pckg-maestro-customize-filters-field v-if="customizeRelation"
                                                      :relation="selection"
                                                      v-model="subModel"
                                                      @set-filter="setSubFilter($event)"
                                                      @chosen="chosen"
                                                      key="customize-relation" class="inline-block"></pckg-maestro-customize-filters-field>

                <pckg-maestro-customize-filters-field-filter v-else
                                                             type="relation"
                                                             :selection="selection"
                                                             :filter="myFilter"
                                                             key="relation-filter"
                                                             @filter-value="setFilterValue($event)" class="inline-block"></pckg-maestro-customize-filters-field-filter>
            </template>
        </template>

    </div>
</template>

<script>
    export default {
        name: 'pckg-maestro-customize-filters-field',
        props: {
            relation: {
                type: Object,
                default: null
            },
            filterFields: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            relations: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            filter: {}
        },
        model: {
            prop: 'filter'
        },
        data: function () {
            let selected = this.filter && this.filter.field
                ? (typeof this.filter.field == 'string'
                        ? 'field-' + this.filter.field
                        : ('relation-' + Object.keys(this.filter.field)[0])
                ) : '';

            let field = this.filter && this.filter.field && typeof this.filter.field == 'object'
                ? this.filter.field[Object.keys(this.filter.field)[0]]
                : null;

            let customizeRelation = selected && selected.indexOf('relation-') === 0;
            customizeRelation = true;

            return {
                myFilter: this.filter,
                selected: selected,
                myRelations: this.relations,
                myFields: this.filterFields,
                customizeRelation: customizeRelation,
                subModel: field,
            };
        },
        watch: {
            relation: {
                handler: function (newVal) {
                    this.fetchRelation();
                }, immediate: true
            },
            filterFields: function (fields) {
                this.myFields = fields;
            },
            relations: function (relations) {
                this.myRelations = relations;
            },
            filter: function (filter) {
                this.myFilter = filter;
            },
            selected: function (newValue) {
                if (!newValue || newValue.length == 0) {
                    console.log('no selected value, setting filter as null');
                    this.setFilterField(null);
                    return;
                }

                if (newValue.indexOf('field-') === 0) {
                    console.log('field selected, setting value ' + newValue.substr(6))
                    this.setFilterField(newValue.substr(6));
                    return;
                }

                if (newValue.indexOf('relation-') === 0) {
                    console.log('relation selected, setting value ' + newValue.substr(6));
                    let filter = {field:{}};
                    filter.field[newValue.substr(9)] = {field: null, value: null, comp: 'is'};
                    //this.setFilter(filter);
                    this.setFilter(filter);
                    return;
                }

                //this.setFilterValue(null);
                //this.setSubFilter({field: '', value: '', comp: 'is'});
            },
        },
        methods: {
            makeSelectedFieldVisible: function () {
                this.$emit('chosen', this.selected);
            },
            setSubFilter: function ($event) {
                this.subModel = $event;
                this.myFilter.field = this.subModel;
                this.$emit('set-filter', this.myFilter);
            },
            setFilter: function (filter) {
                this.myFilter = filter;
                this.$emit('set-filter', this.myFilter);
            },
            setFilterValue: function (value) {
                this.myFilter.value = value;
                this.$emit('set-filter', this.myFilter);
            },
            setFilterField: function (field) {
                this.myFilter.field = field;
                if (typeof this.myFilter.field == 'object') {
                    delete this.myFilter.comp;
                    delete this.myFilter.value;
                }
                this.$emit('set-filter', this.myFilter);
            },
            chosen: function (chosen) {
                let data = {};
                data[this.selected] = chosen;
                this.$emit('chosen', data);
            },
            fetchRelation: function () {
                if (!this.relation) {
                    return;
                }

                http.getJSON('/api/dynamic/relation/' + this.relation.id + '?with[]=fields&with[]=relations', function (data) {
                    this.myRelations = data.relation.relations;
                    this.myFields = data.relation.fields;
                }.bind(this));
            }
        },
        computed: {
            isStringField: function () {
                return this.myFilter && this.myFilter.field && typeof this.myFilter.field == 'string';
            },
            selectionType: function () {
                if (!this.selected) {
                    return null;
                }

                let minus = this.selected.indexOf('-');
                if (minus === -1) {
                    return null;
                }

                return this.selected.substring(0, minus);
            },
            selection: function () {
                if (!this.selected || this.selected.length == 0) {
                    return false;
                }

                let found = null;
                if (this.selected.indexOf('field-') === 0) {
                    let selected = this.selected.substring(6);
                    $.each(this.myFields, function (i, field) {
                        if (field.field == selected) {
                            found = field;
                            return false;
                        }
                    });
                }

                if (this.selected.indexOf('relation-') === 0) {
                    let selected = this.selected.substring(9);
                    $.each(this.myRelations, function (i, relation) {
                        if (relation.alias == selected) {
                            found = relation;
                            return false;
                        }
                    });
                }

                return found;
            },
            options: function () {
                var options = {
                    fields: {},
                    relations: {},
                };

                $.each(this.myFields, function (i, field) {
                    options.fields['field-' + field.field] = field.title;
                });

                $.each(this.myRelations, function (i, relation) {
                    options.relations['relation-' + relation.alias] = relation.title || relation.alias;
                });

                return options;
            },
            isFinal: function () {
                return this.selected && this.selected.length > 0 && this.selected.indexOf('field-') === 0;
            },
            selectedRelation: function () {
                if (!this.isRelation) {
                    return null;
                }

                var relation = null;
                var selectedId = parseInt(this.selected.substring('relation-'.length));
                $.each(this.myRelations, function (i, r) {
                    if (r.id != selectedId) {
                        return;
                    }

                    relation = r;
                    return false;
                });
                return relation;
            },
            isRelation: function () {
                return this.myFilter && this.myFilter.field && typeof this.myFilter.field == 'object';
                // return this.selected && this.selected.indexOf('relation-') === 0;
            },
            isField: function () {
                return this.myFilter && this.myFilter.field && typeof this.myFilter.field == 'string';
                // return this.selected && this.selected.indexOf('field-') === 0;
            }
        },
        created: function(){
            if (!this.filter) {
                this.setFilter({field: null, value: null, comp: 'equals'});
            }
        }
    };
</script>