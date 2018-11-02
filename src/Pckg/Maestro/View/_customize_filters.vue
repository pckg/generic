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