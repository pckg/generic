<?php namespace Pckg\Generic\Controller;

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

        return view('Pckg/Generic:permissions/_permissions');
    }

}