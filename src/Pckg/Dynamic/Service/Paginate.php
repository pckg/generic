<?php namespace Pckg\Dynamic\Service;

use Pckg\Database\Entity;
use Pckg\Dynamic\Record\Table;
use Pckg\Framework\Request\Data\Get;

class Paginate
{

    protected $table;

    protected $get;

    public function __construct(Get $get)
    {
        $this->get = $get;
    }

    public function setTable(Table $table)
    {
        $this->table = $table;

        return $this;
    }

    public function applyOnEntity(Entity $entity, $perPage = 50)
    {
        $limit = get('perPage', $perPage);

        $entity->count();

        $entity->limit($limit == 'all' ? null : $limit);

        $entity->page($this->get->get('page') ?? 1);

        return $this;
    }

}