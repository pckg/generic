<template>
    <div>
        <h4>
            Filter
            <pckg-tooltip icon="question-circle"
                          :content="'You can create custom filters for quicker access in future'"></pckg-tooltip>

            <div class="pull-right">
                <d-input-checkbox v-model="view.live"></d-input-checkbox>
                Live search
            </div>

            <div class="pull-right">
                <d-input-checkbox v-model="view.archived"></d-input-checkbox>
                Archived items
            </div>

            <div class="pull-right">
                <d-input-checkbox v-model="view.deleted"></d-input-checkbox>
                Deleted items
            </div>
        </h4>
        <div v-for="filter in filters" class="display-block clear-both">
            <a href="#"><i class="fa fa-trash"></i></a>

            <pckg-maestro-customize-filters-field style="width: 100%;" :parent-fields="myFields"
                                                  :relations="myRelations"
                                                  @chosen="chosen"></pckg-maestro-customize-filters-field>

        </div>
        <div class="display-block clear-both">
            <a href="#" @click.prevent="addCondition"><i class="fa fa-plus"></i> Add condition</a>
        </div>

        <h4>
            Group by / statistical view

            <pckg-tooltip icon="question-circle"
                          :content="'Grouping records allo'"></pckg-tooltip>
        </h4>
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
            addCondition: function() {
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