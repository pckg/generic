<?php namespace Pckg\Dynamic\Controller;

use Pckg\Dynamic\Entity\Relations;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Record\TableView;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Dynamic\Service\Export as ExportService;
use Pckg\Dynamic\Service\Export\Strategy;
use Pckg\Framework\Controller;
use Pckg\Locale\Record\Language;
use Pckg\Maestro\Service\Tabelize;
use Pckg\Manager\Locale\Locale;

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

        $entity = $table->createEntity(null, false);

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
                                      ->all();
        foreach ($relations as $relation) {
            $relation->loadOnEntity($entity, $dynamicService);
        }

        /**
         * Filter records by $_GET['search']
         */
        $dynamicService->getFilterService()->filterByGet($entity, $relations);
        $fieldTransformations = $dynamicService->getFieldsTransformations($entity, $table->listableFields);

        /**
         * Also, try optimizing php fields. ;-)
         */
        $dynamicService->optimizeSelectedFields($entity, $listedFields);

        $strategy->input($entity);

        $groups = $dynamicService->getGroupService()->getAppliedGroups();
        if ($groups) {
            $entity->addCount();
            $listedFields->push(['field' => 'count', 'title' => 'Count', 'type' => 'text']);
        }

        if (!$entity->getQuery()->getGroupBy()) {
            $entity->groupBy('`' . $entity->getTable() . '`.`id`');
        }

        /**
         * Allow extensions.
         */
        $fieldTransformations = collect($fieldTransformations);
        trigger(get_class($entity) . '.applyOnEntity', [$entity, 'listableFields' => $listableFields, 'fieldTransformations' => $fieldTransformations]);
        $fieldTransformations = $fieldTransformations->all();
        
        $records = $entity->all();
        $tabelize = (new Tabelize($entity))
            ->setRecords($records)
            ->setEntity($entity)
            ->setFields($listedFields)
            ->setFieldTransformations($fieldTransformations);

        $tabelize->setDataOnly();

        $strategy->setHeaders($listedFields->keyBy('field')->map('title'));

        $transformedRecords = $tabelize->transformRecords();

        /**
         * Check for additional export transformations.
         */
        $language = localeManager()->getLanguageBy('slug', $_SESSION['pckg_dynamic_lang_id']);
        $locale = new Locale($language ? $language->locale : 'en_GB');
        $listedFields->each(function($field) use ($strategy, &$transformedRecords) {
            if (!($field instanceof Field)) {
                return;
            }

            if ($field->getSetting('pckg-dynamic-field-nl2brExport' . ucfirst($strategy->getExtension()))) {
                foreach ($transformedRecords as &$record) {
                    $record[$field->field] = br2nl($record[$field->field]);
                }
            }
        });

        $strategy->setData($transformedRecords);

        $strategy->setFileName($table->table . '-' . date('Ymd-his'));

        $strategy->prepare();

        $strategy->output();

        $this->response()->respond();
    }

}