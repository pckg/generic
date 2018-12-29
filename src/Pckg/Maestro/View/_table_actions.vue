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
                    processed in background, available for download from notification center and link will be sent to
                    email.</p>

                <div class="form-group">
                    <label>Format</label>
                    <div>
                        <pckg-select :initial-options="exportFormats" :initial-multiple="false"
                                     v-model="exportFormat"></pckg-select>
                    </div>
                </div>

                <button type="button" class="btn btn-default" @click.prevent="makeExport">Make export</button>
            </div>
        </pckg-bootstrap-modal>

        <pckg-bootstrap-modal :visible="modal == 'import'" @close="modal = null">
            <div slot="body">
                <p>Prepare columns in same format as they are available in export. Extra fields will not be
                    imported. See Comms Knowledge Base for <a href="#">more info about imports</a>.</p>

                <div class="form-group">
                    <label>Available columns</label>
                    <div>
                        <template v-for="(column, i) in columns">
                            {{ column.field }}
                            <template v-if="i + 1 != columns.length">,</template>
                        </template>
                    </div>
                </div>

                <div class="form-group">
                    <label>Merge strategy</label>
                    <div>
                        <pckg-select
                                :initial-options="{skip:'Skip existing records',overwrite:'Overwrite existing records'}"
                                :initial-multiple="false"></pckg-select>
                    </div>
                </div>

                <div class="form-group">
                    <label>File</label>
                    <div></div>
                    <div class="help">Upload file in .csv or .xlsx format.</div>
                </div>

                <button type="button" disabled class="btn btn-default" @click.prevent="makeExport">Import file</button>
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
                this.$emit('export-view', {
                    format: this.exportFormat
                });
            }
        },
        data: function () {
            return {
                modal: null,
                exportFormat: 'csv',
                exportFormats: {
                    xlsx: '.xlsx - Excel',
                    csv: '.csv - CSV',
                    pdf: '.pdf - PDF',
                    html: '.html - HTML',
                }
                //templateRender: null
            };
        },
        entityAction: function (action) {
            this.$emit('entity-action', action);
        },
        computed: {
            columnsToImport: function () {
                return this.columns.filter(function (column) {
                    return ['php', 'mysql'].indexOf(column.type) === -1;
                });
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