<template>
    <div class="pckg-maestro-tabelize" :class="'mode-' + mode">

        <!-- Header template and entity actions -->
        <div class="header" v-if="mode != 'clean' && table && table.id">
            <div class="sec">
                <h2>
                    <template v-if="table">{{ table.title ? table.title : table.table }}</template>
                    <span v-if="paginator" class="clr-grey">{{ paginator.total < paginator.perPage ? paginator.total : paginator.perPage }}/{{ paginator.total }}</span>
                </h2>
            </div>

            <div class="sec quick-search">
                <input type="text" v-model="search" class="form-control" placeholder="Quick search & filter"/>
            </div>

            <div class="sec customize-view">
                <a href="#" v-if="configureSection == 'closed'" @click.prevent="configureSection = 'opened'">
                    <i class="fal fa-sliders-h"></i>
                </a>
                <a href="#" v-else @click.prevent="configureSection = 'closed'">
                    <i class="fa fa-sliders-h"></i>
                </a>
            </div>

            <div class="sec table-actions">
                <pckg-maestro-table-actions :table="table" :actions="actions.entity"
                                            @entity-action="entityAction"
                                            :relation-id="relationId"
                                            :record-id="recordId"
                                            :columns="myFields"
                                            :relations="dbRelations"
                                            @export-view="exportView"></pckg-maestro-table-actions>

            </div>
        </div>

        <div class="clearfix"></div>

        <pckg-loader :loading="loading" class="fixed-centered" style="z-index: 1000;"></pckg-loader>

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

                                <div class="pull-right nobr">
                                    Select view
                                    <pckg-maestro-customize-views :views="views"
                                                                  @set-view="setView"></pckg-maestro-customize-views>
                                </div>

                                <hr/>
                            </div>
                            <div class="col-xs-8 col-md-9">

                                <pckg-maestro-customize-filters :columns="dbFields"
                                                                :relations="dbRelations"
                                                                :filters="myFilters"
                                                                @realtime="view.live = $event"></pckg-maestro-customize-filters>

                            </div>
                            <div class="col-xs-4 col-md-3">

                                <pckg-maestro-customize-fields :parent-fields="dbFields"
                                                               :columns="myFields"
                                                               :relations="dbRelations"
                                                               :table="table"
                                                               @change="myFields = $event"></pckg-maestro-customize-fields>

                            </div>
                            <div class="col-xs-12 text-right">

                                <hr/>

                                <a href="#" style="margin-right: 1.6rem;" @click.prevent="resetView">Reset view</a>

                                <button type="button" class="btn btn-success" @click.prevent="saveView">Refresh data
                                </button>

                                <div class="clearfix"></div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default" v-if="records.length > 0">
                    <div style="position: relative;" class="closest">

                        <!-- context menu -->
                        <div class="showContextMenu dropdown-menu" v-if="selectedRecord">
                            <li v-for="action in actions.record"
                                v-if="action.recordHref && selectedRecord[action.recordHref] || action.event">
                                <a v-if="action.recordHref && selectedRecord[action.recordHref]"
                                   :href="selectedRecord[action.recordHref]">
                                    <i class="fa fa-fw" :class="'fa-' + action.icon"></i>
                                    {{ action.title }}
                                </a>
                                <a v-else-if="action.event" href="#"
                                   @click.prevent="recordAction(selectedRecord, action.event)">
                                    <i class="fa fa-fw" :class="'fa-' + action.icon"></i>
                                    {{ action.title }}
                                </a>
                            </li>
                        </div>

                        <div class="clearfix"></div>

                        <!--<div :style="{'padding-left': (3 + (3 * 10)) + 'rem'}">-->
                        <div class="mode-padding">
                            <div style="overflow-x: auto; overflow-y: visible;" @scroll="scrollTable($event)">
                                <table class="table table-hover table-striped table-items">
                                    <thead>
                                    <tr>
                                        <th class="freeze checkboxes" v-if="mode != 'clean'">
                                            <div>
                                                <d-input-checkbox v-model="allChecked"></d-input-checkbox>
                                            </div>
                                        </th>
                                        <th v-for="(field, i) in myFields"
                                            :style="{'--freeze': field.freeze ? i : null}"
                                            :class="[field.freeze ? 'freeze' : '', getFieldTypeClass(field)]">

                                            <!-- quick sort -->
                                            <div>
                                                <span @click.prevent="toggleFieldSort(field.field)"
                                                      :data-field="field.field">{{ getColumnTitle(field) }}</span>

                                                <span @click.prevent="toggleFieldSort(field.field)"
                                                      v-if="getFieldTypeClass(field) != 'relation' && sort.field == field.field">
                                                    <i class="fa"
                                                       :class="[sort.dir == 'up' ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                                                </span>

                                                <!-- quick filter -->
                                                <span href="#" v-if="hasQuickFilter(field)">
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
                                            <td class="checkboxes freeze" @click.prevent v-if="mode != 'clean'">
                                                <div>
                                                    <d-input-checkbox v-model="ids"
                                                                      :value="record.id"></d-input-checkbox>
                                                </div>
                                            </td>
                                            <!--<td class="actions freeze">
                                                <div>Actions</div>
                                            </td>-->
                                            <td v-for="(field, i) in myFields"
                                                :style="{'--freeze': field.freeze ? i : null}"
                                                :class="[field.freeze ? 'freeze' : '', getFieldTypeClass(field), record[field.field] && (record[field.field].length > 120 || typeof record[field.field] == 'object') ? 'long' : '']">
                                                <pckg-maestro-field :field="field" :record="record"
                                                                    :table="table" :parent-fields="dbFields"
                                                                    :relations="dbRelations"></pckg-maestro-field>
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
                    {{ ids.length }} items selected
                    <template v-if="allChecked && paginator.total != ids.length">
                        <br/>
                        <a href="#"> Select all items from all pages</a>
                    </template>
                </div>

                <div class="pull-right">
                    <a href="#" style="margin-left: 2rem;" v-for="action in actions.entity"
                       @click.prevent="entityAction(action.event)">
                        <i class="fa" :class="'fa-' + action.icon"></i>
                        {{ action.title }}
                    </a>
                </div>

                <div class="clearfix"></div>
            </div>
            <div class="table-paginator">
                <pckg-dynamic-paginator :ref="'maestro-paginator'"
                                        :initial-per-page="paginator.perPage"
                                        :initial-page="paginator.page"
                                        :total="paginator.total"
                                        :url="paginator.url"
                                        :resetpaginatorurl="resetPaginatorUrl"
                                        :initial-records="records"
                                        :initial-groups="groups"
                ></pckg-dynamic-paginator>
            </div>
        </div>

        <!-- additional components -->
        <component :is="component" v-for="component in uniqueActions" :key="component"></component>

    </div>
