<template>
    <div class="pckg-maestro-tabelize">

        <!-- Header template and entity actions -->
        <div class="header">
            <div class="sec">
                <h2>
                    {{ table.title ? table.title : table.table }} {{ paginator.filtered }}/{{ paginator.total }}
                </h2>
            </div>

            <div class="sec quick-search" v-if="table && table.id">
                <input type="text" v-model="search" class="form-control"
                       :placeholder="'Quick search ' + paginator.total + ' ' + (table.title ? table.title : table.table)"/>
            </div>

            <div class="sec table-actions">
                <a :href="'/dynamic/records/add/' + table.id">
                    <i class="fa fa-plus"></i> Add
                </a>

                <a href="#" v-if="configureSection == 'closed'" @click.prevent="configureSection = 'opened'">
                    <i class="fa fa-chevron-down"></i> Customize view
                </a>
                <a href="#" v-else @click.prevent="configureSection = 'closed'">
                    <i class="fa fa-chevron-up"></i> Hide configuration
                </a>

                <pckg-maestro-table-actions :table="table" :actions="actions.entity"
                                            @entity-action="entityAction"></pckg-maestro-table-actions>

            </div>
        </div>

        <div class="clearfix"></div>

        <pckg-loader :loading="loading"></pckg-loader>

        <!-- table template -->
        <div class="pckg-maestro-table">
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
                            <div class="col-xs-12">
                                <h4>Customize view</h4>

                                <hr/>
                            </div>
                            <div class="col-xs-9">

                                <pckg-maestro-customize-filters :columns="myFields"
                                                                :relations="relations"></pckg-maestro-customize-filters>

                            </div>
                            <div class="col-xs-3">

                                <pckg-maestro-customize-fields :fields="myFields"
                                                               :columns="view.columns"
                                                               :relations="relations"
                                                               :table="table"
                                                               @chosen="chosen"
                                                               @remove="removeColumn"
                                                               @reorder="view.columns = $event"></pckg-maestro-customize-fields>

                                <pckg-maestro-customize-views :views="views"></pckg-maestro-customize-views>

                            </div>
                            <div class="col-xs-12">

                                <hr/>

                                <div class="pull-right">
                                    <a href="#">Reset view</a>

                                    <button type="button" class="btn btn-success">Save view</button>
                                </div>

                                <div class="clearfix"></div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default" v-if="records.length > 0">
                    <div style="position: relative;" class="closest">

                        <div class="showContextMenu dropdown-menu" v-if="selectedRecord">
                            <li v-for="action in actions.record"
                                v-if="action.recordHref && selectedRecord[action.recordHref] || action.event">
                                <a v-if="action.recordHref && selectedRecord[action.recordHref]"
                                   :href="selectedRecord[action.recordHref]">
                                    <i class="fa" :class="'fa-' + action.icon"></i>
                                    {{ action.title }}
                                </a>
                                <a v-else-if="action.event" href="#"
                                   @click.prevent="recordAction(selectedRecord, action.event)">
                                    <i class="fa" :class="'fa-' + action.icon"></i>
                                    {{ action.title }}
                                </a>
                            </li>
                        </div>

                        <div class="clearfix"></div>

                        <!--<div :style="{'padding-left': (3 + (3 * 10)) + 'rem'}">-->
                        <div style="padding-left: 3rem;">
                            <div style="overflow-x: auto; overflow-y: visible;" @scroll="scrollTable($event)">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th class="freeze checkboxes">
                                            <div>
                                                <div>
                                                    <d-input-checkbox v-model="allChecked"></d-input-checkbox>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-for="(field, i) in view.columns"
                                            :style="{'--freeze': field.freeze ? i : null}"
                                            :class="[field.freeze ? 'freeze' : '', field.fieldType && field.fieldType.slug ? field.fieldType.slug : '']">

                                            <!-- quick sort -->
                                            <div>
                                                <span @click.prevent="togglefield(field.id)">{{ field.title }}</span>

                                                <span @click.prevent="togglefield(field.id)"
                                                      v-if="sort.field == field.id">
                                                    <i class="fa"
                                                       :class="[sort.dir == 'up' ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                                                </span>

                                                <!-- quick filter -->
                                                <span href="#">
                                                    <i class="fal fa-filter"></i>
                                                </span>
                                            </div>

                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <template v-for="record in records">

                                        <!-- main record row -->
                                        <tr :class="[ids.indexOf(record.id) >= 0 ? 'selected' : '']"
                                            @contextmenu.prevent="showContextMenu($event, record)"
                                            @click.stop="delaySingleClick(record)"
                                            @dblclick.stop="doubleClick(record)">
                                            <td class="checkboxes freeze" @click.prevent>
                                                <div>
                                                    <d-input-checkbox v-model="ids"
                                                                      :value="record.id"></d-input-checkbox>
                                                </div>
                                            </td>
                                            <!--<td class="actions freeze">
                                                <div>Actions</div>
                                            </td>-->
                                            <td v-for="(field, i) in view.columns"
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
                </div>
            </template>
        </div>

        <div class="table-floating-right-bar" :class="quickView" v-if="false">

            <derive-orders-tabelize-quick-view></derive-orders-tabelize-quick-view>

        </div>

        <div class="table-floating-bottom-bar"
             :class="ids.length > 0 || paginator.total > paginator.perPage ? 'in' : ''">
            <div class="table-actions" v-if="ids.length > 0">
                <div class="pull-left" style="margin-right: 4rem;">
                    {{ ids.length }} records selected
                    <template v-if="allChecked && paginator.total != ids.length">
                        <br/>
                        <a href="#"> Select all items from all pages</a>
                    </template>
                </div>

                <a href="#" style="margin-right: 2rem;" v-for="action in actions.entity"
                   @click.prevent="entityAction(action.event)">
                    <i class="fa" :class="'fa-' + action.icon"></i>
                    {{ action.title }}
                </a>

                <a href="#" class="pull-right danger">
                    <i class="fa fa-trash"></i> Delete
                    <pckg-tooltip icon="question-circle"
                                  :content="'This is permanent and non-reversable action. Use it with caution.'"></pckg-tooltip>
                </a>
                <a href="#" style="margin-right: 2rem;" class="pull-right danger"><i class="fa fa-archive"></i> Archive
                    <pckg-tooltip icon="question-circle"
                                  :content="'Archived items can be listed by checking \'Archived items\' on view configuration.'"></pckg-tooltip>
                </a>

                <div class="clearfix"></div>
            </div>
            <div class="table-paginator">
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

    </div>
