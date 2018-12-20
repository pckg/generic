<?php namespace Pckg\Dynamic\Controller;

use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Relation;
use Pckg\Dynamic\Resolver\TableQl;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Maestro\Service\Tabelize;

class HttpQl
{

    public function searchIndexAction(Dynamic $dynamicService)
    {
        /**
         * Fetch main table set in request.
         */
        $path = get('path', null);
        if (substr($path, 0, 1) == '/') {
            $path = substr($path, 1);
        }
        $table = (new TableQl($dynamicService))->resolve($path);

        /**
         * Read Orm data.
         */
        $ormFields = json_decode(post('X-Pckg-Orm-Fields'), true);
        $ormFilters = json_decode(post('X-Pckg-Orm-Filters'), true);
        $ormPaginator = json_decode(post('X-Pckg-Orm-Paginator'), true);
        $ormSearch = json_decode(post('X-Pckg-Orm-Search'), true);
        $ormMeta = json_decode(post('X-Pckg-Orm-Meta'), true);

        /**
         * When relation is set we want to display only values for related product.
         * @T00D00 - solve this on JS level, apply proper filter.
         */
        $entity = null;
        if ($relation) {
            $entity = $relation->over_table_id ? $relation->overTable->createEntity()
                : $relation->showTable->createEntity();

            $relation->applyRecordFilterOnEntity($record, $entity);
        } else {
            $entity = $table->createEntity(null, false);
        }

        /**
         * Apply filter, sort, group, limit and fields sub-services.
         */
        $dynamicService->setTable($table);
        $dynamicService->getFilterService()->applyOnEntity($entity, $ormFilters);
        $dynamicService->getPaginateService()->applyOnEntity($entity, $ormPaginator);
        $dynamicService->getFilterService()->filterByGet($entity, null, $ormSearch);
        $dynamicService->getSortService()->applyOnEntity($entity, $ormPaginator);

        /**
         * Apply relation
         */
        if ($ormMeta && isset($ormMeta['relation'])) {
            $relation = Relation::gets($ormMeta['relation']);
            $entity->where($relation->onField->field, $ormMeta['record']);
        }

        /**
         * Join extensions (translations, permissions and deletable).
         */
        $dynamicService->selectScope($entity);

        /**
         * Get all relations for fields with type (select).
         * @T00D00 - load only required data.
         */
        $listableFields = $table->listableFields;
        $listedFields = $table->getFields($listableFields, $dynamicService->getFilterService());
        $relations = (new \Pckg\Dynamic\Entity\Relations())->withShowTable()
                                                           ->withOnField()
                                                           ->withForeignField()
                                                           ->where('on_table_id', $table->id)
                                                           ->where('dynamic_relation_type_id', 1)
                                                           ->all();

        foreach ($relations as $r) {
            $r->loadOnEntity($entity, $dynamicService);
        }

        /**
         * Transform custom fields (php, geo).
         */
        $fieldTransformations = $dynamicService->getFieldsTransformations($entity, $listableFields);

        /**
         * Optimize selected fields (php, mysql).
         */
        $dynamicService->optimizeSelectedFields($entity, $listedFields);

        /**
         * Group by primary key when joins are made so we display only 1 record per ID.
         */
        if (!$entity->getQuery()->getGroupBy()) {
            $entity->groupBy('`' . $entity->getTable() . '`.`id`');
        }

        /**
         * Fetch page records and total.
         */
        $records = $entity->count()->all();
        $total = $records->total();

        /**
         * Prepare tabelize.
         */
        $tabelize = new Tabelize();

        $tabelize->setTable($table);

        /**
         * Set proper fields.
         */
        $fields = [];
        foreach ($ormFields as $field) {
            if (strpos($field, '.') !== false) {
                continue;
            }

            $fieldRecord = Field::gets(['field' => $field, 'dynamic_table_id' => $table->id]);
            if (!$fieldRecord) {
                continue;
            }

            $fields[] = $fieldRecord;
        }
        $tabelize->setFields($fields);

        /**
         * Transform records for frontend.
         */
        $records = $tabelize->transformCollection($records);

        /**
         * Return all data.
         */
        return [
            'records'   => $records,
            'groups'    => [],
            'paginator' => [
                'total' => $total,
                'url'   => router()->getUri() . (get('search') ? '?search=' . get('search') : ''),
            ],
        ];
    }

}