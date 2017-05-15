<?php namespace Pckg\Dynamic\Controller;

use Pckg\Dynamic\Entity\Relations;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Record\TableView;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Dynamic\Service\Export as ExportService;
use Pckg\Dynamic\Service\Export\Strategy;
use Pckg\Framework\Controller;
use Pckg\Maestro\Service\Tabelize;

class Export extends Controller
{

    public function getExportTableAction(
        Table $table,
        Strategy $strategy,
        Dynamic $dynamicService,
        ExportService $exportService,
        TableView $tableView = null
    ) {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 60);
        set_time_limit(60);

        $entity = $table->createEntity();

        if ($tableView) {
            $dynamicService->setView($tableView);
        }

        $dynamicService->setTable($table);
        $dynamicService->applyOnEntity($entity, false);
        $dynamicService->selectScope($entity);

        /**
         * Get all relations for fields with type (select).
         */
        $listableFields = $table->listableFields;
        $listedFields = $table->getFields($listableFields, $dynamicService->getFilterService());
        $relations = (new Relations())->withShowTable()
                                      ->withOnField()
                                      ->where('on_table_id', $table->id)
                                      ->where('dynamic_relation_type_id', 1)
                                      ->where('on_field_id', $listedFields->map('id'))
                                      ->all();
        foreach ($relations as $relation) {
            $relation->loadOnEntity($entity, $dynamicService);
        }

        $fieldTransformations = $dynamicService->getFieldsTransformations($entity, $table->listableFields);

        /**
         * Also, try optimizing php fields. ;-)
         */
        $dynamicService->optimizeSelectedFields($entity, $listedFields);

        $strategy->input($entity);

        /**
         * @T00D00 - hackish ...
         */
        $tabelize = (new Tabelize($entity))
            ->setRecords($entity->all())
            ->setEntity($entity)
            ->setFields($listedFields)
            ->setFieldTransformations($fieldTransformations);

        $tabelize->setDataOnly();

        $strategy->setHeaders($listedFields->keyBy('field')->map('title'));

        $strategy->setData($tabelize->transformRecords());

        $strategy->setFileName($table->table . '-' . date('Ymd-his'));

        $strategy->prepare();

        $strategy->output();

        $this->response()->respond();
    }

}