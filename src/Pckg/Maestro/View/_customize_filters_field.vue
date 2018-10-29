<template>
    <div class="pckg-maestro-customize-filters-field">

        <pckg-select v-model="selected"
                     :initial-options="options"
                     :initial-multiple="false"
                     class="field-relation"></pckg-select>

        <template v-if="isRelation">
            <i class="fa fa-cogs" @click.prevent="customizeRelation = !customizeRelation"></i>

            <pckg-maestro-customize-filters-field v-if="customizeRelation"
                                                  :relation="selectedRelation"
                                                  @chosen="chosen"></pckg-maestro-customize-filters-field>

            <pckg-maestro-customize-filters-field-filter v-else
                                                         type="relation"
                                                         :selection="selection"></pckg-maestro-customize-filters-field-filter>
        </template>
        <template v-else>

            <pckg-maestro-customize-filters-field-filter v-if="selected"
                                                         type="field"
                                                         :selection="selection"></pckg-maestro-customize-filters-field-filter>

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
            parentFields: {
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
            }
        },
        data: function () {
            return {
                selected: null,
                myRelations: this.relations,
                myFields: this.parentFields,
                customizeRelation: false
            }
        },
        watch: {
            relation: function (newVal) {
                this.fetchRelation();
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
            selectionType: function () {
                let minus = this.selected.indexOf('-');
                if (minus === -1) {
                    return null;
                }

                return this.selected.substring(0, minus);
            },
            selection: function () {
                if (!this.selected || this.selected.length == 0) {
                    return null;
                }

                let found = null;
                if (this.selected.indexOf('field-') === 0) {
                    let selected = this.selected.substring(6);
                    $.each(this.myFields, function (i, field) {
                        if (field.id == selected) {
                            found = field;
                            return false;
                        }
                    });
                }

                if (this.selected.indexOf('relation-') === 0) {
                    let selected = this.selected.substring(9);
                    $.each(this.myRelations, function (i, relation) {
                        if (relation.id == selected) {
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
                    options.fields['field-' + field.id] = field.title;
                });

                $.each(this.myRelations, function (i, relation) {
                    options.relations['relation-' + relation.id] = relation.title;
                });

                return options;
            },
            isFinal: function () {
                return this.selected && this.selected.length > 0 && this.selected.indexOf('field-') === 0;
            },
            selectedRelation: function () {
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
            }
        },
        created: function () {
            if (this.relation) {
                this.fetchRelation();
            }
        }
    };
</script>