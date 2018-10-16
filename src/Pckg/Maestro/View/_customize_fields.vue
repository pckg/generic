<template>
    <div>
        <h4>
            Fields
            <pckg-tooltip icon="question-circle"
                          :content="'Select fields you would like to see, reorder and freeze them'"></pckg-tooltip>
        </h4>
        <div v-for="field in parentFields" v-if="field.visible">
            <i class="fa fa-arrows"></i>
            <i v-if="field.freeze" class="fa fa-lock" @click.prevent="$set(field, 'freeze', false)"></i>
            <i v-else class="fa fa-lock-open" @click.prevent="$set(field, 'freeze', true)"></i>
            {{ field.title }}
            <i class="fa fa-trash" @click.prevent="$set(field, 'visible', false)"></i>
        </div>
        <div>
            <a href="#" v-if="mode != 'add'" @click.prevent="mode = 'add'">
                <i class="fa fa-plus"></i>
                Add
            </a>
            <template v-else-if="mode == 'add'">
                <pckg-maestro-customize-fields-field :parent-fields="parentFields"
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
            };
        },
        methods: {
            makeSelectedFieldVisible: function () {
                $.each(this.parentFields, function (i, field) {
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
                $.each(this.parentFields.filter(function (field) {
                    return !field.visible;
                }), function (i, field) {
                    fields[field.id] = field.title;
                });
                return fields;
            }
        }
    };
</script>