</template>

<script type="text/javascript">

    import {Entity, HttpRepository, Record, Repository} from "../../../../../helpers-js/webpack/orm";

    export class DynamicEntity extends Entity {

        constructor(repository) {
            super(repository);
            this.$record = DynamicRecord;
        }

    }

    export class DynamicRecord extends Record {

        constructor() {
            super();
            this.$entity = DynamicEntity;
        }

    }

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
            relationId: {
                type: Number
            },
            recordId: {},
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
            resetpaginatorurl: {
                type: String,
                default: ''
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
                paginator: {
                    perPage: 50,
                    page: 1,
                    filtered: 0,
                    total: 0,
                    url: null
                },
                selectedRecord: null,
                views: [
                    {
                        type: 'saved',
                        title: 'First custom saved view',
                        data: {},
                    },
                    {
                        type: 'saved',
                        title: 'Second custom saved view',
                        data: {},
                    },
                    {
                        type: 'saved',
                        title: 'Third custom saved view',
                        data: {},
                    },
                    {
                        type: 'system',
                        title: 'First custom saved view',
                        data: {},
                    },
                    {
                        type: 'system',
                        title: 'Second custom saved view',
                        data: {},
                    },
                    {
                        type: 'system',
                        title: 'Third custom saved view',
                        data: {},
                    }
                ],


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
                    deleted: false,
                    live: true
                },
                actions: {
                    entity: [],
                    record: [],
                },
                scroll: {
                    left: false,
                    right: false
                }
            };
        },
        methods: {
            removeColumn: function (column) {
                utils.splice(this.view.columns, column);
            },
            scrollTable: function ($event) {
                this.scroll.left = $event.target.scrollLeft > 0;
                this.scroll.right = $event.target.scrollLeft + $event.target.clientWidth < $event.target.scrollWidth;
            },
            chosen: function (selection) {
                this.view.columns.push(selection);
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
            doubleClick: function (record) {
                http.redirect('/dynamic/records/view/' + this.table.id + '/' + record.id);
            },
            showContextMenu: function ($event, record) {
                console.log($event, record);
                this.selectedRecord = record;
                this.contextMenuShown = true;
                var t = this;
                let x = $event.pageX;
                let y = $event.pageY;
                let $target = $($event.target).closest('.closest');

                this.$nextTick(function () {
                    $('.showContextMenu').css({
                        position: 'absolute',
                        top: (parseInt(y) - parseInt($target.offset().top)) + 'px',
                        left: (parseInt(x) - parseInt($target.offset().left)) + 'px',
                    });
                });
            },
            initialFetch: function () {
                this.loading = true;

                http.getJSON('/api/dynamic/table/' + this.tableId + (this.relationId > 0 ? '/relation/' + this.relationId + '/record/' + this.recordId : ''), function (data) {
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
                    this.actions = data.actions;
                    this.paginator = data.paginator;
                    this.loading = false;
                }.bind(this));
            },
            recordAction: function (record, action) {
                $dispatcher.$emit('record:' + action, record, record.id, this);
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
                this.paginator.url = '/api/dynamic/table/' + this.table.id + this.getUrlParams();

                this.delaySearch();
            },
            delaySearch: function () {
                this.timeout('search', this.refreshData, 500);
            },
            refreshData: function (params) {
                this.loading = true;

                let repositoryHandler = new HttpRepository(null);
                let repository = new Repository(repositoryHandler);
                let dynamicEntity = new DynamicEntity(repository);

                dynamicEntity.where('id', 5000, '<')
                    .limit(this.paginator.perPage)
                    .page(this.paginator.page);


                dynamicEntity.all(this.paginator.url, function(data){

                    if (data.tabelizes) {
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

                }.bind(this)).then(function (data) {
                    console.log('got data', data);

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
            /*tableColumns: function () {
                let columns = [];

                $.each(this.view.columns, function (i, column) {
                    if (column.type == 'field') {
                        columns.push(this.getFieldById(column.id));
                    } else {
                        columns.push(this.getFieldByRelation(this.resolveRelationField(i, column)));
                    }
                }.bind(this));

                console.log('columns', columns);

                return columns;
            }*/
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
                $dispatcher.$emit('pckg-maestro-table-' + this.table.table + ':setSelectedRecords', this.getSelectedRecords());
            }
        },
        created: function () {
            this.initialFetch();

            $('body').on('click', function () {
                /**
                 * Close context menu on any click.
                 */
                this.contextMenuShown = false;
                this.selectedRecord = null;
            }.bind(this));
        },
        mounted: function () {
            // this.refreshData();
            if (this.onTab) {
                $dispatcher.$on('dynamic-tab-' + tab.id + ':refresh', this.refreshData);
            }
            if (this.onTable) {
                $dispatcher.$on('pckg-maestro-table-' + this.table.table + ':setRecords', this.setRecords);
                $dispatcher.$on('pckg-maestro-table-' + this.table.table + ':setPaginatorTotal', this.setPaginatorTotal);
                $dispatcher.$on('pckg-maestro-table-' + this.table.table + ':refresh', this.refreshData);
            }
        }
    };
</script>