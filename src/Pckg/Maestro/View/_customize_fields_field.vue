<template>
    <div>
        <pckg-select v-model="selected"
                     :initial-options="options"
                     :initial-multiple="false"></pckg-select>
        <a v-if="isFinal" href="#" @click.prevent="makeSelectedFieldVisible">Add field</a>
        <pckg-maestro-customize-fields-field v-else-if="isRelation"
                                             :relation="selectedRelation"
                                             @chosen="chosen"></pckg-maestro-customize-fields-field>
    </div>
</template>

<script>
    export default {
        name: 'pckg-maestro-customize-fields-field',
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
                myFields: this.parentFields
            }
        },
        watch: {
            relation: function (newVal) {
                this.fetchRelation();
            }
        },
        methods: {
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