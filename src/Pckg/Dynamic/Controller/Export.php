<?php namespace Pckg\Dynamic\Controller;

use Pckg\Dynamic\Entity\Relations;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Dynamic\Service\Export as ExportService;
use Pckg\Dynamic\Service\Export\Strategy;
use Pckg\Framework\Controller;
use Pckg\Maestro\Service\Tabelize;

class Export extends Controller
{

    public function getExportTableAction(
        Table $table, Strategy $strategy, Dynamic $dynamicService, ExportService $exportService
    ) {
        $entity = $table->createEntity();

        $dynamicService->setTable($table);
        $dynamicService->applyOnEntity($entity, false);
        $dynamicService->selectScope($entity);

        /**
         * Get all relations for fields with type (select).
         */
        $listableFields = $table->listableFields;
        $listedFields = $table->getFields($listableFields, $dynamicService->getFilterService());
        $relations = (new Relations())->where('on_table_id', $table->id)
                                      ->where('dynamic_relation_type_id', 1)
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