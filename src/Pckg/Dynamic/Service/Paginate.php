<?php

namespace Pckg\Dynamic\Service;

use Pckg\Database\Entity;
use Pckg\Dynamic\Record\Table;
use Pckg\Framework\Request\Data\Get;

class Paginate
{

    protected $table;

    protected $get;

    const LIMIT = 25;

    public function __construct(Get $get)
    {
        $this->get = $get;
    }

    public function setTable(Table $table)
    {
        $this->table = $table;

        return $this;
    }

    public function applyOnEntity(Entity $entity, $ormPaginator)
    {
        $entity->count();

        $limit = $ormPaginator['limit'] ?? static::LIMIT;
        $entity->limit($limit == 'all' ? null : $limit);

        $page = $ormPaginator['page'] ?? 1;
        $entity->page($page);

        return $this;
    }
}