</template>

<script type="text/javascript">

    import {Entity, HttpQLRepository, Record, Repository} from "../../../../../helpers-js/webpack/orm";

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
            mode: {type: String, default: 'full'},
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
                refreshDataRequestNum: 0,
                myFilters: [{}],
                myFields: [],
                paginator: {
                    perPage: 50,
                    page: 1,
                    filtered: 0,
                    total: 0,
                    url: null
                },
                selectedRecord: null,
                views: [],
                _searchTimeout: null,
                _sortTimeout: null,
                records: this.initialRecords,
                groups: this.initialGroups,
                ids: [],
                emitted: 0,
                allChecked: false,
                loading: false,
                /**
                 * All available table fields and relations.
                 */
                dbFields: [],
                dbRelations: [],
                contextMenuShown: false,
                /**
                 * Current table info.
                 */
                table: {},
                sort: {
                    field: 'id',
                    dir: 'down'
                },
                configureSection: 'closed',
                quickView: 'closed',
                _quickViewDelay: null,
                doubleClickDiff: null,
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
                },
                localBus: new Vue()
            };
        },
        methods: {
            setView: function (settings) {
                this.myFilters = [];
                this.$nextTick(function () {
                    this.myFilters = settings.filters.length > 0 ? settings.filters : [];
                    this.myFields = settings.fields.length > 0 ? settings.fields : this.myFields;
                    this.paginator = settings.paginator;
                    this.sort = settings.sort;
                    this.refreshData();
                }.bind(this));
            },
            recalculateFreeze: function () {
                console.log('freeze height');
                this.$nextTick(function () {
                    $(this.$el).find('td.freeze > div').each(function () {
                        $(this).height($(this).closest('tr').outerHeight());
                    });
                }.bind(this));
            },
            saveView: function () {
                this.refreshData();
                this.configureSection = 'closed';
            },
            resetView: function () {
                this.myFilters = [];
                this.myFields = this.dbFields.filter(function (field) {
                    return field.visible;
                }).map(function () {
                    return {
                        field: field.field,
                        freeze: false
                    };
                });
            },
            hasQuickFilter: function (field) {
                return ['relation', 'datetime', 'date'].indexOf(this.getFieldTypeClass(field)) >= 0;
            },
            timeoutRefreshData: function (timeout) {
                this.setTimeout('refreshData', this.refreshData, timeout);
            },
            getColumnTitle: function (column) {
                if (typeof column.field == 'string') {
                    let f;
                    $.each(this.dbFields, function (i, field) {
                        if (field.field != column.field) {
                            return;
                        }

                        f = field;
                        return false;
                    });

                    if (!f) {
                        console.log('fetch relation!');
                        return column.field;
                    }

                    return f.title;
                }

                let k = Object.keys(column.field)[0];
                let f;
                $.each(this.dbRelations, function (i, relation) {
                    if (relation.alias != k) {
                        return;
                    }

                    f = relation.alias + ' > ' + this.getColumnTitle(column.field[k]);

                    return false;
                }.bind(this));

                return f;
            },
            getFieldTypeClass: function (column) {
                if (typeof column.field == 'string') {
                    let f;
                    $.each(this.dbFields, function (i, field) {
                        if (field.field != column.field) {
                            return;
                        }

                        f = field;
                        return false;
                    });

                    if (!f) {
                        // @T00D00 - fetch relation fields!
                        return 'relation';
                    }

                    return f.fieldType.slug;
                }

                return 'relation';
                let k = Object.keys(column.field)[0];
                $.each(this.dbRelations, function (i, relation) {
                    if (relation.alias != k) {
                        return;
                    }
                });
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
                    this.dbFields = data.fields;
                    this.dbRelations = data.relations;
                    this.views = data.views;
                    this.table = data.table;
                    this.view = data.view;
                    this.actions = data.actions;
                    this.loading = false;
                    this.myFields = data.view.columns;

                    if (!this.view.live && this.records.length == 0) {
                        this.refreshData();
                    }
                }.bind(this));
            },
            recordAction: function (record, action) {
                this.localBus.$emit('record:' + action, record, record.id, this);
            },
            /*recordactionhandler: function (record, action) {
                $dispatcher.$emit('record:' + action, record, record.id, this);
            },*/
            entityAction: function (action) {
                this.localBus.$emit('entity:' + action, this.getSelectedRecords(), this);
                // $dispatcher.$emit('entity:' + action, this.getSelectedRecords(), this);
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
            toggleFieldSort: function (field) {
                if (this.sort.field != field) {
                    this.sort.field = field;
                } else {
                    this.sort.dir = this.sort.dir == 'up'
                        ? 'down'
                        : 'up';
                }
                this.makeSort();
            },
            makeSort: function () {
                var newValue = this.sort;

                this.resetPaginatorUrl({
                    field: newValue.field,
                    dir: newValue.dir
                });
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
            applyFields: function (entity) {
                $.each(this.myFields, function (i, field) {
                    this.applyField(entity, field);
                }.bind(this));
            },
            applyField: function (entity, filter) {
                let keys = [];
                let value = null;
                let comp = null;
                let tempKey;
                let field = filter.field;
                do {
                    if (!field) {
                        break;
                    }

                    if (typeof field == 'string') {
                        keys.push(field);
                        value = filter.value;
                        comp = filter.comp;

                        break;
                    }

                    tempKey = Object.keys(field)[0];
                    keys.push(tempKey);
                    filter = field[tempKey];
                    field = filter.field;
                } while (true);

                entity.getQuery().addSelect(keys.join('.'));
            },
            mapComp: function (comp) {
                let data = {
                    'equals': '=',
                    'notEquals': '!=',
                    'in': 'IN',
                    'notIn': 'NOT IN',
                    'more': '>',
                    'less': '<',
                    'moreOrEquals': '>=',
                    'lessOrEquals': '<=',
                    'like': 'LIKE',
                    'notLike': 'NOT LIKE',
                };

                return data[comp];
            },
            applyFilters: function (entity) {
                $.each(this.myFilters, function (i, filter) {
                    this.applyFilter(entity, filter);
                }.bind(this));
            },
            applyFilter: function (entity, filter) {
                let keys = [];
                let value = null;
                let comp = null;
                let tempKey;
                do {
                    console.log('filter', filter);
                    if (!filter.field) {
                        break;
                    }

                    if (typeof filter.field == 'string') {
                        keys.push(filter.field);
                        value = filter.value;
                        comp = filter.comp;

                        break;
                    }

                    tempKey = Object.keys(filter.field)[0];
                    keys.push(tempKey);
                    filter = filter.field[tempKey];
                } while (true);

                entity.where(keys.join('.'), value, this.mapComp(comp));
            },
            prepareEntity: function (endpoint) {
                let repositoryHandler = new HttpQLRepository(endpoint || '/api/http-ql');
                let repository = new Repository(repositoryHandler);
                let dynamicEntity = new DynamicEntity(repository);

                return dynamicEntity;
            },
            applyCustomizeViewToEntity: function (dynamicEntity) {
                this.applyFields(dynamicEntity);
                this.applyFilters(dynamicEntity);
                let query = dynamicEntity.getQuery();
                query.search(this.search);
                query.sort(this.sort.field).direction(this.sort.dir);

                if (this.relationId && this.recordId) {
                    query.setMeta({relation: this.relationId, record: this.recordId});
                }

                dynamicEntity.limit(this.paginator.perPage)
                    .page(this.paginator.page);
            },
            exportView: function (data) {
                let dynamicEntity = this.prepareEntity('/api/http-ql/export');
                this.applyCustomizeViewToEntity(dynamicEntity);

                dynamicEntity.exp(this.table.id + '&format=' + data.format, function (data) {
                    $dispatcher.$emit('dynamic-table:exported');
                    if (data.file) {
                        http.redirect(data.file);
                    }
                }, function () {
                    $dispatcher.$emit('notification:error', 'Error making export');
                });
            },
            refreshData: function (params) {
                this.loading = true;

                let dynamicEntity = this.prepareEntity();
                this.applyCustomizeViewToEntity(dynamicEntity);

                /**
                 * Fetch data only when data really changed?
                 */
                this.refreshDataRequestNum++;
                let refreshDataRequestNum = this.refreshDataRequestNum;
                dynamicEntity.all(this.table.id, function (data) {
                    if (this.refreshDataRequestNum != refreshDataRequestNum) {
                        return;
                    }

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
                    this.recalculateFreeze();
                    this.$emit('update:records', this.records);

                }.bind(this)).catch(function () {
                    $dispatcher.$emit('notification:error', 'Error fetching data');
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
            setPaginatorFiltered: function (total) {
                $vue.$set(this.paginator, 'filtered', total);
            },
            getFieldById: function (fieldId) {
                var field = null;
                $.each(this.dbFields, function (i, f) {
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
            uniqueActions: function () {
                let components = {};
                $.each(this.actions.entity, function (i, action) {
                    if (!action.component) {
                        return;
                    }
                    components[action.component] = action.component;
                });
                $.each(this.actions.record, function (i, action) {
                    if (!action.component) {
                        return;
                    }
                    components[action.component] = action.component;
                });
                return components;
            }
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
            myFilters: {
                handler: function () {
                    if (!this.view.live) {
                        return;
                    }
                    this.timeoutRefreshData(1000);
                }, deep: true
            },
            myFields: {
                handler: function () {
                    if (!this.view.live) {
                        return;
                    }
                    this.timeoutRefreshData(1000);
                }, deep: true
            },
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