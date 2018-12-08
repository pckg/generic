<template>
    <div class="pckg-maestro-customize-filters">
        <h5>
            Change filters
            <pckg-tooltip icon="question-circle"
                          :content="'You can create custom filters for quicker access in future'"></pckg-tooltip>
        </h5>

        <div v-for="(filter, i) in Object.keys(filters)" class="single-filter">
            <a href="#" title="Remove condition" style="vertical-align: middle;"
               @click.prevent="removeFilter(filters[filter])">
                <i class="fal fa-minus-circle"></i>
            </a>

            <pckg-maestro-customize-filters-field v-model="filters[i]"
                                                  :filter-fields="myFields"
                                                  :relations="myRelations"
                                                  @set-filter="filters[i] = $event"
                                                  @chosen="chosen"></pckg-maestro-customize-filters-field>

        </div>

        <p>
            <a href="#" title="Add condition" @click.prevent="addCondition"><i class="fal fa-plus-circle"></i></a>
        </p>

        <p>
            <d-input-checkbox v-model="view.live"></d-input-checkbox>
            Live search
        </p>

        <p>
            <d-input-checkbox v-model="view.archived"></d-input-checkbox>
            Archived items
        </p>

        <p>
            <d-input-checkbox v-model="view.deleted"></d-input-checkbox>
            Deleted items
        </p>

        <h5>
            Group by / statistical view

            <pckg-tooltip icon="question-circle"
                          :content="'Grouping records allo'"></pckg-tooltip>

            <div v-if="false">
                {{ filters }}

                <p><b>User selects</b></p>
                <p>Email NOT schtr4jh@schtr4jh.net</p>
                <p>User group IS User</p>
                <p>User group -> Title IS User</p>
                <p>Mailo open rate < 75</p>
                <p>Mailo open rate > 0</p>
                <p>Email LIKE %gmail.com</p>
                <p>Born at >= 1980-01-01</p>
                <p>Orders users -> Added at >= 2018-01-01</p>
                <p>Orders -> Added at >= 2018-01-01</p>
                <p>Orders -> Language IN En, Sl</p>
                <p>Orders -> Orders users -> Status IS Confirmed</p>
                <p>Orders -> Orders users -> Packet IS Foo, Bar and Baz</p>
                <p>Orders -> Orders users -> Packet -> Vat NOT Regular</p>

                <p><b>JS dynamic object should be</b></p>
                <p>[</p>
                <p>{field: 'email', value: 'schtr4jh@schtr4jh.net', comp: 'notEquals'}</p>
                <p>{field: 'user_group_id', value: 2, comp: 'equals'}</p>
                <p>{field: { userGroup: { field: 'id', value: 2, comp: 'equals' } }}}</p>
                <p>{field: 'mailo_open_rate', value: 75, comp: 'less'}</p>
                <p>{field: 'mailo_open_rate', value: 0, comp: 'more'}</p>
                <p>{field: 'email', value: '%gmail.com', comp: 'like'}</p>
                <p>{field: 'dt_birth', value: '1980-01-01', comp: 'moreOrEquals'}</p>
                <p>{field: { ordersUsers: { field: 'dt_added', value: '2018-01-01', comp: 'moreOrEquals' }}}</p>
                <p>{field: { orders: { field: 'dt_added', value: '2018-01-01', comp: 'moreOrEquals' }}}</p>
                <p>{field: { orders: { field: 'language_id', value: ['en', 'sl'], comp: 'in' }}}</p>
                <p>{field: { orders: { field: { ordersUsers: { field: 'status_id', value: 'confirmed', comp: 'equals'
                    }}}}}</p>
                <p>{field: { orders: { field: { ordersUsers: { field: 'packet_id', value: [3, 11, 12], comp: 'in'
                    }}}}}</p>
                <p>{field: { orders: { field: { ordersUsers: { field: { packet: { field: 'vat_level', value: 'regular',
                    comp: 'notEquals' }}}}}}}</p>
                <p>]</p>

                <p><b>JS builds</b></p>
                <p>(new Users())</p>
                <p>.where('email', 'schtr4jh@schtr4jh.net', '!=')</p>
                <p>.where('user_group_id', 2)</p>
                <p>.where('userGroup.title', 'User')</p>
                <p>.where('mailo_open_rate', 75, '<')</p>
                <p>.where('mailo_open_rate', 0, '>')</p>
                <p>.where('email', '%gmail.com', 'LIKE')</p>
                <p>.where('dt_birth', '1980-01-01', '>=')</p>
                <p>.where('orders.dt_added', '2018-01-01', '>=')</p>
                <p>.where('ordersUsers.dt_added', '2018-01-01', '>=')</p>
                <p>.where('orders.language_id', ['en', 'sl'])</p>
                <p>.where('orders.ordersUsers.status_id', 'confirmed')</p>
                <p>.where('orders.ordersUsers.packet_id', [3, 11, 12])</p>
                <p>.where('orders.ordersUsers.packet.vat_level', 'regular', '!=')</p>
                <p>.limit(100)</p>
                <p>.page(3)</p>
                <p>.all()</p>

                <p><b>HTTP REST gets</b></p>
                <p>/api/dynamic/tables/list/1 ? perPage=100 & page=3</p>
                <p></p>

                <p><b>HTTP QL gets</b></p>
                <p>/api/dynamic/tables/list/1 BODY {paginator:{perPage:100,page:3}, fields: ..., filters: ...</p>
                <p></p>

                <p><b>MySQL performs</b></p>
                <p>(new Users())</p>
                <p>->where('users.disabled', false)</p>
                <p>->where('users.email', 'schtr4jh@schtr4jh.net', '!=')</p>
                <p>->where('users.user_group_id', 2)</p>
                <p>->where('userGroup.title', 'User')</p>
                <p>->where('users.mailo_open_rate', 75, '<')</p>
                <p>->where('users.mailo_open_rate', 0, '>')</p>
                <p>->where('users.email', '%gmail.com', 'LIKE')</p>
                <p>->where('users.dt_birth', '1980-01-01', '>=')</p>
                <p><i>->join(function(HasMany $orders){</i></p>
                <p><i> $orders->joinOrdersUsers(function(HasMany $ordersUsers){</i></p>
                <p><i> $ordersUsers->joinPacket();</i></p>
                <p><i> });</i></p>
                <p><i>});</i></p>
                <p>->where('orders.dt_added', '2018-01-01', '>=')</p>
                <p>->where('orders.language_id', ['en', 'sl'])</p>
                <p>->where('orders_users.status_id', 'confirmed')</p>
                <p>->where('orders_users.packet_id', [3, 11, 12])</p>
                <p>->where('packets.vat_level', 'regular', '!=')</p>
                <p><i>->groupBy('users.id')</i></p>
            </div>

        </h5>
    </div>
</template>

<script>
    export default {
        name: 'pckg-maestro-customize-filters',
        props: {
            columns: {
                type: Array,
            },
            relations: {
                type: Array,
            }
        },
        watch: {
            columns: function (columns) {
                this.myFields = columns;
            },
            relations: function (relations) {
                this.myRelations = relations;
            }
        },
        data: function () {
            return {
                filters: [
                    {field: 'email', value: 'schtr4jh@schtr4jh.net', comp: 'notEquals'},
                    {field: 'user_group_id', value: 2, comp: 'equals'},
                    {field: {userGroup: {field: 'title', value: 'User', comp: 'equals'}}},
                    {field: 'mailo_open_rate', value: 75, comp: 'less'},
                    {field: 'mailo_open_rate', value: 0, comp: 'more'},
                    {field: 'email', value: '%gmail.com', comp: 'like'},
                    {field: 'dt_birth', value: '1980-01-01', comp: 'moreOrEquals'},
                    {field: {ordersUsers: {field: 'dt_added', value: '2018-01-01', comp: 'moreOrEquals'}}},
                    {field: {orders: {field: 'dt_added', value: '2018-01-01', comp: 'moreOrEquals'}}},
                    {field: {orders: {field: 'language_id', value: ['en', 'sl'], comp: 'in'}}},
                    {field: {orders: {field: {ordersUsers: {field: 'status_id', value: 'confirmed', comp: 'equals'}}}}},
                    {field: {orders: {field: {ordersUsers: {field: 'packet_id', value: [3, 11, 12], comp: 'in'}}}}},
                    {field: {orders: {field: {ordersUsers: {field: 'packet_id', value: 11, comp: 'equals'}}}}},
                    {
                        field: {
                            orders: {
                                field: {
                                    ordersUsers: {
                                        field: {
                                            packet: {
                                                field: 'vat_level',
                                                value: 'regular',
                                                comp: 'notEquals'
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                ],
                myFields: this.columns,
                myRelations: this.relations,
                view: {
                    archived: false,
                    deleted: false,
                    live: true
                }
            };
        },
        methods: {
            removeFilter: function (filter) {
                utils.splice(this.filters, filter);
            },
            addCondition: function () {
                this.filters.push({field: null, value: null, comp: 'is'});
            },
            chosen: function () {

            }
        },
        computed: {
            selectOptions: function () {
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
            }
        }
    };
</script>