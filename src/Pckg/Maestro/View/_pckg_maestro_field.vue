<template>
    <div>
        <div @dblclick.prevent="toggleEditable">
            <template v-if="type == 'boolean'">
                <template v-if="!editable">
                    <pckg-tabelize-field-boolean :field="field.id"
                                                 :record="record.id"
                                                 :value="record[field.field]"
                                                 :table="table"
                                                 :url="toggleFieldUrl"></pckg-tabelize-field-boolean>
                </template>
                <template v-else>
                    <input type="checkbox" v-model="model"/>
                </template>
            </template>
            <template v-else-if="type == 'order'">
                <pckg-tabelize-field-order :key="record.id"
                                           :field="field.id"
                                           :record="record.id"
                                           :value="record[field.field]"
                                           :table="table"
                                           :url="orderFieldUrl"></pckg-tabelize-field-order>
            </template>
            <template v-else-if="type == 'datetime' && field.isTogglable">
                <template v-if="!editable">
                    <pckg-tabelize-field-datetime :field="field.id"
                                                  :record="record.id"
                                                  :value="record[field.field]"
                                                  :table="table"
                                                  :min="field.minTogglable"
                                                  :max="field.maxTogglable"
                                                  :url="toggleFieldUrl"></pckg-tabelize-field-datetime>
                </template>
                <template v-else>
                    <input type="date" v-model="model"/>
                </template>
            </template>
            <template v-else-if="type == 'editor'">
                <template v-if="!editable">
                    <pckg-tabelize-field-editor
                            :value="record[field.field]"></pckg-tabelize-field-editor>
                </template>
                <template v-else>
                    <textarea v-html="model">{{ model }}</textarea>
                </template>
            </template>
            <template v-else>
                <template v-if="!editable">
                    <template v-if="field.field == 'id'">
                        <a :href="record.viewUrl" v-html="record[field.field]" class="nobr" title="Open record"></a>
                    </template>
                    <template v-else-if="field.field == 'title'">
                        <a :href="record.viewUrl" v-html="record[field.field]" title="Open record"></a>
                    </template>
                    <template v-else-if="field.isRaw"><span class="raw">{{ record[field.field] }}</span></template>
                    <template v-else><span v-html="record[field.field]" class="else"></span></template>
                </template>
                <template v-else>
                    <input type="text" v-model="model"/>
                </template>
            </template>
        </div>
        <div v-if="editable">
            <button href="#" class="btn btn-xs btn-danger" title="Cancel changes" @click.prevent="cancelChanges">
                <i class="fa fa-minus" aria-hidden="true"></i>
            </button>
            <button href="#" class="btn btn-xs btn-success" title="Save changes" @click.prevent="saveChanges">
                <i class="fa fa-check" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'pckg-maestro-field',
        props: {
            field: {
                type: Object,
                required: true
            },
            record: {
                type: Object,
                required: true
            },
            table: {
                type: Object,
                required: true
            },
            model: {
                required: true
            }
        },
        data: function () {
            return {
                editable: false,
                toggleFieldUrl: Pckg.router.urls['dynamic.records.field.toggle'],
                orderFieldUrl: Pckg.router.urls['dynamic.records.field.order']
            };
        },
        methods: {
            toggleEditable: function () {
                // this.editable = !this.editable;
            },
            saveChanges: function () {
                this.editable = false;
            },
            cancelChanges: function () {
                this.editable = false;
            }
        },
        computed: {
            type: function () {
                return this.field.fieldType ? this.field.fieldType.slug : null;
            }
        },
        mounted: function () {
            this.$nextTick(function () {
                $(document).on('keyup', function (e) {
                    if (e.keyCode == 27) {
                        // esc key
                        this.editable = false;
                    }
                }.bind(this));
            }.bind(this));
        }
    };
</script>