<template>
    <div class="pckg-maestro-tabelize">

        <!-- Header template and entity actions -->
        <div class="header">
            <div class="sec">
                <h2>
                    <template v-if="loading">
                        <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                    </template>
                    <template v-else>
                        {{ table.title }} {{ paginator.total }}/{{ total }}
                    </template>
                </h2>
            </div>

            <div class="sec quick-search" v-if="!onTab">
                <input type="text" v-model="search" class="form-control" :placeholder="'Quick search 29483 orders'"/>
            </div>

            <div class="sec table-actions">
                <!-- print all buttons for mixed, entity-plugin and entity actions -->

                <!-- {{ tabelize.getEntityActionsHtml() | raw }} -->

                <a href="#">
                    <i class="fa fa-plus"></i> Add
                </a>

                <a href="#" v-if="configureSection == 'closed'" @click.prevent="configureSection = 'opened'">
                    <i class="fa fa-chevron-down"></i> Customize view
                </a>
                <a href="#" v-else @click.prevent="configureSection = 'closed'">
                    <i class="fa fa-chevron-down"></i> Hide configuration
                </a>

                <div class="btn-group btn-group-sm">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-download"></i> Export
                    </a>
                    <ul class="dropdown-menu dropdown-menu">
                        <li>.pdf</li>
                        <li>.csv</li>
                        <li>.xlsx</li>
                        <li>.html</li>
                    </ul>
                </div>

                <a href="#">
                    <i class="fa fa-upload"></i> Import
                </a>

                <div class="btn-group btn-group-sm">
                    <a type="button" class="entity-action-view dropdown-toggle" href="#"
                       title="" data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bars" aria-hidden="true"></i> More
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <!-- {{ tabelize.getEntityActionsHtml(false) | raw }} -->
                    </ul>
                </div>

            </div>
        </div>

        <div class="clearfix"></div>

        <!-- table template -->
        <div class="pckg-maestro-table" v-if="!loading">
            <template v-if="depth > 0">
                <table class="table table-striped table-hover">
                    <tr v-for="(record,i) in records" :key="record.id">
                        <td>
                            <legend v-if="groups[depth - 1][i]">{{ groups[depth - 1][i] }}</legend>
                            <legend v-else>{{ i }}</legend>
                            <!--<pckg-maestro-table-{{ table }} :ref="'maestro-table'"
                                                    :fields="fields"
                                                    :initial-records="record"
                                                    :groups="groups"
                                                    :depth="depth - 2"
                                                    :entityactions="entityactions"
                                                    :table="table"
                                                    :paginator="paginator"
                                                    :first=""></pckg-maestro-table-{{ table }}>-->
                        </td>
                    </tr>
                </table>
            </template>
            <template v-if="depth < 1">
                <div class="panel panel-default new-filters" :class="configureSection">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-8">
                                <h4>
                                    Filter
                                    <pckg-tooltip icon="question-circle"
                                                  :content="'You can create custom filters for quicker access in future'"></pckg-tooltip>


                                    <div class="pull-right">
                                        <d-input-checkbox v-model="view.archived"></d-input-checkbox>
                                        Archived items
                                    </div>

                                    <div class="pull-right">
                                        <d-input-checkbox v-model="view.deleted"></d-input-checkbox>
                                        Deleted items
                                    </div>
                                </h4>
                                <div>
                                    <select class="form-control">
                                        <option>Field</option>
                                    </select>
                                    <select class="form-control">
                                        <option>equals</option>
                                    </select>
                                    <input type="text" value="sth" class="form-control"/>
                                </div>
                                <div>
                                    <select class="form-control">
                                        <option>Other field</option>
                                    </select>
                                    <select class="form-control">
                                        <option>between</option>
                                    </select>
                                    <input type="number" value="123" class="form-control"/>
                                    and
                                    <input type="number" value="223" class="form-control"/>
                                </div>
                                <div>
                                    <select class="form-control">
                                        <option>Third field</option>
                                    </select>
                                    <select class="form-control">
                                        <option>between</option>
                                    </select>
                                    <input type="date" value="2018-01-01" class="form-control"/>
                                    and
                                    <input type="date" value="2019-01-01" class="form-control"/>
                                </div>
                                <div>
                                    <a href="#"><i class="fa fa-plus"></i> Add field</a>
                                </div>
                                <h4>
                                    Group by / statistical view
                                    <pckg-tooltip icon="question-circle"
                                                  :content="'You can group records by fields and displa'"></pckg-tooltip>
                                </h4>
                            </div>
                            <div class="col-xs-2">

                                <pckg-maestro-customize-fields :parent-fields="myFields"
                                                               :relations="relations"
                                                               :table="table"
                                                               @chosen="chosen"></pckg-maestro-customize-fields>

                            </div>
                            <div class="col-xs-2">

                                <h4>
                                    Saved views
                                    <pckg-tooltip icon="question-circle"
                                                  :content="'You can save custom build views with selected fields and filters for quick access'"></pckg-tooltip>
                                </h4>
                                <div>
                                    <a href="#">Default view</a>
                                </div>
                                <div>
                                    <a href="#">Custom named view #1</a>
                                </div>
                                <div>
                                    <a href="#">Foo bar view</a>
                                </div>
                                <div>
                                    <a href="#"><i class="fa fa-save"></i> Save current view</a>
                                </div>

                                <h4>
                                    System views
                                    <pckg-tooltip icon="question-circle"
                                                  :content="'You can save custom build views with selected fields and filters for quick access'"></pckg-tooltip>
                                </h4>
                                <div>
                                    <a href="#">Default view</a>
                                </div>
                                <div>
                                    <a href="#">Custom named view #1</a>
                                </div>
                                <div>
                                    <a href="#">Foo bar view</a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div style="position: relative;" class="closest">
                    <div class="showContextMenu" v-if="contextMenuShown">
                        <a href="#"><i class="fa fa-search"></i> View</a><br/>
                        <a href="#"><i class="fa fa-edit"></i> Edit</a><br/>
                        <a href="#"><i class="fa fa-check"></i> Confirm order</a><br/>
                        <a href="#"><i class="fa fa-envelope"></i> Send email</a>
                    </div>

                    <div :style="{'padding-left': (3 + (3 * 10)) + 'rem'}">
                        <div style="overflow-x: scroll; overflow-y: visible;">
                            <p v-if="records.length == 0">No records to display :/</p>
                            <table class="table table-hover" v-else>
                                <thead>
                                <tr>
                                    <th class="freeze checkboxes">
                                        <div>
                                            <div>
                                                <d-input-checkbox v-model="allChecked"></d-input-checkbox>
                                            </div>
                                        </div>
                                    </th>
                                    <th v-for="(field, i) in columns"
                                        :style="{'--freeze': field.freeze ? i : null}"
                                        :class="[field.freeze ? 'freeze' : '', field.fieldType && field.fieldType.slug ? field.fieldType.slug : '']">

                                        <!-- quick sort -->
                                        <div>
                                            <span @click.prevent="togglefield(field.id)">{{ field.title }}</span>

                                            <span @click.prevent="togglefield(field.id)" v-if="sort.field == field.id">
                                    <i class="fa"
                                       :class="[sort.dir == 'up' ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                                </span>

                                            <!-- quick filter -->
                                            <span href="#">
                                    <i class="fa fa-filter"></i>
                                </span>
                                        </div>

                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="record in records">

                                    <!-- main record row -->
                                    <tr :class="[record.tabelizeClass ? record.tabelizeClass : '', ids.indexOf(record.id) >= 0 ? 'selected' : '']"
                                        @contextmenu.prevent="showContextMenu($event)"
                                        @click.prevent="delaySingleClick(record)"
                                        @dblclick.prevent="doubleClick(record)">
                                        <td class="checkboxes freeze">
                                            <div>
                                                <d-input-checkbox v-model="ids" :value="record.id"></d-input-checkbox>
                                            </div>
                                        </td>
                                        <!--<td class="actions freeze">
                                            <div>Actions</div>
                                            <pckg-maestro-actions-{{ table }} :record="record"
                                                                              :recordactionhandler="recordactionhandler"
                                                                              :identifier="identifier"></pckg-maestro-actions-{{ table }}>
                                        </td>-->
                                        <td v-for="(field, i) in columns"
                                            :style="{'--freeze': field.freeze ? i : null}"
                                            :class="[field.freeze ? 'freeze' : '', field.fieldType && field.fieldType.slug ? field.fieldType.slug : '', record[field.field] && (record[field.field].length > 120 || typeof record[field.field] == 'object') ? 'long' : '']">
                                            <pckg-maestro-field :field="field" :record="record"
                                                                :model="record[field.field]"
                                                                :table="table"></pckg-maestro-field>
                                        </td>
                                    </tr>

                                </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>
            <div v-if="first">
                <pckg-dynamic-paginator :ref="'maestro-paginator'"
                                        :initial-per-page="paginator.perPage"
                                        :initial-page="computed(paginator.page)"
                                        :total="paginator.total"
                                        :url="paginator.url"
                                        :resetpaginatorurl="resetPaginatorUrl"
                                        :initial-records="records"
                                        :initial-groups="groups"
                ></pckg-dynamic-paginator>
            </div>
        </div>

        <div class="table-floating-right-bar" :class="quickView">

            <div class="row">
                <div class="col-xs-12">

                    <h4>Order info</h4>
                    <div>3x Some packet - blue option</div>
                    <div>3x Some packet - option</div>
                    <div>3x packet - blue option krneki</div>
                    <div>3x Foobar - blue</div>

                    <h4>Attedees</h4>
                    <div>Bojan Rajh - bojan.rajh@t-2.net</div>
                    <div>Ana Brinc - ana@gmail.com</div>
                    <div>Bojan Rajh - bojan.rajh@t-2.net</div>
                    <div>Ana Brinc - ana@gmail.com</div>

                </div>

                <div class="col-xs-12">

                    <h4>Customer</h4>
                    <div>Bojan Rajh</div>
                    <div>bojan.rajh@t-2.net</div>

                    <h4>Status</h4>
                    <div><b>Order:</b> confirmed</div>
                    <div><b>Payment:</b> partial</div>
                    <div><b>Delivery:</b> none</div>
                    <div><b>Voucher:</b> none</div>

                </div>

                <div class="col-xs-12">

                    <h4>Payment info</h4>
                    <div>1. instalment - 123.12€ <i class="fa fa-check"></i> 21. Nov 2018</div>
                    <div>2. instalment - 123.12€ <i class="fa fa-info"></i> 21. Nov 2018</div>
                    <div>3. instalment - 123.12€ 21. Nov 2018</div>
                    <div>4. instalment - 123.12€ 21. Nov 2018</div>
                    <div>Total <b>1234.23 €</b></div>

                    <h4>Documents</h4>
                    <div>Pre-invoice <i class="fa fa-download"></i> <i class="fa fa-sync-alt"></i>
                        <i class="fa fa-envelope"></i></div>
                    <div>Voucher <i class="fa fa-download"></i> <i class="fa fa-sync-alt"></i> <i
                            class="fa fa-envelope"></i></div>
                    <div>Invoice <i class="fa fa-download"></i> <i class="fa fa-sync-alt"></i> <i
                            class="fa fa-envelope"></i></div>

                </div>

                <div class="col-xs-12">

                    <h4>Delivery</h4>
                    <div>Bojan Rajh</div>
                    <div>Šmartno ob Paki 103</div>
                    <div>3327 Šmartno ob Paki</div>
                    <div>SI - Slovenia</div>
                    <div>+386(0)70123456</div>

                </div>
            </div>

        </div>

        <div class="table-floating-bottom-bar" v-if="ids.length > 0">
            <div class="pull-left">
                {{ ids.length }} records selected<br/>
                <a href="#">Select all items from all pages</a>
            </div>

            <a href="#" style="margin-right: 2rem;"><i class="fa fa-check"></i> Confirm orders</a>
            <a href="#" style="margin-right: 2rem;"><i class="fa fa-envelope"></i> Send email</a>
            <a href="#" style="margin-right: 2rem;"><i class="fa fa-truck"></i> Process shipping</a>

            <a href="#" class="pull-right danger"><i class="fa fa-trash"></i> Delete
                <pckg-tooltip icon="question-circle"
                              :content="'This is permanent and non-reversable action. Use it with caution.'"></pckg-tooltip>
            </a>
            <a href="#" style="margin-right: 2rem;" class="pull-right danger"><i class="fa fa-archive"></i> Archive
                <pckg-tooltip icon="question-circle"
                              :content="'Archived items can be listed by checking \'Archived items\' on view configuration.'"></pckg-tooltip>
            </a>

            <div class="btn-group btn-group-sm pull-right" style="margin-right: 2rem;">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-download"></i> Export
                </a>
                <ul class="dropdown-menu dropdown-menu">
                    <li>.pdf</li>
                    <li>.csv</li>
                    <li>.xlsx</li>
                    <li>.html</li>
                </ul>
            </div>
        </div>

    </div>
