<?php namespace Pckg\Generic\Controller;

use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Entity\TableActions;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Record\Record;
use Pckg\Generic\Entity\MenuItems;

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

        vueManager()->addView('Pckg/Generic:permissions/_permissions');

        return view('Pckg/Generic:permissions/permissions');
    }

    public function getPermissionsAction()
    {
        $for = get('for', null);
        $id = get('id', null);
        $groups = [
            1  => 'Superadmin',
            3  => 'Admin',
            4  => 'PR',
            5  => 'Checkin',
            2  => 'User',
            null => 'Guest',
        ];
        $permissions = [];
        $actions = [];
        if ($for == 'table') {
            $tables = (new Tables(null, null, false));
            $tables->usePermissionableTable();
            $allPermissions = $tables->where('id', $id)->all();
            $allPermissions->each(function($permission) use (&$permissions) {
                $permissions[$permission->action][$permission->user_group_id] = true;
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
                $permissions[$permission->action][$permission->user_group_id] = true;
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
                $permissions[$permission->action][$permission->user_group_id] = true;
            });
            $actions = [
                'execute' => 'Execute',
            ];
        } else if ($for == 'menu') {
            $menuItems = (new MenuItems(null, null, false));
            $menuItems->usePermissionableTable();
            $allPermissions = $menuItems->where('id', $id)->all();
            $allPermissions->each(function($permission) use (&$permissions) {
                $permissions[$permission->action][$permission->user_group_id] = true;
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

    public function getEditTablePermissionsAction(Record $table)
    {
        vueManager()->addView('Pckg/Generic:permissions/_permissions');

        return '<pckg-generic-permissions type="table" id="' . $table->id . '"></pckg-generic-permissions>';
    }

    public function getEditFieldPermissionsAction(Record $field)
    {
        vueManager()->addView('Pckg/Generic:permissions/_permissions');

        return '<pckg-generic-permissions type="field" id="' . $field->id . '"></pckg-generic-permissions>';
    }

    public function getEditActionPermissionsAction(Record $action)
    {
        vueManager()->addView('Pckg/Generic:permissions/_permissions');

        return '<pckg-generic-permissions type="action" id="' . $action->id . '"></pckg-generic-permissions>';
    }

    public function getEditMenuPermissionsAction(Record $menuItem)
    {
        vueManager()->addView('Pckg/Generic:permissions/_permissions');

        return '<pckg-generic-permissions type="menu" id="' . $menuItem->id . '"></pckg-generic-permissions>';
    }

}