<?php

namespace Pckg\Dynamic\Provider;

use CommsCenter\Pagebuilder\Entity\Routes;
use Pckg\Dynamic\Controller\Records;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Resolver\ForeignRecord;
use Pckg\Dynamic\Resolver\FunctionizesTabelizes;
use Pckg\Dynamic\Resolver\Record as RecordResolver;
use Pckg\Dynamic\Resolver\Relation;
use Pckg\Dynamic\Resolver\Tab as TabResolver;
use Pckg\Dynamic\Resolver\Table as TableResolver;
use Pckg\Dynamic\Resolver\TableRecordRelated;
use Pckg\Framework\Provider;

class DynamicRoutes extends Provider
{
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
                        FunctionizesTabelizes::class,
                        TableRecordRelated::class,
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


                /*'dynamic.record.add.relation' => route('/dynamic/records/[table]/add/[relation]/[foreign]', 'add')->resolvers([
                    'table' => function () {
                        return resolve(TableResolver::class)->validator(function (Table $table) {
                            $table->checkPermissionsFor('write');
                        });
                    },
                    'relation' => Relation::class,
                    'foreign' => ForeignRecord::class,
                ])->mergeToData($backendData('<pckg-maestro-form :table-id="$route.params.table" :form-model="$route.meta.resolved.record"></pckg-maestro-form>')),*/

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
        ];
    }
}