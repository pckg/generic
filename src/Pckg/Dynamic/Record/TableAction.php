<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Record as DatabaseRecord;
use Pckg\Dynamic\Entity\TableActions;

class TableAction extends DatabaseRecord
{

    protected $entity = TableActions::class;

    public function getEntityTemplateAttribute()
    {
        if (!$this->template) {
            return $this->slug;
        }

        if (!strpos($this->template, '@')) {
            return $this->template;
        }

        if (strpos($this->template, ':')) {
            return substr($this->template, strpos($this->template, ':') + 1);
        }

        return substr($this->template, strpos($this->template, '@') + 1);
    }

}