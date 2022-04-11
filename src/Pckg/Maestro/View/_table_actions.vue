<template>
    <div>

        <pb-link :to="$root.tenantUrl('/dynamic/records/' + table.id + '/add' + (relationId ? '/' + relationId + '/' + recordId : ''))"
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
                <li><a href="#" @click.prevent="exportView"><i class="fal fa-fw fa-download"></i> Export</a></li>
                <li v-if="table.privileges.write"><a href="#" @click.prevent="importView"><i class="fal fa-fw fa-upload"></i> Import</a></li>
                <li v-if="table.privileges.bulk"><a href="#" @click.prevent="bulkView"><i class="fal fa-fw fa-pencil"></i> Bulk edit</a></li>
                <li v-for="action in actions">
                    <a href="#" @click.prevent="entityAction(action.event)">
                        <i class="fa fa-fw" :class="'fa-' + action.icon"></i>
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
                    <a :href="'/dynamic/tables/import/' + table.id + '/export-empty'" target="_blank">Download .csv
                        example</a>
                    <span class="margin-horizontal-xxs">|</span>
                    <a href="#" v-if="importData.columnsVisible" @click.prevent="importData.columnsVisible = false">Hide
                        available columns</a>
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
                        <th>{{ column.field }}<br/><span class="color-grayish">{{ column.title }}</span></th>
                        <th>{{ column.type }}<br/><span class="color-grayish">{{ columnLimit(column) }}</span></th>
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

                    <div class="form-group" v-if="meta">
                        <label>Column delimiter</label>
                        <div>{{ meta.delimiter }}</div>
                        <div class="help">Automatically detected column delimiter.</div>
                    </div>

                    <div class="form-group" v-if="meta">
                        <label>Detected columns</label>
                        <div>{{ meta.columns.join(', ') }}</div>
                        <div class="help">Columns that were properly detected and will be imported.</div>
                    </div>

                    <div class="form-group" v-if="meta">
                        <label>Row delimiter</label>
                        <div v-text="meta.newline"></div>
                        <div class="help">Automatically detected newline delimiter.</div>
                    </div>

                    <div class="form-group" v-if="meta">
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

        <pckg-bootstrap-modal :visible="modal == 'bulk'" @close="modal = null">
            <div slot="body">
                <p>Select a field and enter the value which will be applied to
                    <template v-if="ids && ids.length">
                        the selection of {{ ids.length }}<b> {{ table.title }}</b>.
                    </template>
                    <template v-else-if="filters.length">
                        <b>filtered {{ total }} {{ table.title }}</b>.
                    </template>
                    <template v-else>
                        <b>ALL {{ total }} {{ table.title }}</b>.
                    </template>
                </p>

                <p>This action is <b>not reversible</b>. Make sure to have a backup by creating an export first.</p>

                <form-group label="Field"
                            type="select:single"
                            :options="bulkFieldOptions"
                            v-model="bulkFieldModel"></form-group>

                <pckg-maestro-form v-if="bulkFieldModel"
                                   :table-id="table.id"
                                   :only-field="bulkFieldModel"
                                   :group-class="null"
                                   :grid-class="null"
                                   :additional-model="additionalModel"
                                   @update="$emit('update')">

                    <form-group label="Confirmation"
                                type="number"
                                help="Enter the number of records to which the change will be applied to"
                                v-model="confirmTotal"></form-group>

                </pckg-maestro-form>
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
        },
        filters: {
            type: Array,
        },
        ids: {
            type: Array,
        },
        total: {
            type: Number,
        },
    },
    methods: {
        importView: function () {
            this.modal = 'import';
        },
        exportView: function () {
            this.modal = 'export';
        },
        bulkView: function () {
            this.modal = 'bulk';
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
            bulkMode: null,
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
            },
            bulkFieldModel: null,
            confirmTotal: null,
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
        columnsToBulkEdit: function () {
            return this.columns.filter(column => !['php', 'mysql'].includes(column.type))
                .filter(column => !column.unique)
                .filter(column => column.field !== 'id');
        },
        uploadFileUrl: function () {
            return utils.url('@api.dynamic.table.uploadFile', {table: this.table.id});
        },
        realColumns: function () {
            return this.columns.filter(function (column) {
                return ['mysql', 'php', 'password'].indexOf(column.type) === -1;
            });
        },
        bulkFieldOptions: function () {
            return {
                options: this.columnsToBulkEdit.reduce((reduced, field) => {
                    reduced[field.id] = field.title;
                    return reduced;
                }, {}),
            }
        },
        additionalModel: function () {
            let model = {
                confirmTotal: this.confirmTotal,
            };

            if (this.ids && this.ids.length) {
                model.ids = this.ids;
            } else if (this.filters.length) {
                model.appliedFilters = this.filters.map(filter => ({
                    k: filter.field,
                    v: filter.value,
                    c: {
                        'is': '=',
                        'not': '!=',
                        'in': 'IN',
                        'notIn': 'NOT IN',
                        'more': '>',
                        'less': '<',
                        'moreOrEquals': '>=',
                        'lessOrEquals': '<=',
                        'like': 'LIKE',
                        'notLike': 'NOT LIKE',
                    }[filter.comp],
                }));
            }

            return model;
        }
    },
}
</script>
