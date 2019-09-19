<template>
    <div class="pckg-maestro-customize-filters-field">

        <!-- field or relation is selected -->
        <!-- on table orders, 1st level: ordered packets -->
        <pckg-select v-model="selected"
                     :initial-options="options"
                     :initial-multiple="false"
                     :with-empty="' - - select field or relation - - '"
                     class="field-relation inline-block" key="field-relation"></pckg-select>

        <!-- when something is selected we can display some more fields -->
        <template v-if="selection">

            <!-- when field is selected we display field comparators and input for value -->
            <template v-if="isField">
                <pckg-maestro-customize-filters-field-filter v-if="isField && selected"
                                                             type="field"
                                                             :selection="selection"
                                                             :filter="myFilter"
                                                             @filter-value="setFilterValue($event)"
                                                             key="field-filter"
                                                             class="inline-block"></pckg-maestro-customize-filters-field-filter>

            </template>

            <!-- when relation is selected -->
            <template v-else-if="isRelation">
                <template v-if="customizeRelation">
                    <i class="fal fa-cog" @click.prevent="decustomizeRelation" title="Decustomize relation"></i>
                </template>
                <template v-else>
                    <i class="fal fa-cogs" @click.prevent="customizeRelationBtn" title="Customize relation"></i>
                </template>

                <!-- another level when customized -->
                <pckg-maestro-customize-filters-field v-if="customizeRelation"
                                                      :relation="selection"
                                                      v-model="subModel"
                                                      class="inline-block"></pckg-maestro-customize-filters-field>

                <!-- record selection -->
                <pckg-maestro-customize-filters-field-filter v-else
                                                             type="relation"
                                                             :selection="selection"
                                                             v-model="myFilter"
                                                             class="inline-block"></pckg-maestro-customize-filters-field-filter>

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
            let selected = this.getSelected();

            let field = this.getSubmodel();

            let customizeRelation = selected && selected.indexOf('relation-') === 0;

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
                this.selected = this.getSelected();
                this.subModel = this.getSubmodel();
                this.$emit('input', filter);
            },
            subModel: function (subModel) {
                let k = Object.keys(this.myFilter.field)[0];
                let filter = this.myFilter;
                filter.field[k] = subModel;
                this.setFilter(filter);
            },
            selected: function (newValue) {
                if (!newValue || newValue.length == 0) {
                    this.setFilterField(null);
                    return;
                }

                if (newValue.indexOf('field-') === 0) {
                    this.setFilterField(newValue.substr(6));
                    return;
                }

                if (newValue.indexOf('relation-') === 0) {
                    this.customizeRelation = true;
                    var f = {field: {}};
                    let k = newValue.substr(9);
                    f.field[k] = {field: null, value: null, comp: 'is'};
                    this.setFilter(f);
                    return;
                }

                //this.setFilterValue(null);
                //this.setSubFilter({field: '', value: '', comp: 'is'});
            },
        },
        methods: {
            getSelected: function () {
                return this.filter && this.filter.field
                    ? (typeof this.filter.field == 'string'
                            ? 'field-' + this.filter.field
                            : ('relation-' + Object.keys(this.filter.field)[0])
                    ) : '';
            },
            getSubmodel: function () {
                return this.filter && this.filter.field && typeof this.filter.field == 'object'
                    ? this.filter.field[Object.keys(this.filter.field)[0]]
                    : null;
            },
            customizeRelationBtn: function () {
                this.customizeRelation = true;
            },
            decustomizeRelation: function () {
                this.customizeRelation = false;
                this.myFilter.comp = 'in';
            },
            makeSelectedFieldVisible: function () {
                this.$emit('chosen', this.selected);
            },
            setSubFilter: function ($event) {
                this.subModel = $event;
                this.myFilter.field = this.subModel;
                this.$emit('input', this.myFilter);
            },
            setFilter: function (filter) {
                this.$emit('input', filter);
            },
            setFilterValue: function (value) {
                this.myFilter.value = value;
                this.$emit('input', this.myFilter);
            },
            setFilterField: function (field) {
                this.myFilter.field = field;
                if (typeof this.myFilter.field == 'object') {
                    delete this.myFilter.comp;
                    delete this.myFilter.value;
                }
                this.$emit('input', this.myFilter);
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
        created: function () {
            if (!this.filter) {
                this.setFilter({field: null, value: null, comp: 'is'});
            }
        }
    };
</script>