<template>
    <pckg-loader v-if="state === 'loading'"></pckg-loader>
    <div class="c-pckg-maestro-form" v-else>

        <div v-for="group in groupedFields" class="s-form-field-group">
            <div class="s-form-field animated fadeIn"
                 :class="'--field-type' + field.type"
                 v-for="(field, i) in group">
                <h2 class="__component-title" v-if="i === 0 && field.group">{{ field.group.title }}</h2>

                <form-group :label="getFieldLabel(field)"
                            :type="field.type"
                            :help="field.help"
                            :options="field.options"
                            :name="field.slug"
                            v-model="formModel[field.slug]"></form-group>

            </div>

            <div class="form-group">
                <button type="button"
                        @click.prevent="submitForm"
                        class="__submit-btn btn btn-primary"
                        :disabled="['submitting', 'redirecting'].indexOf(state) >= 0">
                    {{ formModel.id ? 'Save changes' : 'Add' }}
                    <i v-if="['submitting', 'error', 'success'].indexOf(state) >= 0"
                       class="fal fa-fw"
                       :class="'submitting' === state ? 'fa-spinner fa-spin' : ('error' === state ? 'fa-times' : 'fa-check')"></i>
                </button>
            </div>
        </div>

    </div>
</template>

<script>

    export default {
        mixins: [pckgFormValidator, pckgTranslations],
        props: {
            tableId: {},
            formModel: {
                default: function () {
                    return {};
                }
            }
        },
        created: function () {
            this.initialFetch();
        },
        data: function () {
            return {
                myForm: {
                    fields: []
                },
                state: 'loading'
            };
        },
        computed: {
            groupedFields: function () {
                let fields = this.myForm.fields;
                let grouped = {};
                $.each(fields, function (i, field) {
                    if (!grouped[field.group ? field.group.id : 'x']) {
                        grouped[field.group ? field.group.id : 'x'] = [];
                    }

                    grouped[field.group ? field.group.id : 'x'].push(field);
                });

                return grouped;
            },
        },
        methods: {
            initialFetch: function () {
                this.state = 'loading';
                http.get('/api/dynamic/form/' + this.tableId + (this.formModel && this.formModel.id ? '/' + this.formModel.id : ''), function (data) {
                    this.myForm = data.form;
                    this.state = null;
                }.bind(this));
            },
            submitForm: function () {
                this.state = 'submitting';
                this.validateAndSubmit(function () {
                    let url = this.formModel.id
                        ? ('/dynamic/records/edit/' + this.tableId + '/' + this.formModel.id)
                        : ('/dynamic/records/add/' + this.tableId);
                    http.post(url, this.collectFormData(), function (data) {
                        this.$emit('saved');
                        this.state = 'success';
                        this.clearErrorResponse();
                        if (!this.formModel.id) {
                            this.state = 'redirecting';
                            $dispatcher.$emit('notification:info', 'The record has been added, redirecting to new page');
                            http.redirect(data.redirect);
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
                    d[field.slug] = this.formModel[field.slug] || null;
                }.bind(this));
                return d;
            },
            getFieldLabel: function (field) {
                return (field.required ? '* ' : '') + field.title;
            },
        }
    }
</script>