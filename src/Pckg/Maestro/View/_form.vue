<template>
    <pckg-loader v-if="state === 'loading'"></pckg-loader>
    <div class="c-pckg-maestro-form" v-else :class="'--mode-' + mode">

        <div class="flex-grid --gap-md" :class="gridClass">
            <div v-for="position in filteredPositions">
                <div class="flex-grid --gap-md">
                    <div v-for="group in position" class="s-form-field-group" :class="groupClass">
                        <div class="s-form-field animated fadeIn"
                             :class="'--field-type-' + field.type"
                             v-for="(field, i) in group">
                            <h2 class="h-page-subsubtitle" v-if="!onlyField && i === 0 && field.group">{{ field.group.title }}</h2>

                            <form-group v-if="field.type === 'pdf'"
                                        :label="getFieldLabel(field)"
                                        :help="field.help"
                                        :name="field.slug"
                                        v-model="myFormModel[field.slug]">
                                <a v-if="field.settings['pckg.dynamic.field.previewFileUrl']"
                                   class="btn btn-default btn-md"
                                   :href="makePreviewUrl(field)"
                                   title="Preview">
                                    <i class="fal fa-fw fa-external-link" aria-hidden="true"></i>
                                    Preview
                                </a>
                                <a v-if="field.settings['pckg.dynamic.field.generateFileUrl']"
                                   class="btn btn-default btn-md"
                                   :href="makeGenerateUrl(field)"
                                   title="Generate">
                                    <i class="fal fa-fw fa-refresh" aria-hidden="true"></i>
                                    Generate
                                </a>
                                <a v-if="myFormModel[field.slug]"
                                   class="btn btn-default btn-md"
                                   :href="makeDownloadUrl(field)"
                                   title="Download">
                                    <i class="fal fa-fw fa-download" aria-hidden="true"></i>
                                    Download
                                </a>
                            </form-group>
                            <form-group v-else-if="field.type === 'file:picture'"
                                        :label="getFieldLabel(field)"
                                        :type="mode === 'edit' ? field.type : 'encoded'"
                                        :help="field.help"
                                        :options="Object.assign(field.options, {current: myFormModel.image})"
                                        :name="field.slug"
                                        v-model="myFormModel[field.slug]"></form-group>
                            <form-group v-else-if="field.type === 'editor'"
                                        :label="getFieldLabel(field)"
                                        :type="mode === 'edit' ? field.type : 'encoded'"
                                        :help="field.help"
                                        :options="{id: `${uuid}-${field.slug}-${field.id}`}"
                                        :name="field.slug"
                                        v-model="myFormModel[field.slug]">
                                <!--<button type="button"
                                        class="pckg-editor-toggle btn btn-xs btn-default"
                                        @click.prevent="toggleEditor(field.slug)">Turn Editor On/Off</button>-->
                            </form-group>
                            <form-group v-else
                                        :label="getFieldLabel(field)"
                                        :type="mode === 'edit' ? field.type : 'encoded'"
                                        :help="field.help"
                                        :options="field.options"
                                        :name="field.slug"
                                        :id="`${uuid}-${field.slug}-${field.id}`"
                                        v-model="myFormModel[field.slug]">
                                <slot name="element"
                                      v-if="mode === 'view' && field.type === 'select:single' && myFormModel[`*${field.slug}`] && typeof myFormModel[`*${field.slug}`] === 'object'">
                                    <router-link :to="myFormModel[`*${field.slug}`].url">
                                        {{ myFormModel[`*${field.slug}`].value }}
                                    </router-link>
                                </slot>
                                <slot v-if="field.type === 'editor'"
                                      name="default">
                                    <button type="button"
                                            class="pckg-editor-toggle btn btn-xs btn-default"
                                            @click.prevent="toggleEditor(field.slug)">Turn Editor On/Off
                                    </button>
                                </slot>
                            </form-group>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <slot></slot>

        <div class="__form-actions form-group margin-top-sm" v-if="mode !== 'view' && visibleFields.length">
            <button type="button"
                    @click.prevent="submitForm"
                    class="__submit-btn btn btn-primary"
                    :disabled="['submitting', 'redirecting'].indexOf(state) >= 0">
                {{ onlyField ? 'Overwrite' : (myFormModel.id ? 'Save changes' : 'Add') }}
                <i v-if="['submitting', 'error', 'success'].indexOf(state) >= 0"
                   class="fal fa-fw"
                   :class="'submitting' === state ? 'fa-spinner-third fa-spin' : ('error' === state ? 'fa-times' : 'fa-check')"></i>
            </button>
        </div>

    </div>
