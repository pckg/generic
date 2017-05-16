<?php namespace Pckg\Dynamic\Service;

use Pckg\Collection;
use Pckg\Database\Entity;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Service\Filter as FilterService;
use Pckg\Dynamic\Service\Group as GroupService;
use Pckg\Dynamic\Service\Sort as OrderService;
use Pckg\Framework\Locale\Lang;
use Pckg\Framework\Request\Data\Session;

class Dynamic
{

    /**
     * @var Session
     */
    protected $session;

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
        Session $session,
        FilterService $filterService,
        OrderService $sortService,
        GroupService $groupService,
        Paginate $paginateService,
        Fields $fieldsService
    ) {
        $this->session = $session;
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

            $entity->setTranslatableLang((new Lang($_SESSION['pckg_dynamic_lang_id'])));
            $entity->joinTranslations();
        }
    }

    public function joinPermissionsIfPermissionable($entity)
    {
        if ($entity->isPermissionable()) {
        }
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

    public function applyOnEntity($entity, $limit = true)
    {
        $this->filterService->applyOnEntity($entity);
        $this->sortService->applyOnEntity($entity);
        $this->groupService->applyOnEntity($entity);
        if ($limit) {
            $this->paginateService->applyOnEntity($entity);
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
        $listedFields->each(function(Field $field) use ($entity) {
            if ($field->fieldType->slug == 'php' &&
                method_exists($entity, 'select' . ucfirst($field->field) . 'Field')
            ) {
                $entity->{'select' . ucfirst($field->field) . 'Field'}();
            }
        });
    }

    public function getFieldsTransformations(Entity $entity, Collection $fields)
    {
        $fieldTransformations = [];

        $fields->each(
            function(Field $field) use (&$fieldTransformations, $entity) {
                $transformation = $field->getTransformedValue($entity);

                if ($transformation) {
                    $fieldTransformations[$field->field] = $transformation;
                }
            }
        );

        return $fieldTransformations;
    }

}