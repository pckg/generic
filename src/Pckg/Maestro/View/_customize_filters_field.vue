<template>
    <div class="pckg-maestro-customize-filters-field">

        <!-- field or relation is selected -->
        <pckg-select v-model="selected"
                     :initial-options="options"
                     :initial-multiple="false"
                     class="field-relation"></pckg-select>

        <template v-if="selection">
            <template v-if="isRelation">
                <i class="fa fa-cogs" @click.prevent="customizeRelation = !customizeRelation"></i>

                <pckg-maestro-customize-filters-field v-if="customizeRelation"
                                                      :relation="selectedRelation"
                                                      @chosen="chosen"></pckg-maestro-customize-filters-field>

                <pckg-maestro-customize-filters-field-filter v-else
                                                             type="relation"
                                                             :selection="selection"></pckg-maestro-customize-filters-field-filter>
            </template>
            <template v-else-if="isField">

                <!-- when field is selected we display field comparators and input for value -->
                <pckg-maestro-customize-filters-field-filter v-if="selected"
                                                             type="field"
                                                             :selection="selection"
                                                             :filter="filter"></pckg-maestro-customize-filters-field-filter>

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
            return {
                selected: this.filter.field ? (typeof this.filter.field == 'string'
                    ? 'field-' + this.filter.field
                    : ('relation-' + Object.keys(this.filter.field)[0])) : null,
                myRelations: this.relations,
                myFields: this.filterFields,
                customizeRelation: false
            }
        },
        watch: {
            relation: function (newVal) {
                this.fetchRelation();
            },
            filterFields: function (fields) {
                this.myFields = fields;
            },
            relations: function (relations) {
                this.myRelations = relations;
            }
        },
        methods: {
            makeSelectedFieldVisible: function () {
                this.$emit('chosen', this.selected);
            },
            chosen: function (chosen) {
                let data = {};
                data[this.selected] = chosen;
                this.$emit('chosen', data);
            },
            fetchRelation: function () {
                http.getJSON('/api/dynamic/relation/' + this.relation.id + '?with[]=fields&with[]=relations', function (data) {
                    this.myRelations = data.relation.relations;
                    this.myFields = data.relation.fields;
                }.bind(this));
            }
        },
        computed: {
            isStringField: function () {
                return this.filter && this.filter.field && typeof this.filter.field == 'string';
            },
            selectionType: function () {
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
                    options.relations['relation-' + relation.alias] = relation.title;
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
                return this.selected && this.selected.indexOf('relation-') === 0;
            },
            isField: function () {
                return this.selected && this.selected.indexOf('field-') === 0;
            }
        },
        created: function () {
            if (this.relation) {
                this.fetchRelation();
            }
        }
    };
</script>