<?php namespace Pckg\Dynamic\Service;

use Pckg\Database\Entity;
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
    )
    {
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

    public function removeDeletedIfDeletable($entity)
    {
        if ($entity->isDeletable()) {
            $entity->nonDeleted();
        }
    }

    public function joinTranslationsIfTranslatable($entity)
    {
        if ($entity->isTranslated()) {
            return;
        }

        if ($entity->isTranslatable()) {
            $session = $this->session;
            $entity->joinTranslation(
                function(Entity $entity) use ($session) {
                    $entity->setTranslatableLang((new Lang())->setLangId($session->pckg_dynamic_lang_id ?: 'en'));
                }
            );
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

}