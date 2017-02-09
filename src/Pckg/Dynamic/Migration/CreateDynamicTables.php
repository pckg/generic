<?php namespace Pckg\Dynamic\Migration;

use Pckg\Auth\Migration\CreateAuthTables;
use Pckg\Database\Repository;
use Pckg\Migration\Migration;

class CreateDynamicTables extends Migration
{

    protected $repository = Repository::class . '.dynamic';

    public function partials()
    {
        return [
            (new CreateAuthTables())->setRepository($this->repository),
        ];
    }

    public function up()
    {
        $this->dynamicTablesUp();
        $this->dynamicFieldTypesUp();
        $this->dynamicFieldGroupsUp();
        $this->dynamicFieldsUp();
        $this->dynamicRelationTypesUp();
        $this->dynamicRelationsUp();
        $this->dynamicTableTabsUp();
        $this->dynamicFunctionsUp();
        $this->dynamicTableActionsUp();
        $this->dynamicTableViewsUp();

        $this->save();
    }

    protected function dynamicTablesUp()
    {
        $dynamicTables = $this->table('dynamic_tables');
        $dynamicTables->varchar('table');
        $dynamicTables->varchar('framework_entity');
        $dynamicTables->varchar('repository');

        $dynamicTablesI18n = $this->translatable('dynamic_tables');
        $dynamicTablesI18n->title();

        $dynamicTablesP17n = $this->permissiontable('dynamic_tables');
    }

    protected function dynamicFieldTypesUp()
    {
        $dynamicFieldTypes = $this->table('dynamic_field_types');
        $dynamicFieldTypes->slug();

        $dynamicFieldTypesI18n = $this->translatable('dynamic_field_types');
        $dynamicFieldTypesI18n->title();
    }

    protected function dynamicFieldGroupsUp()
    {
        $dynamicFieldGroups = $this->table('dynamic_field_groups');
        $dynamicFieldGroups->boolean('opened');

        $dynamicFieldGroupsI18n = $this->translatable('dynamic_field_groups');
        $dynamicFieldGroupsI18n->title();
    }

    protected function dynamicFieldsUp()
    {
        $dynamicFields = $this->table('dynamic_fields');
        $dynamicFields->varchar('field');
        $dynamicFields->integer('dynamic_field_type_id')->references('dynamic_field_types');
        $dynamicFields->integer('dynamic_table_id')->references('dynamic_tables');
        $dynamicFields->integer('dynamic_field_group_id')->references('dynamic_field_groups');
        $dynamicFields->integer('order');
        $dynamicFields->boolean('visible');
        $dynamicFields->boolean('searchable');
        $dynamicFields->orderable();

        $dynamicFieldsI18n = $this->translatable('dynamic_fields');
        $dynamicFieldsI18n->title();
        $dynamicFieldsI18n->text('help');

        $dynamicFieldsP17n = $this->permissiontable('dynamic_fields');
    }

    protected function dynamicRelationTypesUp()
    {
        $dynamicRelationTypes = $this->table('dynamic_relation_types');
        $dynamicRelationTypes->slug();

        $dynamicRelationTypesI18n = $this->translatable('dynamic_relation_types');
        $dynamicRelationTypesI18n->title();
    }

    protected function dynamicRelationsUp()
    {
        $dynamicRelations = $this->table('dynamic_relations');
        $dynamicRelations->integer('on_table_id')->references('dynamic_tables');
        $dynamicRelations->integer('on_field_id')->references('dynamic_fields');
        $dynamicRelations->integer('show_table_id')->references('dynamic_tables');
        $dynamicRelations->integer('dynamic_relation_type_id')->references('dynamic_relation_types');
        $dynamicRelations->varchar('value')->nullable();
        $dynamicRelations->integer('dynamic_table_tab_id')->references('dynamic_table_tabs')->nullable();
        $dynamicRelations->varchar('filter', 255)->nullable();
        $dynamicRelations->varchar('alias')->nullable();

        $dynamicRelationsI18n = $this->translatable('dynamic_relations');
        $dynamicRelationsI18n->title();

        $dynamicRelationsP17n = $this->permissiontable('dynamic_relations');
    }

    protected function dynamicTableTabsUp()
    {
        $dynamicTableTabs = $this->table('dynamic_table_tabs');
        $dynamicTableTabs->integer('dynamic_table_id')->references('dynamic_tables');
        $dynamicTableTabs->orderable();

        $dynamicTableTabsI18n = $this->translatable('dynamic_table_tabs');
        $dynamicTableTabsI18n->title();
    }

    protected function dynamicFunctionsUp()
    {
        $dynamicFunctions = $this->table('dynamic_functions');
        $dynamicFunctions->integer('dynamic_table_id')->references('dynamic_tables');
        $dynamicFunctions->integer('dynamic_table_tab_id')->references('dynamic_table_tabs')->nullable();
        $dynamicFunctions->varchar('class');
        $dynamicFunctions->varchar('method');

        $dynamicFunctionsI18n = $this->translatable('dynamic_functions');
        $dynamicFunctionsI18n->title();
    }

    protected function dynamicTableActionsUp()
    {
        $dynamicTableActions = $this->table('dynamic_table_actions');
        $dynamicTableActions->integer('dynamic_table_id')->references('dynamic_tables');
        $dynamicTableActions->slug('slug', 128, false);
        $dynamicTableActions->varchar('template');
        $dynamicTableActions->varchar('type');
        $dynamicTableActions->orderable();

        $dynamicTableActionsI18n = $this->translatable('dynamic_table_actions');
        $dynamicTableActionsI18n->title();

        $dynamicTableActionsP17n = $this->permissiontable('dynamic_table_actions');
    }

    protected function dynamicTableViewsUp()
    {
        $dynamicTableViews = $this->table('dynamic_table_views');
        $dynamicTableViews->integer('dynamic_table_id')->references('dynamic_tables');
        $dynamicTableViews->text('settings');

        $dynamicTableViewsI18n = $this->translatable('dynamic_table_views');
        $dynamicTableViewsI18n->title();

        $dynamicTableViewsP17n = $this->permissiontable('dynamic_table_views');
    }

}