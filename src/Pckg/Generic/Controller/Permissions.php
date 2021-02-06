<?php

namespace Pckg\Generic\Controller;

use Exception;
use Pckg\Database\Record;
use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Entity\TableActions;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Generic\Entity\MenuItems;
use Pckg\Generic\Entity\Routes;

class Permissions
{

    public function getIndexAction()
    {
        /**
         * We have permissions
         * - by user group:
         *   - tables (read, write)
         *   - fields (read, write)
         *   - table actions (execute)
         *   - menu items (view)
         *   - routes (view)
         * - by language:
         *   - offers (view)
         *   - packets (view)
         *
         * We need to create views on record level (editing one record's permissions) and entity level (editing all record's permissions).
         */
        return view('Pckg/Generic:permissions/permissions');
    }

    public function getPermissionsAction()
    {
        $for = get('for', null);
        $id = get('id', null);
        $groups = [
            0 => 'Guest',
            1 => 'Superadmin',
            3 => 'Admin',
            4 => 'PR',
            5 => 'Checkin',
            2 => 'User',
        ];
        $permissions = [];
        $actions = [];
        if ($for == 'table') {
            $tables = (new Tables(null, null, false));
            $tables->usePermissionableTable();
            $allPermissions = $tables->where('id', $id)->all();
            $allPermissions->each(function($permission) use (&$permissions) {
                $permissions[$permission->action][$permission->user_group_id ?? 0] = true;
            });
            $actions = [
                'read'  => 'Read',
                'write' => 'Write',
            ];
        } else if ($for == 'field') {
            $fields = (new Fields(null, null, false));
            $fields->usePermissionableTable();
            $allPermissions = $fields->where('id', $id)->all();
            $allPermissions->each(function($permission) use (&$permissions) {
                $permissions[$permission->action][$permission->user_group_id ?? 0] = true;
            });
            $actions = [
                'read'  => 'Read',
                'write' => 'Write',
            ];
        } else if ($for == 'action') {
            $tableActions = (new TableActions(null, null, false));
            $tableActions->usePermissionableTable();
            $allPermissions = $tableActions->where('id', $id)->all();
            $allPermissions->each(function($permission) use (&$permissions) {
                $permissions[$permission->action][$permission->user_group_id ?? 0] = true;
            });
            $actions = [
                'execute' => 'Execute',
            ];
        } else if ($for == 'menu') {
            $menuItems = (new MenuItems(null, null, false));
            $menuItems->usePermissionableTable();
            $allPermissions = $menuItems->where('id', $id)->all();
            $allPermissions->each(function($permission) use (&$permissions) {
                $permissions[$permission->action][$permission->user_group_id ?? 0] = true;
            });
            $actions = [
                'read' => 'Read',
            ];
        } else if ($for == 'route') {
            $routes = (new Routes(null, null, false));
            $routes->usePermissionableTable();
            $allPermissions = $routes->where('id', $id)->all();
            $allPermissions->each(function($permission) use (&$permissions) {
                $permissions[$permission->action][$permission->user_group_id ?? 0] = true;
            });
            $actions = [
                'read' => 'Read',
            ];
        }

        return [
            'groups'      => $groups,
            'actions'     => $actions,
            'records'     => [['name' => '']],
            'permissions' => $permissions,
        ];
    }

    public function postPermissionsAction()
    {
        $for = get('for', null);
        $id = get('id', null);

        $entity = null;
        if ($for == 'table') {
            $entity = (new Tables(null, null, false));
        } else if ($for == 'field') {
            $entity = (new Fields(null, null, false));
        } else if ($for == 'action') {
            $entity = (new TableActions(null, null, false));
        } else if ($for == 'menu') {
            $entity = (new MenuItems(null, null, false));
        } else if ($for == 'route') {
            $entity = (new Routes(null, null, false));
        }

        if (!$entity) {
            throw new Exception("No entity?");
        }

        $record = $entity->where('id', $id)->oneOrFail();

        $permissions = [];
        foreach (post('permissions') as $action => $groups) {
            foreach ($groups as $group => $has) {
                if (!$has) {
                    continue;
                }

                $permissions[] = [
                    'id'            => $id,
                    'user_group_id' => $group > 0 ? $group : null,
                    'action'        => $action,
                ];
            }
        }

        $entity = new $entity(null, null, false);
        $entity->usePermissionableTable();

        /**
         * Delete old permissions.
         */
        $entity->where('id', $record->id)->delete();

        /**
         * Save new permissions.
         */
        foreach ($permissions as $permission) {
            $record->grantPermissionTo($permission['action'], $permission['user_group_id']);
        }

        return response()->respondWithSuccess();
    }

    public function getEditTablePermissionsAction(Record $table)
    {
        return '<pckg-generic-permissions type="table" id="' . $table->id . '"></pckg-generic-permissions>';
    }

    public function getEditFieldPermissionsAction(Record $field)
    {
        return '<pckg-generic-permissions type="field" id="' . $field->id . '"></pckg-generic-permissions>';
    }

    public function getEditActionPermissionsAction(Record $action)
    {
        return '<pckg-generic-permissions type="action" id="' . $action->id . '"></pckg-generic-permissions>';
    }

    public function getEditMenuPermissionsAction(Record $menuItem)
    {
        return '<pckg-generic-permissions type="menu" id="' . $menuItem->id . '"></pckg-generic-permissions>';
    }

    public function getEditRoutePermissionsAction(Record $route)
    {
        return '<pckg-generic-permissions type="route" id="' . $route->id . '"></pckg-generic-permissions>';
    }
}