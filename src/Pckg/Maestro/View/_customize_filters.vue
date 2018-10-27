<template>
    <div>
        <h4>
            Filter
            <pckg-tooltip icon="question-circle"
                          :content="'You can create custom filters for quicker access in future'"></pckg-tooltip>

            <div class="pull-right">
                <d-input-checkbox v-model="view.archived"></d-input-checkbox>
                Archived items
            </div>

            <div class="pull-right">
                <d-input-checkbox v-model="view.deleted"></d-input-checkbox>
                Deleted items
            </div>
        </h4>
        <div v-for="filter in filters" style="display: block; clear: both;">
            <div>
                <a href="#"><i class="fa fa-trash"></i></a>
            </div>
            <div>

                <pckg-maestro-customize-filters-field :parent-fields="myFields"
                                                      :relations="myRelations"
                                                      @chosen="chosen"></pckg-maestro-customize-filters-field>

            </div>
        </div>
        <div>
            <pckg-select :initial-options="selectOptions" :initial-multiple="false"></pckg-select>
        </div>
        <!--
        <h4>
            Group by / statistical view
            <pckg-tooltip icon="question-circle"
                          :content="'You can group records by fields and displa'"></pckg-tooltip>
        </h4>
        -->
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
                view: {}
            };
        },
        methods: {
            chosen: function(){

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