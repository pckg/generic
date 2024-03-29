<?php

namespace Pckg\Dynamic\Provider;

use Pckg\Dynamic\Controller\Export;
use Pckg\Dynamic\Controller\Import;
use Pckg\Dynamic\Controller\Records;
use Pckg\Dynamic\Controller\Relations;
use Pckg\Dynamic\Controller\View;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Middleware\RegisterDynamicAssets;
use Pckg\Dynamic\Middleware\SetContentLanguage;
use Pckg\Dynamic\Middleware\SwitchLanguage;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Resolver\ExportStrategy;
use Pckg\Dynamic\Resolver\Field as FieldResolver;
use Pckg\Dynamic\Resolver\ForeignRecord;
use Pckg\Dynamic\Resolver\FunctionizesTabelizes;
use Pckg\Dynamic\Resolver\Language;
use Pckg\Dynamic\Resolver\Record;
use Pckg\Dynamic\Resolver\Record as RecordResolver;
use Pckg\Dynamic\Resolver\Relation;
use Pckg\Dynamic\Resolver\Tab as TabResolver;
use Pckg\Dynamic\Resolver\Table as TableResolver;
use Pckg\Dynamic\Resolver\TableRecordRelated;
use Pckg\Dynamic\Resolver\TableView as ViewResolver;
use Pckg\Framework\Provider;
use Pckg\Framework\View\Event\RenderingView;

class Dynamic extends Provider
{
    public function listeners()
    {
        return [
            RenderingView::class => [
                RegisterDynamicAssets::class,
            ],
        ];
    }

    public function paths()
    {
        return [
            $this->getViewPaths(),
        ];
    }

    public function middlewares()
    {
        return [
            SetContentLanguage::class,
        ];
    }

