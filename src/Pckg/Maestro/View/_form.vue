<template>
    <pckg-loader v-if="state === 'loading'"></pckg-loader>
    <div class="c-pckg-maestro-form" v-else :class="'--mode-' + mode">

        <div class="flex-grid --gap-md grid-2-1">
            <div v-for="position in [leftGroups, rightGroups]">
                <div class="flex-grid --gap-md">
                    <div v-for="group in position" class="s-form-field-group box-with-padding --bg-color">
                        <div class="s-form-field animated fadeIn"
                             :class="'--field-type-' + field.type"
                             v-for="(field, i) in group">
                            <h2 class="h-page-subsubtitle" v-if="i === 0 && field.group">{{ field.group.title }}</h2>

                            <form-group v-if="field.type === 'file:picture'"
                                        :label="getFieldLabel(field)"
                                        :type="mode === 'edit' ? field.type : 'encoded'"
                                        :help="field.help"
                                        :options="Object.assign(field.options, {current: myFormModel.image})"
                                        :name="field.slug"
                                        v-model="myFormModel[field.slug]"></form-group>
                            <form-group v-else
                                        :label="getFieldLabel(field)"
                                        :type="mode === 'edit' ? field.type : 'encoded'"
                                        :help="field.help"
                                        :options="field.options"
                                        :name="field.slug"
                                        v-model="myFormModel[field.slug]">
                                <slot name="element" v-if="mode === 'view' && field.type === 'select:single' && myFormModel[`*${field.slug}`] && typeof myFormModel[`*${field.slug}`] === 'object'">
                                    <router-link :to="myFormModel[`*${field.slug}`].url">{{ myFormModel[`*${field.slug}`].value }}</router-link>
                                </slot>
                            </form-group>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="__form-actions form-group margin-top-sm" v-if="mode !== 'view'">
            <button type="button"
                    @click.prevent="submitForm"
                    class="__submit-btn btn btn-primary"
                    :disabled="['submitting', 'redirecting'].indexOf(state) >= 0">
                {{ myFormModel.id ? 'Save changes' : 'Add' }}
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
            state: 'loading'
        };
    },
    computed: {
        isNew: function () {
            return !this.myFormModel.id;
        },
        visibleFields: function () {
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
            return this.$route.meta.resolved.table;
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
            http.get('/api/dynamic/form/' + this.table.id + (this.myFormModel && this.myFormModel.id ? '/' + this.myFormModel.id : ''), function (data) {
                this.myForm = data.form;
                if (data.model) {
                    this.myFormModel = data.model;
                }
                this.state = null;
            }.bind(this));
        },
        submitForm: function () {
            this.state = 'submitting';
            this.validateAndSubmit(function () {
                let url = this.myFormModel.id
                    ? ('/api/dynamic/records/' + this.table.id + '/' + this.myFormModel.id + '/edit')
                    : ('/api/dynamic/records/' + this.table.id + '/add');
                http.post(url, this.collectFormData(), function (data) {
                    this.$emit('saved');
                    this.state = 'success';
                    this.clearErrorResponse();
                    if (this.onSuccess && this.onSuccess()) {
                        return;
                    }
                    if (!this.myFormModel.id) {
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
            let d = {};
            $.each(this.myForm.fields, function (i, field) {
                d[field.slug] = this.myFormModel[field.slug] || null;
            }.bind(this));
            return d;
        },
        getFieldLabel: function (field) {
            return (field.required ? '* ' : '') + field.title;
        }
    }
}
</script>
