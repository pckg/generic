<template>
    <div>

        <pb-link :to="'/dynamic/records/add/' + table.id + (relationId ? '/' + relationId + '/' + recordId : '')"
           v-if="table.privileges.write"
           class="btn btn-primary">
            Add new
        </pb-link>

        <div class="btn-group margin-left-xs">
            <button type="button" class="dropdown-toggle btn btn-default"
                    title="" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                <i class="fal fa-ellipsis-h"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="#" @click.prevent="exportView"><i class="fal fa-download"></i> Export</a></li>
                <li><a href="#" @click.prevent="importView"
                       v-if="table.privileges.write"><i class="fal fa-upload"></i> Import</a></li>
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
                                :icon="exportMode == 'exported' ? 'fal fa-check clr-success' : (exportMode == 'error' ? 'fal fa-times clr-error' : '')"
                                @click.native.prevent="makeExport"></d-input-button>
            </div>
        </pckg-bootstrap-modal>

        <pckg-bootstrap-modal :visible="modal == 'import'" @close="modal = null" size="lg">
            <div slot="body">
                <p>Prepare columns in same format as they are available in export. Extra fields will not be
                    imported. Pipe (|), comma (,) and semicolon (;) are supported as column delimiters.
                    See Comms Knowledge Base for <a href="#">more info about imports</a>.</p>

                <div class="display-block margin-bottom-sm">
                    <a :href="'/dynamic/tables/import/' + table.id + '/export-empty'" target="_blank">Download .csv example</a>
                    <span class="margin-horizontal-xxs">|</span>
                    <a href="#" v-if="importData.columnsVisible" @click.prevent="importData.columnsVisible = false">Hide available columns</a>
                    <a href="#" v-else @click.prevent="importData.columnsVisible = true">Show available columns</a>
                </div>

                <table class="table table-condensed table-borderless table-hover" v-if="importData.columnsVisible">
                    <thead>
                    <tr>
                        <th>Column</th>
                        <th>Type</th>
                        <th>Help</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(column, i) in realColumns">
                        <th>{{ column.field }}<br /><span class="color-grayish">{{ column.title }}</span></th>
                        <th>{{ column.type }}<br /><span class="color-grayish">{{ columnLimit(column) }}</span></th>
                        <th>{{ column.help }}</th>
                    </tr>
                    </tbody>
                </table>

                <div class="form-group">
                    <label>File to import (.csv)</label>
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
                                    v-model="importStrategy"
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
                        <div v-text="meta.newline"></div>
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
                                :icon="importMode == 'imported' ? 'fal fa-check clr-success' : (importMode == 'error' ? 'fal fa-times clr-error' : '')"
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
            },
            makeImport: function () {
                this.importMode = 'importing';
                http.post(utils.url('@api.dynamic.table.importFile', {table: this.table.id}), {
                    meta: this.meta,
                    strategy: this.importStrategy
                }, function (data) {
                    if (data.success) {
                        this.importMode = 'imported';
                        return;
                    }

                    this.importMode = 'error';
                }.bind(this));
            },
            entityAction: function (event) {
                this.$emit('entity-action', event);
            },
            columnLimit: function (column) {
                let limits = {
                    id: 'Unique integer',
                    text: 'Non-html, max 256',
                    textarea: 'Non-html, max 2048',
                    editor: 'HTML, max 8096',
                    select: 'ID integer',
                    datetime: 'Y-m-d H:i:s',
                    date: 'Y-m-d',
                    boolean: 'Empty or 1',
                    order: 'Integer',
                    picture: 'Available in .zip'
                };
                let f = column.field;
                if (column.type === 'select' && f.substring(-3) !== '_id') {
                    return 'key';
                }
                return limits[column.type] || null;
            }
        },
        data: function () {
            return {
                exportMode: null,
                importMode: null,
                importStrategy: 'skip',
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
                },
                importData: {
                    columnsVisible: false
                }
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
            },
            realColumns: function () {
                return this.columns.filter(function (column) {
                    return ['mysql', 'php', 'password'].indexOf(column.type) === -1;
                });
            }
        },
    }
</script>