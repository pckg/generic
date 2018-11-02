<template>
    <div>
        <h5>
            Modify columns
            <pckg-tooltip icon="question-circle"
                          :content="'Select fields you would like to see, reorder and freeze them'"></pckg-tooltip>
        </h5>

        <draggable v-model="myColumns"
                   :options="{handle:'.move-handle', animation: 333}"
                   @end="emitTreeChange"
                   v-if="Object.keys(myColumns).length > 0"
        >

            <div v-for="(column, i) in myColumns" :key="i">
                <a href="#"><i class="fal fa-minus-circle" @click.prevent="$emit('remove', column)"></i></a>
                <a href="#" style="cursor: move;" class="move-handle"><i class="fas fa-ellipsis-v"></i></a>
                {{ getColumnTitle(column) }}
                <a href="#" v-if="column.freeze">
                    <i class="pull-right fas fa-thumbtack"
                       @click.prevent="column.freeze = false"></i>
                </a>
                <a href="#" v-else>
                    <i class="pull-right fa fa-thumbtack"
                       @click.prevent="column.freeze = true"></i>
                </a>
            </div>

        </draggable>

        <div>
            <a href="#" v-if="mode != 'add'" @click.prevent="mode = 'add'">
                <i class="fal fa-plus-circle"></i>
            </a>
            <template v-else-if="mode == 'add'">
                <pckg-maestro-customize-fields-field :parent-fields="myFields"
                                                     :relations="relations"
                                                     @chosen="chosen"></pckg-maestro-customize-fields-field>
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
                myColumns: this.columns
            };
        },
        watch: {
            parentFields: function (parentFields) {
                this.myFields = parentFields;
            },
            columns: function (columns) {
                this.myColumns = columns;
            },
        },
        methods: {
            getColumnTitle: function (column) {
                return column.title;
            },
            emitTreeChange: function () {
                console.log('emitting', this.myColumns);
                this.$emit('reorder', this.myColumns);
            },
            makeSelectedFieldVisible: function () {
                $.each(this.myFields, function (i, field) {
                    if (field.id != this.newField) {
                        return;
                    }

                    this.$set(field, 'visible', true);
                }.bind(this));
                this.newField = '';
            },
            chosen: function (data) {
                this.$emit('chosen', data);
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