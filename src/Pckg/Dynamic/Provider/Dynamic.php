<?php namespace Pckg\Dynamic\Provider;

use Pckg\Dynamic\Controller\Export;
use Pckg\Dynamic\Controller\Fields;
use Pckg\Dynamic\Controller\FilterBy;
use Pckg\Dynamic\Controller\GroupBy;
use Pckg\Dynamic\Controller\Import;
use Pckg\Dynamic\Controller\Records;
use Pckg\Dynamic\Controller\SortBy;
use Pckg\Dynamic\Controller\View;
use Pckg\Dynamic\Middleware\RegisterDynamicAssets;
use Pckg\Dynamic\Middleware\SetContentLanguage;
use Pckg\Dynamic\Middleware\SwitchLanguage;
use Pckg\Dynamic\Resolver\ExportStrategy;
use Pckg\Dynamic\Resolver\Field as FieldResolver;
use Pckg\Dynamic\Resolver\Record as RecordResolver;
use Pckg\Dynamic\Resolver\Relation;
use Pckg\Dynamic\Resolver\Tab as TabResolver;
use Pckg\Dynamic\Resolver\Table as TableResolver;
use Pckg\Dynamic\Resolver\TableView as ViewResolver;
use Pckg\Framework\Provider;
use Pckg\Framework\Provider\Frontend as FrontendProvider;
use Pckg\Framework\View\Event\RenderingView;

class Dynamic extends Provider
{

