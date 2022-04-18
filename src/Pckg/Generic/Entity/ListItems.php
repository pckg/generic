<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Generic\Record\ListItem;

class ListItems extends Entity
{
    protected $record = ListItem::class;

    public function boot()
    {
        /*$this->joinTranslation()
             ->addSelect(['value' => 'IFNULL(' . $this->getAlias() . '_i18n.value, ' . $this->getAlias() . '.value)']);*/
        return $this;
    }
}
