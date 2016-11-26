<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Query\Raw;
use Pckg\Database\Record as DatabaseRecord;
use Pckg\Dynamic\Entity\Relations;

class Relation extends DatabaseRecord
{

    protected $entity = Relations::class;

    public function applyFilterOnEntity($entity, $foreignRecord)
    {
        if (!$this->filter) {
            return;
        }

        $entity->where(Raw::raw($this->eval($this->filter, $foreignRecord)));
    }

    public function eval($eval, $foreignRecord)
    {
        try {
            return eval(' return ' . $eval . '; ');
        } catch (\Exception $e) {
            return '-- ' . exception($e) . ' --';
        }
    }

}