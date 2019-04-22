<template>
    <div>

        <a :href="'/dynamic/records/add/' + table.id + (relationId ? '/' + relationId + '/' + recordId : '')"
           class="btn default">
            Add new
        </a>

        <div class="btn-group">
            <button type="button" class="dropdown-toggle btn btn-default"
                    title="" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-h"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="#" @click.prevent="exportView"><i class="fa fa-download"></i> Export</a></li>
                <li><a href="#" @click.prevent="importView"><i class="fa fa-upload"></i> Import</a></li>
                <li v-for="action in actions">
                    <a href="#" @click.prevent="entityAction(action.event)">
                        <i class="fa" :class="'fa-' + action.icon"></i>
                        {{ action.title }}</a>
                </li>
            </ul>
        </div>

        <pckg-bootstrap-modal :visible="modal == 'export'" @close="modal = null">
            <div slot="body">
                <p>Select in which format you would like to export current view. Exports with more than 500 will be
                    processed in background, available for download from notification center, and link will be sent to
                    email.</p>

                <div class="form-group">
                    <label>Format</label>
                    <div>
                        <pckg-select :initial-options="exportFormats" :initial-multiple="false"
                                     v-model="exportFormat"></pckg-select>
                    </div>
                </div>

                <d-input-button :disabled="exportMode == 'exporting'" :loading="exportMode == 'exporting'"
                                :text="exportMode == 'exporting' ? 'Exporting' : (exportMode == 'exported' ? 'Exported' : 'Make export')"
                                :icon="exportMode == 'exported' ? 'fa fa-check clr-success' : (exportMode == 'error' ? 'fa fa-times clr-error' : '')"
                                @click.native.prevent="makeExport"></d-input-button>
            </div>
        </pckg-bootstrap-modal>

        <pckg-bootstrap-modal :visible="modal == 'import'" @close="modal = null">
            <div slot="body">
                <p>Prepare columns in same format as they are available in export. Extra fields will not be
                    imported. Pipe (|), comma (,) and semicolon (;) are supported as column delimiters.
                    See Comms Knowledge Base for <a href="#">more info about imports</a>.</p>

                <div class="form-group">
                    <label>Available columns</label>
                    <div>
                        <pckg-tooltip v-for="(column, i) in columns" :key="i + column.field" style="margin-right: 1rem;"
                                      tag="span"
                                      :text="column.field" :visible="true"
                                      :content="column.title + ' - ' + column.help"></pckg-tooltip>
                    </div>
                </div>

                <div class="form-group">
                    <label>File</label>
                    <div>
                        <pckg-htmlbuilder-dropzone :url="uploadFileUrl" id="import"
                                                   @uploaded="importUploaded"
                                                   accept="text/csv"></pckg-htmlbuilder-dropzone>
                    </div>
                    <div class="help">Upload file in .csv format.</div>
                </div>

                <template v-if="['preparing', 'importing'].indexOf(importMode) >= 0">
                    <div class="form-group">
                        <label>Merge strategy</label>
                        <div>
                            <pckg-select
                                    :initial-options="{import:'Import all (invalid on unique check)',skip:'Skip existing records',overwrite:'Overwrite existing records',existing:'Only overwrite existing'}"
                                    :initial-multiple="false"></pckg-select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Column delimiter</label>
                        <div>{{ meta.delimiter }}</div>
                        <div class="help">Automatically detected column delimiter.</div>
                    </div>

                    <div class="form-group">
                        <label>Detected columns</label>
                        <div>{{ meta.columns.join(', ') }}</div>
                        <div class="help">Columns that were properly detected and will be imported.</div>
                    </div>

                    <div class="form-group">
                        <label>Row delimiter</label>
                        <div v-html="meta.newline"></div>
                        <div class="help">Automatically detected newline delimiter.</div>
                    </div>

                    <div class="form-group">
                        <label>Detected rows</label>
                        <div>{{ meta.rows }}</div>
                        <div class="help">Number of all rows in imported document</div>
                    </div>
                </template>

                <d-input-button :disabled="['preparing', 'imported', 'error'].indexOf(importMode) < 0"
                                :loading="importMode == 'importing'"
                                :text="importMode == 'importing' ? 'Importing' : (importMode == 'imported' ? 'Imported' : 'Make import')"
                                :icon="importMode == 'imported' ? 'fa fa-check clr-success' : (importMode == 'error' ? 'fa fa-times clr-error' : '')"
                                @click.native.prevent="makeImport"></d-input-button>

            </div>
        </pckg-bootstrap-modal>

    </div>

</template>

<script>
    export default {
        name: 'pckg-maestro-table-actions',
        props: {
            table: {
                required: true
            },
            actions: {
                type: Array
            },
            relationId: {
                default: null
            },
            recordId: {
                default: null
            },
            relations: {
                type: Array,
            },
            columns: {
                type: Array,
            }
        },
        methods: {
            importView: function () {
                this.modal = 'import';
            },
            exportView: function () {
                this.modal = 'export';
            },
            makeExport: function () {
                this.exportMode = 'exporting';
                this.$emit('export-view', {
                    format: this.exportFormat
                });
            },
            importUploaded: function (data) {
                this.importMode = 'preparing';
                this.meta = data.data.meta;
                console.log(data);
            },
            makeImport: function () {
                this.importMode = 'importing';
                http.post(utils.url('@api.dynamic.table.importFile', {table: this.table.id}), {
                    meta: this.meta
                }, function (data) {
                    if (data.success) {
                        this.importMode = 'imported';
                        return;
                    }

                    this.importMode = 'error';
                }.bind(this));
            },
            entityAction: function (action) {
                this.$emit('entity-action', action);
            },
        },
        data: function () {
            return {
                exportMode: null,
                importMode: null,
                modal: null,
                exportFormat: 'csv',
                exportFormats: {
                    txt: '.txt - TXT',
                    csv: '.csv - CSV',
                    xlsx: '.xlsx - Excel',
                    docx: '.docx - Word',
                    html: '.html - HTML',
                    pdf: '.pdf - PDF',
                },
                meta: {
                    rows: 0,
                    columns: []
                }
                //templateRender: null
            };
        },
        created: function () {
            $dispatcher.$on('dynamic-table:exported', function () {
                this.exportMode = 'exported';
            }.bind(this));
        },
        computed: {
            columnsToImport: function () {
                return this.columns.filter(function (column) {
                    return ['php', 'mysql'].indexOf(column.type) === -1;
                });
            },
            uploadFileUrl: function () {
                return utils.url('@api.dynamic.table.uploadFile', {table: this.table.id});
            }
        },
        /*watch: {
            table: {
                immediate: true,
                handler: function (newTable, oldTable) {
                    console.log(newTable, oldTable);
                    http.get('/api/vue/dynamic/table/' + 1 + '/actions', function (data) {

                        let res = Vue.compile(data.template);

                        this.templateRender = res.render;
                        this.$options.staticRenderFns = [];
                        this._staticTrees = [];
                        if (res.staticRenderFns) {
                            for (var i in res.staticRenderFns) {
                                this.$options.staticRenderFns.push(res.staticRenderFns[i]);
                            }
                        }
                    }.bind(this));
                }
            }
        },
        render: function (h) {
            if (!this.templateRender) {
                return h('div', 'Loading ...');
            }

            return this.templateRender();
        },*/
    }
</script>