    public function providers()
    {
        return [
            FrontendProvider::class,
        ];
    }

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
        return [
            'url' => array_merge_array(
                         [
                             'controller' => Records::class,
                         ],
                         [
                             '/dynamic/tables/list/[table]'                                   => [
                                 'name'      => 'dynamic.record.list',
                                 'view'      => 'viewTable',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                             '/dynamic/tables/list/[table]/[tableView]'                       => [
                                 'name'      => 'dynamic.record.listView',
                                 'view'      => 'viewTableView',
                                 'resolvers' => [
                                     'table'     => TableResolver::class,
                                     'tableView' => ViewResolver::class,
                                 ],
                             ],
                             '/dynamic/records/add/[table]'                                   => [
                                 'name'      => 'dynamic.record.add',
                                 'view'      => 'add',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                             '/dynamic/records/add/[table]/[relation]/[foreign]'              => [
                                 'name'      => 'dynamic.record.add.related',
                                 'view'      => 'add',
                                 'resolvers' => [
                                     'table'    => TableResolver::class,
                                     'relation' => Relation::class,
                                 ],
                             ],
                             '/dynamic/records/view/[table]/[record]'                         => [
                                 'name'      => 'dynamic.record.view',
                                 'view'      => 'view',
                                 'resolvers' => [
                                     'table'  => TableResolver::class,
                                     'record' => RecordResolver::class,
                                 ],
                             ],
                             '/dynamic/records/edit/[table]/[record]'                         => [
                                 'name'        => 'dynamic.record.edit',
                                 'view'        => 'edit',
                                 'resolvers'   => [
                                     'table'  => TableResolver::class,
                                     'record' => RecordResolver::class,
                                 ],
                                 'middlewares' => [
                                     SwitchLanguage::class,
                                 ],
                             ],
                             '/dynamic/records/clone/[table]/[record]'                        => [
                                 'name'        => 'dynamic.record.clone',
                                 'view'        => 'clone',
                                 'resolvers'   => [
                                     'table'  => TableResolver::class,
                                     'record' => RecordResolver::class,
                                 ],
                                 'middlewares' => [
                                     SwitchLanguage::class,
                                 ],
                             ],
                             '/dynamic/records/delete/[table]/[record]'                       => [
                                 'name'      => 'dynamic.record.delete',
                                 'view'      => 'delete',
                                 'resolvers' => [
                                     'table'  => TableResolver::class,
                                     'record' => RecordResolver::class,
                                 ],
                             ],
                             '/dynamic/records/force-delete/[table]/[record]'                 => [
                                 'name'      => 'dynamic.record.forceDelete',
                                 'view'      => 'forceDelete',
                                 'resolvers' => [
                                     'table'  => TableResolver::class,
                                     'record' => RecordResolver::class,
                                 ],
                             ],
                             '/dynamic/records/tab/[table]/[record]/[tab]'                    => [
                                 'name'      => 'dynamic.record.tab',
                                 'view'      => 'tab',
                                 'resolvers' => [
                                     'table'  => TableResolver::class,
                                     'record' => RecordResolver::class,
                                     'tab'    => TabResolver::class,
                                 ],
                             ],
                             '/dynamic/records/field/[table]/[field]/[record]/toggle/[state]' => [
                                 'name'      => 'dynamic.records.field.toggle',
                                 'view'      => 'toggleField',
                                 'resolvers' => [
                                     'table'  => TableResolver::class,
                                     'field'  => FieldResolver::class,
                                     'record' => RecordResolver::class,
                                 ],
                             ],
                             '/dynamic/records/field/[table]/[field]/[record]/order/[order]'  => [
                                 'name'      => 'dynamic.records.field.order',
                                 'view'      => 'orderField',
                                 'resolvers' => [
                                     'table'  => TableResolver::class,
                                     'field'  => FieldResolver::class,
                                     'record' => RecordResolver::class,
                                 ],
                             ],
                             '/dynamic/records/field/[table]/[field]/[record]/upload'         => [
                                 'name'      => 'dynamic.records.field.upload',
                                 'view'      => 'upload',
                                 'resolvers' => [
                                     'table'  => TableResolver::class,
                                     'field'  => FieldResolver::class,
                                     'record' => RecordResolver::class,
                                 ],
                             ],
                             '/dynamic/records/field/[table]/[field]/none/select-list'        => [
                                 'name'      => 'dynamic.records.field.selectList.none',
                                 'view'      => 'selectList',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                     'field' => FieldResolver::class,
                                 ],
                             ],
                             '/dynamic/records/field/[table]/[field]/[record]/select-list'    => [
                                 'name'      => 'dynamic.records.field.selectList',
                                 'view'      => 'selectList',
                                 'resolvers' => [
                                     'table'  => TableResolver::class,
                                     'field'  => FieldResolver::class,
                                     'record' => RecordResolver::class,
                                 ],
                             ],
                         ]
                     ) + array_merge_array(
                         [
                             'controller' => GroupBy::class,
                         ],
                         [
                             '/dynamic/tables/group/[table]'      => [
                                 'name'      => 'dynamic.record.group',
                                 'view'      => 'groupTable',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                             '/dynamic/tables/group/[table]/save' => [
                                 'name'      => 'dynamic.record.group.save',
                                 'view'      => 'save',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                         ]
                     ) + array_merge_array(
                         [
                             'controller' => FilterBy::class,
                         ],
                         [
                             '/dynamic/tables/filter/[table]'      => [
                                 'name'      => 'dynamic.record.filter',
                                 'view'      => 'filterTable',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                             '/dynamic/tables/filter/[table]/save' => [
                                 'name'      => 'dynamic.record.filter.save',
                                 'view'      => 'save',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                         ]
                     ) + array_merge_array(
                         [
                             'controller' => Fields::class,
                         ],
                         [
                             '/dynamic/tables/fields/[table]'      => [
                                 'name'      => 'dynamic.record.fields',
                                 'view'      => 'fields',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                             '/dynamic/tables/fields/[table]/save' => [
                                 'name'      => 'dynamic.record.fields.save',
                                 'view'      => 'save',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                         ]
                     ) + array_merge_array(
                         [
                             'controller' => SortBy::class,
                         ],
                         [
                             '/dynamic/tables/sort/[table]'      => [
                                 'name'      => 'dynamic.record.sort',
                                 'view'      => 'sortTable',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                             '/dynamic/tables/sort/[table]/save' => [
                                 'name'      => 'dynamic.record.sort.save',
                                 'view'      => 'save',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                         ]
                     ) + array_merge_array(
                         [
                             'controller' => SortBy::class,
                         ],
                         [
                             '/dynamic/tables/actions/[table]'      => [
                                 'name'      => 'dynamic.record.actions',
                                 'view'      => 'actionsTable',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                             '/dynamic/tables/actions/[table]/save' => [
                                 'name'      => 'dynamic.record.actions.save',
                                 'view'      => 'save',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                         ]
                     ) + array_merge_array(
                         [
                             'controller' => View::class,
                         ],
                         [
                             '/dynamic/tables/view/[table]'                  => [
                                 'name'      => 'dynamic.record.view',
                                 'view'      => 'viewTable',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                             '/dynamic/tables/view/[table]/save'             => [
                                 'name'      => 'dynamic.record.view.save',
                                 'view'      => 'saveView',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                             '/dynamic/tables/view/[table]/reset'            => [
                                 'name'      => 'dynamic.record.view.reset',
                                 'view'      => 'resetView',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                             '/dynamic/tables/view/[table]/share'            => [
                                 'name'      => 'dynamic.record.view.share',
                                 'view'      => 'shareView',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                             '/dynamic/tables/view/[table]/load/[tableView]' => [
                                 'name'      => 'dynamic.record.view.load',
                                 'view'      => 'loadView',
                                 'resolvers' => [
                                     'table'     => TableResolver::class,
                                     'tableView' => ViewResolver::class,
                                 ],
                             ],
                         ]
                     ) + array_merge_array(
                         [
                             'controller' => Export::class,
                         ],
                         [
                             '/dynamic/tables/export/[table]/[type]' => [
                                 'name'      => 'dynamic.record.export',
                                 'view'      => 'exportTable',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                     'type'  => ExportStrategy::class,
                                 ],
                             ],
                         ]
                     ) + array_merge_array(
                         [
                             'controller' => Import::class,
                         ],
                         [
                             '/dynamic/tables/import/[table]' => [
                                 'name'      => 'dynamic.record.import',
                                 'view'      => 'importTable',
                                 'resolvers' => [
                                     'table' => TableResolver::class,
                                 ],
                             ],
                         ]
                     ),
        ];
    }

}