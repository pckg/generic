<template>
    <div class="pckg-maestro-customize-filters">
        <h5>
            Change filters
            <pckg-tooltip icon="question-circle"
                          :content="'You can create custom filters for quicker access in future'"></pckg-tooltip>
        </h5>

        <div v-for="(filter, i) in myFilters" class="single-filter" :key="filter.id">
            <a href="#" title="Remove condition" style="vertical-align: middle;"
               @click.prevent="removeFilter(filter)">
                <i class="fal fa-minus-circle"></i>
            </a>

            <pckg-maestro-customize-filters-field v-model="myFilters[i]"
                                                  :filter-fields="myFields"
                                                  :relations="myRelations"></pckg-maestro-customize-filters-field>

        </div>

        <p>
            <a href="#" title="Add condition" @click.prevent="addCondition"><i class="fal fa-plus-circle"></i></a>
        </p>

        <p>
            <d-input-checkbox v-model="realtime" @change="$emit('realtime', $event)"></d-input-checkbox>
            Realtime updates
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
            },
            filters: {
                type: Array,
            }
        },
        watch: {
            filters: function (filters) {
                this.myFilters = filters;
            },
            columns: function (columns) {
                this.myFields = columns;
            },
            relations: function (relations) {
                this.myRelations = relations;
            },
        },
        data: function () {
            return {
                myFilters: this.filters,
                myFields: this.columns,
                myRelations: this.relations,
                view: {
                    archived: false,
                    deleted: false
                },
                realtime: false
            };
        },
        methods: {
            removeFilter: function (filter) {
                utils.splice(this.myFilters, filter);
            },
            addCondition: function () {
                this.myFilters.push({field: null, value: [], comp: 'in', id: Math.random()});
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