    public function routes()
    {
        $backendData = function ($component = null, $tags = []) {
            $defaultTags = $component ? [
                'auth:in',
                'group:backend',
                'layout:backend',
                'vue:route',
                'vue:route:template' => substr($component, 0, 1) !== '<' ? '<' . $component . '></' . $component . '>' : $component,
            ] : [
                'auth:in',
                'group:backend',
                'layout:backend',
                'vue:route',
            ];

            if ($tags) {
                foreach ($tags as $k => $v) {
                    if (is_numeric($k) && !in_array($v, $defaultTags)) {
                        $defaultTags[] = $v;
                    } else if (!is_numeric($k)) {
                        $defaultTags[$k] = $v;
                    }
                }
            }

            return [
                'tags' => $defaultTags
            ];
        };
        
        return [
            /**
             * Views.
             * What if there's a VueRoute attached to a controller?
             * Shall we handle it as a normal route?
             */
            routeGroup([
                'controller' => Records::class,
                'urlPrefix' => '/dynamic/records/[table]',
                ], [

                'dynamic.record.add' => vueRoute('/add', 'dynamic-singular')->resolvers([
                    'table' => function () {
                        return resolve(TableResolver::class)->validator(function (Table $table) {
                            //$table->checkPermissionsFor('write');
                        });
                    },
                ])->mergeToData($backendData()),

                'dynamic.record.add.relation' => vueRoute('/add/[relation]/[foreign]', 'pckg-maestro-form')->resolvers([
                    'table' => function () {
                        return resolve(TableResolver::class)->validator(function (Table $table) {
                            $table->checkPermissionsFor('write');
                        });
                    },
                    'relation' => Relation::class,
                    'foreign' => ForeignRecord::class,
                ])->mergeToData($backendData()),

                'dynamic.record' => vueRoute('/[record]', 'pckg-dynamic-record-tabs', [], [

                    'view' => vueRoute('/view', '<pckg-maestro-form :form-model="$route.meta.resolved.mappedRecord" mode="view"></pckg-maestro-form>')->resolvers([
                        'table' => function () {
                            return resolve(TableResolver::class)->validator(function (Table $table) {
                                $table->checkPermissionsFor('read');
                            });
                        },
                        'record' => RecordResolver::class,
                        TableRecordRelated::class,
                    ])->mergeToData($backendData()),

                    'edit' => vueRoute('/edit', '<pckg-maestro-form :form-model="$route.meta.resolved.mappedRecord" mode="edit"></pckg-maestro-form>')->resolvers([
                        'table' => function () {
                            return resolve(TableResolver::class)->validator(function (Table $table) {
                                $table->checkPermissionsFor('write');
                            });
                        },
                        'record' => RecordResolver::class,
                        TableRecordRelated::class,
                    ])->mergeToData($backendData()),

                    'tab' => vueRoute('/tab/[tab]', 'tabelize-functionize')->resolvers([
                        'table' => function () {
                            return resolve(TableResolver::class)->validator(function (Table $table) {
                                $table->checkPermissionsFor('read');
                            });
                        },
                        'record' => RecordResolver::class, // relation.show_table_id, relation.id
                        'tab' => TabResolver::class,
                        TableRecordRelated::class,
                        FunctionizesTabelizes::class,
                    ])->mergeToData($backendData()),

                ])->resolvers([
                    'table' => function () {
                        return resolve(TableResolver::class)->validator(function (Table $table) {
                            $table->checkPermissionsFor('read');
                        });
                    },
                    'record' => RecordResolver::class,
                    TableRecordRelated::class,
                ])->mergeToData($backendData('pckg-dynamic-record-tabs')), // vue route, no component, rendered in controller as tabs

            ]),
            routeGroup([
                'controller' => Records::class,
                'urlPrefix' => '/dynamic/records/[table]',
            ], [

                /*'dynamic.record.edit.foreign' => route('/dynamic/records/[table]/[record]/edit/[relation]/[foreign]', 'edit')->resolvers([
                    'table' => function () {
                        return resolve(TableResolver::class)->validator(function (Table $table) {
                            $table->checkPermissionsFor('write');
                        });
                    },
                    'record'   => RecordResolver::class,
                    'relation' => Relation::class,
                    'foreign' => ForeignRecord::class,
                ])->mergeToData($backendData('<pckg-maestro-form :table-id="$route.params.table" :form-model="$route.meta.resolved.record"></pckg-maestro-form>')),*/

            ]),
            /**
             * APIs.
             */
            'url' => array_merge_array(
                [
                    'tags' => ['group:backend', 'layout:backend'],
                ],
                array_merge_array(
                    [
                        'controller' => Records::class,
                    ],
                    [

                        '/api/dynamic/records/[table]/add' => [
                            'name' => 'api.dynamic.records.add',
                            'view' => 'add',
                            'method' => 'POST',
                            'resolvers' => [
                                'table' => function () {
                                    return resolve(TableResolver::class)->validator(function (Table $table) {
                                        //$table->checkPermissionsFor('write');
                                    });
                                }
                            ]
                        ],
                        '/api/dynamic/records/[table]/add/[relation]/[foreign]' => [
                            'name' => 'api.dynamic.records.add',
                            'view' => 'add',
                            'method' => 'POST',
                            'resolvers' => [
                                'table' => function () {
                                    return resolve(TableResolver::class)->validator(function (Table $table) {
                                        //$table->checkPermissionsFor('write');
                                    });
                                },
                                'relation' => Relation::class,
                                'foreign' => ForeignRecord::class,
                            ]
                        ],
                        '/api/dynamic/records/[table]/[record]/edit' => [
                            'name' => 'api.dynamic.records.edit',
                            'view' => 'edit',
                            'method' => 'POST',
                            'resolvers' => [
                                'table' => function () {
                                    return resolve(TableResolver::class)->validator(function (Table $table) {
                                        //$table->checkPermissionsFor('write');
                                    });
                                },
                                'record' => RecordResolver::class,
                            ]
                        ],
                        '/api/vue/dynamic/table/[table]/actions' => [
                            'name' => 'api.vue.dynamic.table.actions',
                            'view' => 'tableActions',
                            'resolvers' => [
                                'table' => TableResolver::class,
                            ]
                        ],
                        '/api/dynamic/switch-language' => [
                            'name' => 'api.dynamic.switchLanguage',
                            'view' => 'switchLanguage',
                        ],
                        '/dynamic/tables/list/[table]' => [
                            'name' => 'dynamic.record.list',
                            'view' => 'viewTable',
                            'resolvers' => [
                                'table' => TableResolver::class,
                            ],
                        ],
                        '/api/dynamic/table/[table]' => [
                            'name' => 'api.dynamic.record.list',
                            'view' => 'viewTableApi',
                            'resolvers' => [
                                'table' => TableResolver::class,
                            ],
                        ],
                        '/api/dynamic/form/[table]' => [
                            'name' => 'api.dynamic.form',
                            'view' => 'viewFormApi',
                            'resolvers' => [
                                'table' => TableResolver::class,
                            ],
                        ],
                        '/api/dynamic/form/[table]/[record]' => [
                            'name' => 'api.dynamic.form.record',
                            'view' => 'viewFormApiRecord',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'record' => RecordResolver::class,
                            ],
                        ],
                        '/api/dynamic/form/[table]/[relation]/[foreign]' => [
                            'name' => 'api.dynamic.form.record.relation',
                            'view' => 'viewFormApiRelation',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'relation' => Relation::class,
                                'foreign' => ForeignRecord::class,
                            ],
                        ],
                        '/api/dynamic/table/[table]/relation/[relation]/record/[record]' => [
                            'name' => 'api.dynamic.record.relation.list',
                            'view' => 'viewTableApi',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'relation' => Relation::class,
                                'record' => ForeignRecord::class,
                            ],
                        ],
                        '/dynamic/tables/list/[table]/configure' => [
                            'name' => 'dynamic.record.listConfigure',
                            'view' => 'configureTableView',
                            'resolvers' => [
                                'table' => TableResolver::class,
                            ],
                        ],
                        '/dynamic/tables/list/[table]/[tableView]' => [
                            'name' => 'dynamic.record.listView',
                            'view' => 'viewTableView',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'tableView' => ViewResolver::class,
                            ],
                        ],
                        '/dynamic/tables/list/[table]/[tableView]/configure' => [
                            'name' => 'dynamic.record.listViewConfigure',
                            'view' => 'configureTableView',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'tableView' => ViewResolver::class,
                            ],
                        ],
                        '/dynamic/tables/tableview/[view]/delete' => [
                            'name' => 'dynamic.record.deleteView',
                            'view' => 'deleteView',
                            'resolvers' => [
                                'tableView' => ViewResolver::class,
                            ],
                        ],
                        '/dynamic/records/[table]/[record]/clone' => [
                            'name' => 'dynamic.record.clone',
                            'view' => 'clone',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'record' => RecordResolver::class,
                            ],
                            'middlewares' => [
                                SwitchLanguage::class,
                            ],
                        ],
                        '/dynamic/records/[table]/[record]/delete' => [
                            'name' => 'dynamic.record.delete',
                            'view' => 'delete',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'record' => RecordResolver::class,
                            ],
                        ],
                        '/dynamic/records/[table]/[record]/delete/[language]' => [
                            'name' => 'dynamic.record.deleteTranslation',
                            'view' => 'deleteTranslation',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'record' => RecordResolver::class,
                                'language' => Language::class,
                            ],
                        ],
                        '/dynamic/records/[table]/[record]/force-delete' => [
                            'name' => 'dynamic.record.forceDelete',
                            'view' => 'forceDelete',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'record' => RecordResolver::class,
                            ],
                        ],
                        '/api/dynamic/records/field/[table]/[field]/bulk-edit' => [
                            'name' => 'dynamic.records.field.bulkEdit',
                            'view' => 'bulkEdit',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'field' => FieldResolver::class,
                            ],
                        ],
                        '/dynamic/records/field/[table]/[field]/[record]/toggle/[state]' => [
                            'name' => 'dynamic.records.field.toggle',
                            'view' => 'toggleField',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'field' => FieldResolver::class,
                                'record' => RecordResolver::class,
                            ],
                        ],
                        '/dynamic/records/field/[table]/[field]/[record]/order/[order]' => [
                            'name' => 'dynamic.records.field.order',
                            'view' => 'orderField',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'field' => FieldResolver::class,
                                'record' => RecordResolver::class,
                            ],
                        ],
                        '/dynamic/records/field/[table]/[field]/[record]/upload' => [
                            'name' => 'dynamic.records.field.upload',
                            'view' => 'upload',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'field' => FieldResolver::class,
                                'record' => RecordResolver::class,
                            ],
                        ],
                        '/dynamic/records/field/[table]/[field]/upload-new' => [
                            'name' => 'dynamic.records.field.upload.new',
                            'view' => 'uploadNew',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'field' => FieldResolver::class,
                            ],
                        ],
                        '/dynamic/records/field/[table]/[field]/[relation]/[record]/upload-new-foreign' => [
                            'name' => 'dynamic.records.field.upload.newForeign',
                            'view' => 'uploadNewForeign',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'field' => FieldResolver::class,
                                'relation' => Relation::class,
                                'record' => ForeignRecord::class,
                            ],
                        ],
                        '/dynamic/uploader' => [
                            'name' => 'dynamic.records.editor.upload',
                            'view' => 'editorUpload',
                        ],
                        '/dynamic/records/field/[table]/[field]/none/select-list' => [
                            'name' => 'dynamic.records.field.selectList.none',
                            'view' => 'selectList',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'field' => FieldResolver::class,
                            ],
                        ],
                        '/dynamic/records/field/[table]/[field]/[record]/select-list' => [
                            'name' => 'dynamic.records.field.selectList',
                            'view' => 'selectList',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'field' => FieldResolver::class,
                                'record' => RecordResolver::class,
                            ],
                        ],
                        '/dynamic/tables/select-list/[table]' => [
                            'name' => 'dynamic.record.selectList',
                            'view' => 'selectList',
                            'resolvers' => [
                                'table' => TableResolver::class,
                            ],
                        ],
                    ]
                ) + array_merge_array([
                        'controller' => View::class,
                    ], [
                        /*'/dynamic/tables/view/[table]'                      => [
                            'name'      => 'dynamic.record.view',
                            'view'      => 'viewTable',
                            'resolvers' => [
                                'table' => TableResolver::class,
                            ],
                        ],*/
                        '/dynamic/tables/view/[table]/save' => [
                            'name' => 'dynamic.record.view.save',
                            'view' => 'saveView',
                            'resolvers' => [
                                'table' => TableResolver::class,
                            ],
                        ],
                        '/dynamic/tables/view/[table]/[tableView]/savePlus' => [
                            'name' => 'dynamic.record.view.savePlusView',
                            'view' => 'saveView',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'tableView' => ViewResolver::class,
                            ],
                        ],
                        '/dynamic/tables/view/[table]/reset' => [
                            'name' => 'dynamic.record.view.reset',
                            'view' => 'resetView',
                            'resolvers' => [
                                'table' => TableResolver::class,
                            ],
                        ],
                        '/dynamic/tables/view/[table]/share' => [
                            'name' => 'dynamic.record.view.share',
                            'view' => 'shareView',
                            'resolvers' => [
                                'table' => TableResolver::class,
                            ],
                        ],
                        '/dynamic/tables/view/[table]/load/[tableView]' => [
                            'name' => 'dynamic.record.view.load',
                            'view' => 'loadView',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'tableView' => ViewResolver::class,
                            ],
                        ],
                    ]) + array_merge_array([
                        'controller' => Export::class,
                    ], [
                        '/dynamic/tables/export/[table]/[type]' => [
                            'name' => 'dynamic.record.export',
                            'view' => 'exportTable',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'type' => ExportStrategy::class,
                            ],
                        ],
                        '/dynamic/tables/export/[table]/[type]/[tableView]' => [
                            'name' => 'dynamic.record.export.view',
                            'view' => 'exportTable',
                            'resolvers' => [
                                'table' => TableResolver::class,
                                'type' => ExportStrategy::class,
                                'tableView' => ViewResolver::class,
                            ],
                        ],
                    ]) + array_merge_array([
                        'controller' => Import::class,
                    ], [
                        '/dynamic/tables/import/[table]/export-empty' => [
                            'name' => 'dynamic.record.import.exportEmpty',
                            'view' => 'exportEmptyImport',
                            'resolvers' => [
                                'table' => TableResolver::class,
                            ],
                        ],
                        '/dynamic/tables/import/[table]/upload-file' => [
                            'name' => 'api.dynamic.table.uploadFile',
                            'view' => 'uploadFile',
                            'resolvers' => [
                                'table' => TableResolver::class,
                            ],
                        ],
                        '/dynamic/tables/import/[table]/import-file' => [
                            'name' => 'api.dynamic.table.importFile',
                            'view' => 'importFile',
                            'resolvers' => [
                                'table' => TableResolver::class,
                            ],
                        ],
                    ]) + array_merge_array([
                    'controller' => Relations::class,
                    ], [
                    '/api/dynamic/relation/[relation]' => [
                        'name' => 'api.dynamic.relation',
                        'view' => 'relation',
                        'resolvers' => [
                            'relation' => Relation::class,
                        ]
                    ]
                    ])
            ),
        ];
    }
}
