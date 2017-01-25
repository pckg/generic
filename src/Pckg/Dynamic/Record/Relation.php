<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Query\Raw;
use Pckg\Database\Record as DatabaseRecord;
use Pckg\Dynamic\Entity\Relations;
use Throwable;

class Relation extends DatabaseRecord
{

    protected $entity = Relations::class;

    public function applyFilterOnEntity($entity, $foreignRecord)
    {
        if (!$this->filter/* || !$foreignRecord*/) {
            return;
        }

        $evalResult = $this->eval($this->filter, $foreignRecord);

        if (!$evalResult) {
            return;
        }

        $entity->where(Raw::raw($evalResult));
    }

    public function eval($eval, $foreignRecord)
    {
        try {
            return eval(' return ' . $eval . '; ');
        } catch (Throwable $e) {
            if (prod()) {
                return null;
            }

            throw $e;
        }
    }

}