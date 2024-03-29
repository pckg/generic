<?php

namespace Pckg\Dynamic\Service;

use Pckg\Collection;
use Pckg\Database\Entity;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Service\Filter as FilterService;
use Pckg\Dynamic\Service\Group as GroupService;
use Pckg\Dynamic\Service\Sort as OrderService;
use Pckg\Locale\Lang;

class Dynamic
{
    /**
     * @var Filter
     */
    protected $filterService;

    protected $sortService;

    protected $groupService;

    protected $paginateService;

    protected $fieldsService;

    protected $table;

    protected $view;

    public function __construct(
        FilterService $filterService,
        OrderService $sortService,
        GroupService $groupService,
        Paginate $paginateService,
        Fields $fieldsService
    ) {
        $this->filterService = $filterService;
        $this->sortService = $sortService;
        $this->groupService = $groupService;
        $this->paginateService = $paginateService;
        $this->fieldsService = $fieldsService;
    }

    public function getGroupService()
    {
        return $this->groupService;
    }

    public function getPaginateService()
    {
        return $this->paginateService;
    }

    public function getFilterService()
    {
        return $this->filterService;
    }

    public function getFieldsService()
    {
        return $this->fieldsService;
    }

    public function getSortService()
    {
        return $this->sortService;
    }

    public function removeDeletedIfDeletable($entity)
    {
        if ($entity->isDeletable()) {
            $entity->nonDeleted();
        }
    }

    public function joinTranslationsIfTranslatable(Entity $entity)
    {
        if ($entity->isTranslatable()) {
            if ($entity->isTranslated()) {
                return;
            }

            $entity->setTranslatableLang((new Lang(localeManager()->getCurrent(2))));
            $entity->joinTranslations();
        }
    }

    public function joinPermissionsIfPermissionable(Entity $entity, $action = 'read')
    {
        if (!$entity->isPermissionable()) {
            return;
        }

        if ($auth = $entity->getPermissionableAuth()) {
            $userId = $auth->userId();
            if ($userId && (auth()->user('id') === $userId) && auth()->isAdmin()) {
                return;
            }
        }

        $entity->joinPermissionTo($action);
    }

    public function getContentLanguage()
    {
    }

    public function getTable()
    {
        return $this->table;
    }

    public function setTable($table)
    {
        $this->table = $table;

        $this->filterService->setTable($table);
        $this->sortService->setTable($table);
        $this->groupService->setTable($table);
        $this->fieldsService->setTable($table);
    }

    public function setView($view)
    {
        $this->view = $view;

        $this->filterService->setView($view);
        $this->sortService->setView($view);
        $this->groupService->setView($view);
        $this->fieldsService->setView($view);
    }

    public function getView()
    {
        return $this->view;
    }

    public function applyOnEntity($entity, $limit = 50, $page = 1)
    {
        $this->filterService->applyOnEntity($entity);
        $this->sortService->applyOnEntity($entity);
        $this->groupService->applyOnEntity($entity);
        if ($limit) {
            $this->paginateService->applyOnEntity($entity, $limit, $page);
        }
        $this->fieldsService->applyOnEntity($entity);
    }

    public function selectScope(Entity $entity)
    {
        $this->joinTranslationsIfTranslatable($entity);
        $this->joinPermissionsIfPermissionable($entity);
        $this->removeDeletedIfDeletable($entity);
    }

    public function optimizeSelectedFields(Entity $entity, Collection $listedFields)
    {
        $listedFields->each(function (Field $field) use ($entity) {
            $slug = $field->fieldType ? $field->fieldType->slug : null;
            if (in_array($slug, ['php', 'mysql'])) {
                if (method_exists($entity, 'select' . ucfirst($field->field) . 'Field')) {
                    $entity->{'select' . ucfirst($field->field) . 'Field'}();
                } else {
                    message('Optimize field ' . $field->field, 'optimize');
                }
            }
        });
    }

    public function getFieldsTransformations(Entity $entity, Collection $fields)
    {
        $fieldTransformations = [];

        $fields->each(
            function (Field $field) use (&$fieldTransformations, $entity) {
                $transformation = $field->getTransformedValue($entity);

                if ($transformation) {
                    $fieldTransformations[$field->field] = $transformation;
                }
            }
        );

        return $fieldTransformations;
    }
}
