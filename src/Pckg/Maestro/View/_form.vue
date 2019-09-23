<template>
    <div class="c-pckg-maestro-form">

        <div v-for="group in groupedFields" class="s-form-field-group box-with-padding --bg-color">
            <div class="s-form-field animated fadeIn"
                 :class="'--field-type' + field.type"
                 v-for="(field, i) in group">
                <h2 class="__component-title" v-if="i === 0 && field.group">{{ field.group.title }}</h2>

                <form-group :label="getFieldLabel(field)"
                            :type="field.type"
                            :help="field.help"
                            :options="field.options"
                            :name="'field[' + field.slug + ']'"
                            v-model="formModel[field.slug]"></form-group>

            </div>
        </div>

        <div class="form-group">
            <button type="button"
                    @click.prevent="submitForm"
                    class="__submit-btn btn btn-primary">
                Save
                <i v-if="['submitting', 'error', 'success'].indexOf(state) >= 0" class="fal fa-fw"
                   :class="'submitting' === state ? 'fa-spinner fa-spin' : ('error' === state ? 'fa-times' : 'fa-check')"></i>
            </button>
        </div>

    </div>
</template>

<script>
    export default {
        mixins: [pckgFormValidator, pckgTranslations],
        props: {
            tableId: {},
        },
        created: function () {
            this.initialFetch();
        },
        data: function () {
            return {
                myForm: {
                    fields: []
                },
                formModel: {},
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
                http.get('/api/dynamic/form/' + this.tableId, function (data) {
                    this.myForm = data.form;
                    this.state = null;
                }.bind(this));
            },
            submitForm: function () {
                this.state = 'submitting';
                return;
                this.successStateHtml = [];
                this.validateAndSubmit(function () {

                    http.post('/api/forms/' + this.myForm.id + '/respond', this.collectGoogleRecaptcha(this.collectFormData()), function (data) {
                        this.state = 'success';
                        this.handleSuccessResponses();
                    }.bind(this), function (response) {
                        this.state = 'error';
                        this.hydrateErrorResponse(response);
                    }.bind(this));

                }.bind(this), function () {
                    this.state = 'error';
                }.bind(this));
            },
            handleSuccessResponses: function () {
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
                $.each(this.mappedFields(''), function (field, label) {
                    d[field] = this.formModel[field] || null;
                }.bind(this));
                return {field: d};
            },
            getFieldLabel: function (field) {
                return (field.required ? '* ' : '') + field.title;
            },
        }
    }
</script>