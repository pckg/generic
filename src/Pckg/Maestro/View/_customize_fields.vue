<template>
    <div>
        <h5>
            Modify columns
            <pckg-tooltip icon="question-circle"
                          :content="'Select fields you would like to see, reorder and freeze them'"></pckg-tooltip>
        </h5>

        <draggable v-model="myColumns"
                   handle=".move-handle"
                   animation="333"
                   @end="emitTreeChange"
                   v-if="Object.keys(myColumns).length > 0"
        >

            <div v-for="(column, i) in myColumns" :key="i">
                <a href="#"><i class="fal fa-minus-circle" @click.prevent="remove(column)"></i></a>
                {{ getColumnTitle(column) }}
                <a href="#" style="cursor: move;" class="move-handle"><i style="margin-left: .5rem;" class="pull-right fal fa-arrows"></i></a>
                <a href="#" v-if="column.freeze">
                    <i class="pull-right fal fa-thumbtack"
                       @click.prevent="column.freeze = false"></i>
                </a>
                <a href="#" v-else>
                    <i class="pull-right fal fa-thumbtack"
                       @click.prevent="column.freeze = true"></i>
                </a>
            </div>

        </draggable>

        <div>
            <a href="#" v-if="mode != 'add'" @click.prevent="mode = 'add'">
                <i class="fal fa-plus-circle"></i>
            </a>
            <template v-else-if="mode == 'add'">
                <pckg-maestro-customize-fields-field :columns="myColumns" :parent-fields="myFields"
                                                     :relations="myRelations"
                                                     @chosen="addField"></pckg-maestro-customize-fields-field>
            </template>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'pckg-maestro-customize-fields',
        mode: 'view',
        props: {
            parentFields: {
                type: Array
            },
            columns: {},
            relations: {
                type: Array
            },
            table: {
                type: Object
            },
        },
        data: function () {
            return {
                newField: '',
                mode: 'view',
                myFields: this.parentFields,
                myRelations: this.relations,
                myColumns: this.columns,
            };
        },
        watch: {
            parentFields: function (parentFields) {
                this.myFields = parentFields;
            },
            columns: function (columns) {
                this.myColumns = columns;
            },
            relations: function (relations) {
                this.myRelations = relations;
            },
        },
        methods: {
            getColumnTitle: function (column) {
                if (typeof column.field == 'string') {
                    let f;
                    $.each(this.myFields, function (i, field) {
                        if (field.field != column.field) {
                            return;
                        }

                        f = field;
                        return false;
                    });

                    if (!f) {
                        return column.field;
                    }

                    return f.title;
                }

                let k = Object.keys(column.field)[0];
                let f;
                $.each(this.myRelations, function (i, relation) {
                    if (relation.alias != k) {
                        return;
                    }

                    f = relation.alias + ' > ' + this.getColumnTitle(column.field[k]);

                    return false;
                }.bind(this));

                return f;
            },
            emitTreeChange: function () {
                this.$emit('change', this.myColumns);
            },
            makeSelectedFieldVisible: function () {
                $.each(this.myFields, function (i, field) {
                    if (field.id != this.newField) {
                        return;
                    }

                    $vue.$set(field, 'visible', true);
                }.bind(this));
                this.newField = '';
            },
            remove: function (data) {
                utils.splice(this.myColumns, data);
                this.$emit('change', this.myColumns);
            },
            addField: function (data) {
                this.myColumns.push(data);
                this.$emit('change', this.myColumns);
                this.mode = null;
                this.$nextTick(function(){
                    this.mode = 'add';
                }.bind(this));
            },
            chosen: function (data) {
                this.myColumns.push(data);
                this.$emit('change', this.myColumns);
                this.mode = 'view';
            }
        },
        computed: {
            invisibleFields: function () {
                var fields = {};
                $.each(this.myFields.filter(function (field) {
                    return !field.visible;
                }), function (i, field) {
                    fields[field.id] = field.title;
                });
                return fields;
            }
        }
    };
</script>