<template>
    <div>
        <div @dblclick.prevent="toggleEditable">
            <template v-if="type == 'boolean'">
                <template v-if="!hasPrivilegeToEdit">
                    <i class="fal fa-fw" :class="value ? 'fa-check clr-success' : 'fa-times clr-error'"></i>
                </template>
                <template v-else-if="!editable">
                    <pckg-tabelize-field-boolean :field="fieldId"
                                                 :record="record.id"
                                                 :value="value"
                                                 :table="table"
                                                 :url="toggleFieldUrl"></pckg-tabelize-field-boolean>
                </template>
                <template v-else>
                    <input type="checkbox" v-model="model"/>
                </template>
            </template>
            <template v-else-if="type == 'order'">
                <template v-if="!hasPrivilegeToEdit">{{ value }}</template>
                <pckg-tabelize-field-order v-else
                                           :key="record.id"
                                           :field="fieldId"
                                           :record="record.id"
                                           :value="value"
                                           :table="table"
                                           :url="orderFieldUrl"></pckg-tabelize-field-order>
            </template>
            <template v-else-if="type == 'datetime'">
                <template v-if="isTogglable">
                    <template v-if="!editable">
                        <template v-if="!hasPrivilegeToEdit">{{ value | datetime }}</template>
                        <pckg-tabelize-field-datetime v-else
                                                      :field="fieldId"
                                                      :record="record.id"
                                                      :value="value"
                                                      :table="table"
                                                      :min="minTogglable"
                                                      :max="maxTogglable"
                                                      :url="toggleFieldUrl"></pckg-tabelize-field-datetime>
                    </template>
                    <template v-else>
                        <input type="date" v-model="model"/>
                    </template>
                </template>
                <template v-else-if="value">
                    <span class="display-block nobr">{{ value | date }}</span>
                    <span class="color-grayish display-block nobr">{{ value | time }}</span>
                </template>
            </template>
            <template v-else-if="type == 'editor'">
                <template v-if="!editable">
                    <pckg-tabelize-field-editor
                            :value="value"></pckg-tabelize-field-editor>
                </template>
                <template v-else>
                    <textarea v-html="model">{{ model }}</textarea>
                </template>
            </template>
            <template v-else-if="type == 'select'">
                <template v-if="!editable">
                    <pckg-maestro-field-indicator :field="myField" :record="record"
                                                  :db-field="dbField"></pckg-maestro-field-indicator>
                    <router-link :to="record[`*${key}`].url" v-if="record[`*${key}`] && typeof record[`*${key}`] === 'object'">{{ record[`*${key}`].value }}</router-link>
                    <span v-else v-html="richValue"></span>
                </template>
            </template>
            <template v-else-if="type == 'php'">
                <template v-if="!editable">
                    <span v-html="value"></span>
                </template>
            </template>
            <template v-else-if="type == 'picture' && value && Object.keys(dbField.options).indexOf('dir') >= 0">
                <template v-if="!editable">
                    <a :href="'/storage/uploads/' + dbField.options.dir + '/' + value"
                       @click="openPopup($event, '/storage/uploads/' + dbField.options.dir + '/' + value)">
                        <img :src="cdn(imageCache('/storage/uploads/' + dbField.options.dir + '/' + value, 'c', '64x48'))"
                             :alt="value" :title="value" class="img-table-preview"/>
                    </a>
                </template>
            </template>
            <template v-else>
                <template v-if="!editable">
                    <pb-link v-if="key == 'id'" :to="record.viewUrl" v-html="value" class="nobr" title="Open record"></pb-link>
                    <pb-link v-else-if="key == 'title'" :to="record.viewUrl" v-html="value" title="Open record"></pb-link>
                    <span class="raw" v-else-if="true || field.isRaw">{{ value }}</span>
                    <template v-else><span v-html="value" class="else"></span></template>
                </template>
                <template v-else>
                    <input type="text" v-model="model"/>
                </template>
            </template>
        </div>
        <div v-if="editable">
            <button href="#" class="btn btn-xs btn-danger" title="Cancel changes" @click.prevent="cancelChanges">
                <i class="fal fa-minus" aria-hidden="true"></i>
            </button>
            <button href="#" class="btn btn-xs btn-success" title="Save changes" @click.prevent="saveChanges">
                <i class="fal fa-check" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        mixins: [CommsHubHelpers.cdn],
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
            relations: {},
            parentFields: {}
        },
        watch: {
            field: function (field) {
                this.myField = field;
            },
            relations: function (relations) {
                this.myRelations = relations;
            },
            parentFields: function (fields) {
                this.myFields = fields;
            }
        },
        data: function () {
            return {
                myField: this.field,
                myRelations: this.relations,
                myFields: this.parentFields,
                editable: false,
                toggleFieldUrl: Pckg.router.urls['dynamic.records.field.toggle'],
                orderFieldUrl: Pckg.router.urls['dynamic.records.field.order'],
                model: null
            };
        },
        methods: {
            openPopup: function ($event, url) {
                if ($event.ctrlKey) {
                    return;
                }

                $event.preventDefault();
                $.magnificPopup.open({
                    items: {
                        src: url
                    },
                    type: 'image'
                });

                return false;
            },
            toggleEditable: function () {
                // this.editable = !this.editable;
            },
            saveChanges: function () {
                this.editable = false;
            },
            cancelChanges: function () {
                this.editable = false;
            },
            findDottedKey: function (field, keys) {
                if (typeof field === 'string') {
                    keys.push(field);
                    return keys;
                }

                let key = Object.keys(field)[0];
                keys.push(key);

                return this.findDottedKey(field[key].field, keys);
            }
        },
        computed: {
            fieldId: function () {
                let fieldId = null;
                $.each(this.myFields, function (i, field) {
                    if (field.field != this.myField.field) {
                        return;
                    }

                    fieldId = field.id;
                    return false;
                }.bind(this));

                return fieldId;
            },
            dbField: function () {
                if (typeof this.myField.field != 'string') {
                    return null;
                }

                let t;
                $.each(this.myFields, function (i, field) {
                    if (field.field != this.myField.field) {
                        return;
                    }

                    t = field;
                    return false;
                }.bind(this));

                return t;
            },
            isTogglable: function () {
                let field = this.dbField;
                if (!field) {
                    return false;
                }

                return field.isTogglable;
            },
            minTogglable: function () {
                let field = this.dbField;
                if (!field) {
                    return false;
                }

                return field.minTogglable;
            },
            maxTogglable: function () {
                let field = this.dbField;
                if (!field) {
                    return false;
                }

                return field.maxTogglable;
            },
            type: function () {
                if (typeof this.myField.field == 'string') {
                    let t;
                    $.each(this.myFields, function (i, field) {
                        if (field.field != this.myField.field) {
                            return;
                        }

                        t = field.fieldType.slug;
                        return false;
                    }.bind(this));

                    return t;
                }

                return 'relation';
            },
            key: function () {
                if (typeof this.myField.field == 'string') {
                    return this.myField.field;
                }
            },
            value: function () {
                if (typeof this.myField.field == 'string') {
                    return this.record[this.key];
                }

                let key = this.findDottedKey(this.myField.field, []).join('.');
                if (key && this.record[key]) {
                    return this.record[key];
                }
                // for fields: value
                // for relations: record[relation.alias]...[field]
            },
            richValue: function () {
                if (typeof this.myField.field == 'string') {
                    return this.record['*' + this.key];
                }
                // for fields: value
                // for relations: record[relation.alias]...[field]
            },
            hasPrivilegeToEdit: function () {
                return this.table.privileges.write || false;
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
