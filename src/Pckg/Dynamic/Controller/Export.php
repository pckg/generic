<?php namespace Pckg\Dynamic\Controller;

use Pckg\Database\Entity;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Repository;
use Pckg\Dynamic\Entity\Relations;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Dynamic\Service\Export as ExportService;
use Pckg\Dynamic\Service\Export\Strategy;
use Pckg\Framework\Controller;
use Pckg\Maestro\Service\Tabelize;

class Export extends Controller
{

    /**
     * @var ExportService
     */
    protected $exportService;

    protected $dynamic;

    public function __construct(ExportService $exportService, Dynamic $dynamic)
    {
        $this->exportService = $exportService;
        $this->dynamic = $dynamic;
    }

    public function getExportTableAction(Table $table, Strategy $strategy, Dynamic $dynamicService)
    {
        $entity = $table->createEntity();
        $this->dynamic->setTable($table);

        $this->dynamic->applyOnEntity($entity, false);

        $dynamicService->joinTranslationsIfTranslatable($entity);
        $dynamicService->joinPermissionsIfPermissionable($entity);

        /**
         * Get all relations for fields with type (select).
         */
        $relations = (new Relations())->where('on_table_id', $table->id)
                                      ->where('dynamic_relation_type_id', 1)
                                      ->all();
        foreach ($relations as $relation) {
            /**
             * Right table entity is created here.
             */
            $relationEntity = $relation->showTable->createEntity();

            /**
             * We need to add relations to select.
             * $tableRecord is for example users.
             * So entity is entity with table users.
             * We will fetch all users and related user_group_id and language_id
             * as user.relation_user_group_id and user.relation_language_id.
             */
            $entity->with(
                (new BelongsTo($entity, $relationEntity))
                    ->foreignKey($relation->onField->field)
                    ->fill('relation_' . $relation->onField->field)
                    ->after(
                        function($record) use ($relation) {
                            $record->setRelation('select_relation_' . $relation->onField->field, $relation);
                        }
                    )
            );
        }

        $strategy->input($entity);

        /**
         * @T00D00 - hackish ...
         */
        $tabelize = (new Tabelize($entity))
            ->setRecords($entity->all())
            ->setEntity($entity)
            ->setFields(
                $table->listableFields->reduce(
                    function(Field $field) use ($table) {
                        $fields = $this->dynamic->getFilterService()->getSession('fields');

                        return !$fields || in_array($field->field, $fields);
                    }
                )
            );

        $tabelize->setDataOnly();
        $strategy->setData($tabelize->transformRecords());

        $strategy->setFileName($table->table . '-' . date('Ymd-his'));

        $strategy->prepare();

        $strategy->output();

        $this->response()->respond();
    }

}