</template>

<style lang="less" scoped>
@media (min-width: 640px) {
    .grid-2-1 {
        grid-template-columns: 3fr 2fr;
    }
}
</style>

<script>
import {v4} from "uuid";

export default {
    mixins: [pckgFormValidator, pckgTranslations],
    props: {
        mode: {
            default: 'edit'
        },
        formModel: {
            default() {
                return {};
            },
        },
        onSuccess: {
            default: null
        },
        tableId: {
            default: null
        },
        groupClass: {
            default: () => 'box-with-padding --bg-color',
        },
        gridClass: {
            default: () => 'grid-2-1',
        },
        onlyField: {
            defafult: () => null,
        },
        additionalModel: {
            default: () => ({}),
        }
    },
    created: function () {
        this.initialFetch();
    },
    watch: {
        formModel: function (newValue) {
            this.myFormModel = newValue;
        }
    },
    data: function () {
        return {
            myForm: {
                fields: []
            },
            myFormModel: this.formModel,
            state: 'loading',
            uuid: v4(),
        };
    },
    computed: {
        isNew: function () {
            return !this.myFormModel.id;
        },
        visibleFields: function () {
            if (this.onlyField) {
                return this.myForm.fields.filter((field) => this.onlyField === `${field.id}`);
            }

            return this.myForm.fields.filter(this.isVisible);
        },
        leftFields: function () {
            return this.visibleFields.filter(field => !field.group || field.group.position === 'left');
        },
        rightFields: function () {
            return this.visibleFields.filter(field => field.group && field.group.position === 'right');
        },
        leftGroups: function () {
            return this.groupFields(this.leftFields);
        },
        rightGroups: function () {
            return this.groupFields(this.rightFields);
        },
        table: function () {
            return this.$route.meta.resolved.table || {id: this.tableId};
        },
        filteredPositions: function () {
            return [this.leftGroups, this.rightGroups].filter(groups => Object.keys(groups).length);
        }
    },
    methods: {
        groupFields: function (fields) {
            let grouped = {};
            $.each(fields, function (i, field) {
                if (!grouped[field.group ? field.group.id : 'x']) {
                    grouped[field.group ? field.group.id : 'x'] = [];
                }

                grouped[field.group ? field.group.id : 'x'].push(field);
            }.bind(this));

            return grouped;
        },
        isVisible: function (field) {
            if (field.type === 'id') {
                return false;
            }

            if (field.required) {
                return true;
            }

            if (this.mode === 'edit' && ['php', 'mysql'].indexOf(field.type) >= 0) {
                return false;
            }

            return !this.isNew;
        },
        initialFetch: function () {
            this.state = 'loading';
            let url = '/api/dynamic/form/' + this.table.id + (this.myFormModel && this.myFormModel.id ? '/' + this.myFormModel.id : '');
            if (this.$route.params.relation) {
                const params = this.$route.params;
                url = '/api/dynamic/form/' + this.table.id + '/' + params.relation + '/' + params.foreign;
            }
            http.get(url, function (data) {
                this.myForm = data.form;
                if (data.model) {
                    this.myFormModel = data.model;
                } else {
                    // pre-select foreign record when adding
                    console.log('preselecting', this.$route.params)
                    if (this.$route.params.foreign && this.$route.params.relation) {
                        Object.values(data.form.fields).filter(field => field.reverseRelation)
                            .forEach((field) => {
                                if (field.reverseRelation.id === parseInt(this.$route.params.relation)) {
                                    console.log('match', field.slug);
                                    this.myFormModel[field.slug] = parseInt(this.$route.params.foreign);
                                }
                            })
                    }
                }
                this.state = null;
            }.bind(this));
        },
        submitForm: function () {
            this.state = 'submitting';
            this.validateAndSubmit(function () {
                let url = this.onlyField
                    ? ('/api/dynamic/records/field/' + this.table.id + '/' + this.onlyField + '/bulk-edit')
                    : (this.myFormModel.id
                    ? ('/api/dynamic/records/' + this.table.id + '/' + this.myFormModel.id + '/edit')
                    : ('/api/dynamic/records/' + this.table.id + '/add'));
                http.post(url, this.collectFormData(), function (data) {
                    this.$emit('saved');
                    this.state = 'success';
                    this.clearErrorResponse();
                    if (this.onSuccess && this.onSuccess()) {
                        return;
                    }
                    if (this.onlyField) {
                        $dispatcher.$emit('notification:success', 'Records have been updated');
                        this.$emit('update');
                    } else if (!this.myFormModel.id) {
                        this.state = 'redirecting';
                        $dispatcher.$emit('notification:info', 'The record has been added, redirecting to new page');
                        this.$router.push(data.redirect);
                    } else {
                        $dispatcher.$emit('notification:success', 'The record has been updated');
                    }
                }.bind(this), function (response) {
                    this.state = 'error';
                    this.hydrateErrorResponse(response);
                    $dispatcher.$emit('notification:error', 'Something went wrong, try again');
                }.bind(this));
            }.bind(this), function () {
                this.state = 'error';
            }.bind(this));
        },
        handleSuccessResponses: function () {
            return;
            this.successStateHtml = [];
            $.each(this.myForm.settings.responses, function (i, response) {
                if (response.type === 'html:replace') {
                    this.successStateHtml.push(response.options.content);
                } else if (response.type === 'http:redirect') {
                    setTimeout(function () {
                        let url = this.__(response.options.url);
                        $dispatcher.$emit('notification:info', 'Redirecting to ' + url);
                        http.redirect(url);
                    }, 100);
                }
            }.bind(this));
        },
        collectFormData: function () {
            let d = this.additionalModel;
            $.each(this.visibleFields, function (i, field) {
                d[field.slug] = this.myFormModel[field.slug] || null;
            }.bind(this));
            return d;
        },
        getFieldLabel: function (field) {
            return (field.required ? '* ' : '') + field.title;
        },
        toggleEditor: function (name) {
            var textarea = $('textarea[name="' + name + '"]');
            textarea.idify();
            var id = textarea.attr('id');
            if (pckgEditors[id]) {
                destroyTinymce(id);
            } else {
                var forcedRootBlock = 'p';
                initTinymce(id, {
                    setup: function (editor) {
                        editor.on('Change', function (e) {
                            this.myFormModel[name] = editor.getContent();
                        }.bind(this)).on('KeyDown', function (e) {
                            this.myFormModel[name] = editor.getContent();
                        }.bind(this));
                    }.bind(this),
                    forced_root_block: forcedRootBlock
                });
            }
        },
        makePreviewUrl(field) {
            return `${field.settings['pckg.dynamic.field.previewFileUrl']}?zoom=1`
                .replace('[order]', this.myFormModel.id);
        },
        makeGenerateUrl(field) {
            return `${field.settings['pckg.dynamic.field.generateFileUrl']}`
                .replace('[order]', this.myFormModel.id);
        },
        makeDownloadUrl(field) {
            return `/storage/private/${field.settings['pckg.dynamic.field.dir']}/${this.myFormModel[field.slug]}`;
        },
    }
}
</script>
