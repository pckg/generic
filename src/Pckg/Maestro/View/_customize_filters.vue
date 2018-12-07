<template>
    <div>
        <h5>
            Filter table
            <pckg-tooltip icon="question-circle"
                          :content="'You can create custom filters for quicker access in future'"></pckg-tooltip>
        </h5>

        <div v-for="filter in filters" class="display-block clear-both" style="padding-bottom: .5rem; height: 3.8rem;">
            <a href="#" title="Remove condition" style="vertical-align: middle;" @click.prevent="removeFilter(filter)">
                <i class="fal fa-minus-circle"></i>
            </a>

            <pckg-maestro-customize-filters-field :parent-fields="myFields"
                                                  :relations="myRelations"
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

            {{ filters }}

            <p><b>User selects</b></p>
            <p>Disabled IS NOT</p>
            <p>Email NOT schtr4jh@schtr4jh.net</p>
            <p>Language IS En, Hr, Sl</p>
            <p>Status IS User</p>
            <p>Mailo open rate < 75</p>
            <p>Mailo open rate > 0</p>
            <p>Email LIKE %gmail.com</p>
            <p>Born at >= 1980-01-01</p>
            <p>Orders -> Added at >= 2018-01-01</p>
            <p>Orders -> Language IN En, Sl</p>
            <p>Orders -> Orders users -> Status IS Confirmed</p>
            <p>Orders -> Orders users -> Packet IS Foo, Bar and Baz</p>
            <p>Orders -> Orders users -> Packet -> Vat NOT Regular</p>

            <p><b>JS dynamic object should be</b></p>
            <p>[</p>
            <p>{field: 'disabled', value: false, comp: 'is'}</p>
            <p>{field: 'email', value: 'schtr4jh@schtr4jh.net', comp: 'not'}</p>
            <p>{field: 'language_id', value: ['en', 'sl', 'hr'], comp: 'in'}</p>
            <p>{field: 'status_id', value: 2, comp: 'is'}</p>
            <p>{field: 'mailo_open_rate', value: 75, comp: 'lessThan'}</p>
            <p>{field: 'mailo_open_rate', value: 0, comp: 'moreThan'}</p>
            <p>{field: 'email', value: '%gmail.com', comp: 'like'}</p>
            <p>{field: 'born_at', value: '1980-01-01', comp: 'moreOrEquals'}</p>
            <p>{field: { orders: { field: 'dt_added', value: '2018-01-01', comp: 'moreOrEquals' }}}</p>
            <p>{field: { orders: { field: 'language_id', value: ['en', 'sl'], comp: 'in' }}}</p>
            <p>{field: { orders: { field: { ordersUsers: { field: 'status_id', value: 'confirmed', comp: 'is' }}}}}</p>
            <p>{field: { orders: { field: { ordersUsers: { field: 'packet_id', value: [1, 3, 6], comp: 'in' }}}}}</p>
            <p>{field: { orders: { field: { ordersUsers: { field: { packet: { field: 'vat_level', value: 'regular', comp: 'not' }}}}}}}</p>
            <p>]</p>

            <p><b>JS builds</b></p>
            <p>(new Users())</p>
            <p>.where('disabled', false)</p>
            <p>.where('email', 'schtr4jh@schtr4jh.net', '!=')</p>
            <p>.where('language_id', ['en', 'sl', 'hr'], 'in')</p>
            <p>.where('status_id', 2)</p>
            <p>.where('mailo_open_rate', 75, '<')</p>
            <p>.where('mailo_open_rate', 0, '>')</p>
            <p>.where('email', '%gmail.com', 'LIKE')</p>
            <p>.where('born_at', '1980-01-01', '>=')</p>
            <p>.where('orders.dt_added', '2018-01-01', '>=')</p>
            <p>.where('orders.language_id', ['en', 'sl'])</p>
            <p>.where('orders.ordersUsers.status_id', 'confirmed')</p>
            <p>.where('orders.ordersUsers.packet_id', [1, 3, 6])</p>
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
            <p>->where('users.status_id', 2)</p>
            <p>->where('users.mailo_open_rate', 75, '<')</p>
            <p>->where('users.mailo_open_rate', 0, '>')</p>
            <p>->where('users.email', '%gmail.com', 'LIKE')</p>
            <p>->where('users.born_at', '1980-01-01', '>=')</p>
            <p><i>->join(function(HasMany $orders){</i></p>
            <p><i> $orders->joinOrdersUsers(function(HasMany $ordersUsers){</i></p>
            <p><i>  $ordersUsers->joinPacket();</i></p>
            <p><i> });</i></p>
            <p><i>});</i></p>
            <p>->where('orders.dt_added', '2018-01-01', '>=')</p>
            <p>->where('orders.language_id', ['en', 'sl'])</p>
            <p>->where('orders_users.status_id', 'confirmed')</p>
            <p>->where('orders_users.packet_id', [1, 3, 6])</p>
            <p>->where('packets.vat_level', 'regular', '!=')</p>
            <p><i>->groupBy('users.id')</i></p>

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
                    {}
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
                this.filters.push({});
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