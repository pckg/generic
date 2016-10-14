<?php namespace Pckg\Generic\Record;

use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Resolver\Table;
use Pckg\Generic\Entity\ActionsMorphs;
use Pckg\Generic\Entity\SettingsMorphs;

class SettingsMorph extends Record
{

    protected $entity = SettingsMorphs::class;

    public function resolve(&$args)
    {
        if ($this->setting_id == 1) {
            $args[] = Reflect::class(Table::class)->resolve($this->value);

        } else if ($this->setting_id == 7) {
            $args[] = (new ActionsMorphs())->where('id', $this->value)->oneOrFail();

        } else if ($this->setting_id == 9) {
            $args[] = (new Tables())->where('id', $this->value)->oneOrFail();

        }
    }

}