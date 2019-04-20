<template>
    <div class="pckg-maestro-customize-fields-field">
        <pckg-select v-model="selected"
                     :initial-options="options"
                     :initial-multiple="false" @change="checkFinal" :with-empty="'Select column'"></pckg-select>
        <pckg-maestro-customize-fields-field v-if="!isFinal && isRelation"
                                             :relation="selectedRelation"
                                             :columns="columns"
                                             @chosen="chosen"
                                             @remove="$emit('remove', $event)"></pckg-maestro-customize-fields-field>
    </div>
</template>

<script>
    export default {
        name: 'pckg-maestro-customize-fields-field',
        props: {
            columns: {
                type: Array,
                default: function(){
                    return [];
                }
            },
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
                myFields: this.parentFields
            }
        },
        watch: {
            relation: function (newVal) {
                this.fetchRelation();
            },
            relations: function (relations) {
                this.myRelations = relations;
            },
            parentFields: function (fields) {
                this.myFields = fields;
            }
        },
        methods: {
            checkFinal: function () {
                this.$nextTick(function () {
                    if (!this.isFinal) {
                        return;
                    }

                    this.makeSelectedFieldVisible();
                }.bind(this));
            },
            removeColumn: function (column) {
                this.$emit('remove');
            },
            test: function () {
                $.each(this.relations, function (i, relation) {
                    fields['relation-' + relation.id] = relation;
                    /**
                     * Belongs to relations (user on orders) has posibility of showing separate fields
                     * (orders -> user.email, user.name; units -> unit.title, unit.available_from)
                     * directly in table.
                     *
                     * #order.id #order.num #order.user.email #order.user.name
                     *
                     * Has many relations (orders on users) has posibility of showing grouped fields
                     * (users -> order.num, order.date; offers -> packet.title, packet.price
                     * directly in table.
                     *
                     * #user.id #user.email
                     *  - (#user.orders.num #user.orders.created_at)*
                     *  - (#user.ordersUsers.packet.quantity #user.ordersUsers.packet.offer.category.title)*
                     *  - (#user.newslettersUsers.newsletter.title #user.newslettersUsers.created_at)*
                     *
                     * #order.id #order.num
                     *  - (#order.ordersUsers.ordersUsersItems.quantity #order.ordersUsers.ordersUsersItems.packetsItem.item.title)
                     */
                });
            },
            makeSelectedFieldVisible: function () {
                let field = this.selectedField;
                if (!field) {
                    console.log('no field');
                    return;
                }
                field.frozen = false;
                field.type = 'field';

                this.$emit('chosen', {field: field.field});
            },
            chosen: function (chosen) {
                let onRelation = this.selected.indexOf('relation-') === 0;
                if (onRelation) {
                    let e = {};
                    e[this.selectedRelation.alias] = {field: chosen};
                    this.$emit('chosen', e);
                    return;
                }
                this.$emit('chosen', {field: chosen});
            },
            fetchRelation: function () {
                http.getJSON('/api/dynamic/relation/' + this.relation.id + '?with[]=fields&with[]=relations', function (data) {
                    this.myRelations = data.relation.relations;
                    this.myFields = data.relation.fields;
                }.bind(this));
            }
        },
        computed: {
            options: function () {
                var options = {
                    fields: {},
                    relations: {},
                };

                let selectedFields = this.columns.map(function(column){ return column.field; });
                $.each(this.myFields, function (i, field) {
                    if (selectedFields.indexOf(field.field) >= 0) {
                        return;
                    }

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
                if (this.selected.indexOf('relation-') !== 0) {
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
            selectedField: function () {
                if (this.selected.indexOf('field-') !== 0) {
                    return null;
                }

                var field = null;
                var selectedId = parseInt(this.selected.substring('field-'.length));
                $.each(this.myFields, function (i, f) {
                    if (f.id != selectedId) {
                        return;
                    }

                    field = f;
                    return false;
                });

                return field;
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