</template>

<script type="text/javascript">
    export default {
        name: 'pckg-maestro-table',
        mixins: [pckgTimeout],
        props: {
            /**
             * New
             */
            tableId: {
                required: true,
                type: Number
            },
            onTab: {
                default: false
            },
            /**
             * Old
             */
            initialFields: {
                default: function () {
                    return [];
                }
            },
            depth: {
                default: 0
            },
            initialRecords: {
                default: function () {
                    return [];
                }
            },
            initialGroups: {
                default: function () {
                    return [];
                }
            },
            first: {
                default: false
            },
            search: {
                default: '',
                type: String
            },
            entityactions: {},
            paginator: {
                default: function () {
                    return {
                        perPage: 50,
                        page: 1,
                        total: 0,
                        url: null
                    };
                },
             type: Object
            },
            resetpaginatorurl: {
                type: String,
                default: ''
            },
            total: {
                default: 0,
                type: Number
            },
            listed: {
                default: 0,
                type: Number
            },
            identifier: {
                default: '',
                type: String
            }
        },
        data: function () {
            return {
                _searchTimeout: null,
                _sortTimeout: null,
                records: this.initialRecords,
                groups: this.initialGroups,
                ids: [],
                emitted: 0,
                allChecked: false,
                loading: false,
                /**
                 * All available table fields.
                 */
                myFields: this.initialFields,
                contextMenuShown: false,
                /**
                 * Current table info.
                 */
                table: {},
                sort: {
                    field: '',
                    dir: 'up'
                },
                configureSection: 'closed',
                quickView: 'closed',
                _quickViewDelay: null,
                doubleClickDiff: null,
                relations: [],
                view: {
                    /**
                     * Visible columns.
                     */
                    columns: {},
                    /**
                     * Applied filters.
                     */
                    filters: {},
                    archived: false,
                    deleted: false
                }
            };
        },
        methods: {
            chosen: function (selection) {
                /**
                 * Field (or relation) was chosen.
                 * We need to add it to listed fields and refetch table because field was not loaded earlier.
                 */
                $.each(selection, function (i, j) {
                    $vue.$set(this.view.columns, i, j);
                    return false;
                }.bind(this));
            },
            delaySingleClick: function () {
                if (this._quickViewDelay) {
                    clearTimeout(this._quickViewDelay);
                    this._quickViewDelay = null;
                    this.doubleClick();
                    return;
                }

                this._quickViewDelay = setTimeout(function () {
                    clearTimeout(this._quickViewDelay);
                    this._quickViewDelay = null;
                    this.quickView = 'opened';
                    var t = this;
                    $('body').on('click', function () {
                        if ($(this).closest('.table-floating-right-bar').length == 0) {
                            t.quickView = 'closed';
                            return;
                        }
                    });
                }.bind(this), 333);
            },
            doubleClick: function () {
                $dispatcher.$emit('notification:success', 'Double click');
            },
            showContextMenu: function ($event) {
                console.log($event);
                this.contextMenuShown = true;
                var t = this;
                let x = $event.pageX;//: 674
                let y = $event.pageY;
                let $target = $($event.target).closest('.closest');

                this.$nextTick(function () {
                    $('.showContextMenu').css({
                        position: 'absolute',
                        top: (parseInt(y) - parseInt($target.offset().top)) + 'px',
                        left: (parseInt(x) - parseInt($target.offset().left)) + 'px',
                    });

                    $('body').on('click', function () {
                        /**
                         * Close context menu on any click.
                         */
                        t.contextMenuShown = false;
                    });
                });
            },
            initialFetch: function () {
                this.loading = true;
                http.getJSON('/api/dynamic/table/' + this.tableId, function (data) {
                    /**
                     * Receive view config:
                     *  - fields
                     *  - current page
                     *  - paginator, fetch and search urls
                     */
                    this.myFields = data.fields;
                    this.relations = data.relations;
                    this.records = data.records;
                    this.table = data.table;
                    this.view = data.view;
                    this.loading = false;
                }.bind(this));
            },
            recordAction: function (record, action) {
                this.$parent.recordAction(record, action);
            },
            computed: function (val) {
                return val;
            },
            recordactionhandler: function (record, action) {
                $dispatcher.$emit('record:' + action, record, record.id, this);
            },
            entityAction: function (action) {
                $dispatcher.$emit('entity:' + action, this.getSelectedRecords(), this);
            },
            getSelectedRecords: function () {
                var selected = [];

                $.each(this.records, function (i, record) {
                    if (this.ids.indexOf(record.id) >= 0) {
                        selected.push(record);
                    }
                }.bind(this));

                return selected;
            },
            togglefield: function (fieldId) {
                if (this.sort.field != fieldId) {
                    this.sort.field = fieldId;
                } else {
                    this.sort.dir = this.sort.dir == 'up'
                        ? 'down'
                        : 'up';
                }
                this.makeSort();
            },
            makeSort: function () {
                var newValue = this.sort;
                /*if (this._sortTimeout) {
                    this._sortTimeout.abort();
                }*/

                this.resetPaginatorUrl({
                    field: newValue.field,
                    dir: newValue.dir
                });

                /*this._sortTimeout = http.getJSON(this.paginator.url, function (data) {
                    this.records = data.records;
                }.bind(this));*/
            },
            setUrlParams: function (params) {
                params = params || {};

                if (params.search) {
                    this.search = params.search;
                }

                if (params.sort) {
                    this.sort.field = params.sort;
                }

                if (params.dir) {
                    this.sort.dir = params.dir;
                }

                if (params.page) {
                    this.paginator.page = params.page;
                }

                if (params.perPage) {
                    this.paginator.perPage = params.perPage;
                }
            },
            getUrlParams: function () {
                var finalParams = {};

                if (this.search.length > 0) {
                    finalParams.search = this.search;
                }

                if (this.sort.dir.length > 0) {
                    finalParams.dir = this.sort.dir;
                }

                if (this.sort.field.length > 0) {
                    finalParams.field = this.sort.field;
                }

                if (this.paginator.page > 0) {
                    finalParams.page = this.paginator.page;
                }

                if (this.paginator.perPage > 0 || this.paginator.perPage == 'all') {
                    finalParams.perPage = this.paginator.perPage;
                }

                if (Object.keys(finalParams).length == 0) {
                    return '';
                }
                return '?' + $.param(finalParams);
            },
            resetPaginatorUrl: function (preset) {
                this.setUrlParams(preset);
                this.paginator.url = '{{ searchUrl }}' + this.getUrlParams();

                this.delaySearch();
            },
            delaySearch: function () {
                this.timeout('search', this.refreshData, 500);
            },
            refreshData: function (params) {
                this.loading = true;
                http.getJSON(this.paginator.url, function (data) {
                    if (data.tabelizes) {
                        /**
                         * @T00D00 - auto detect index for multiple tables?
                         */
                        var d = data.tabelizes[0];
                        this.records = d.records;
                        this.groups = d.groups;
                        this.setPaginatorTotal(d.paginator.total);

                        if (params && params.callback) {
                            params.callback(data);
                        }
                    } else {
                        this.records = data.records;
                        this.groups = data.groups;
                        this.setPaginatorTotal(data.paginator.total);
                    }
                    this.loading = false;
                }.bind(this));
            },
            setRecords: function (records) {
                this.records = records;
            },
            setPaginatorTotal: function (total) {
                $vue.$set(this.paginator, 'total', total);
            },
            getFieldById: function (fieldId) {
                var field = null;
                $.each(this.myFields, function (i, f) {
                    if (f.id != fieldId) {
                        return;
                    }

                    field = f;
                    return false;
                });

                return field;
            },
            getFieldByRelation: function (relationId) {
                return {
                    field: 'relation-162-relation-152-field-455',
                    title: 'Unit > Unit group > Title',
                    visible: true
                };
            },
            resolveRelationField: function (relation, field) {
                if (typeof field == 'string') {
                    return relation + '-' + field;
                }

                let key = Object.keys(field)[0];
                return relation + '-' + this.resolveRelationField(key, field[key]);
            },
            isFieldLoaded: function (field) {
                if (!this.records[0]) {
                    return false;
                }

                let record = this.records[0];
                return Object.keys(this.records[0]).indexOf(field.field) >= 0;
            }
        },
        computed: {
            columns: function () {
                console.log('computing columns');
                let columns = [];

                $.each(this.view.columns, function (i, column) {
                    if (i.indexOf('field-') === 0) {
                        console.log('field');
                        columns.push(this.getFieldById(column.substring(6)));
                    } else {
                        console.log('relation');
                        columns.push(this.getFieldByRelation(this.resolveRelationField(i, column)));
                    }
                }.bind(this));
                console.log('columns', columns);

                return columns;
            }
        },
        watch: {
            allChecked: function (all) {
                if (all) {
                    $.each(this.records, function (i, record) {
                        this.ids.push(record.id);
                    }.bind(this));

                    this.ids = Array.from(new Set(this.ids));
                } else {
                    this.ids = [];
                }
            },
            search: function (newValue) {
                this.resetPaginatorUrl({
                    search: newValue,
                    page: 1
                });
            },
            ids: function () {
                $dispatcher.$emit('pckg-maestro-table-' + this.onTable.table + ':setSelectedRecords', this.getSelectedRecords());
            }
        },
        created: function () {
            this.initialFetch();
        },
        mounted: function () {
            this.refreshData();
            if (this.onTab) {
                $dispatcher.$on('dynamic-tab-' + tab.id + ':refresh', this.refreshData);
            }
            if (this.onTable) {
                $dispatcher.$on('pckg-maestro-table-' + this.onTable.table + ':setRecords', this.setRecords);
                $dispatcher.$on('pckg-maestro-table-' + this.onTable.table + ':setPaginatorTotal', this.setPaginatorTotal);
                $dispatcher.$on('pckg-maestro-table-' + this.onTable.table + ':refresh', this.refreshData);
            }
        }
    };
</script>