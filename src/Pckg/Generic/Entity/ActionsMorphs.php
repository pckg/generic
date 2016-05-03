<?php namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;

use Pckg\Generic\Record\ActionsMorph;

class ActionsMorphs extends Entity
{

    protected $record = ActionsMorph::class;

    public function action()
    {
        return $this->belongsTo(Actions::class);
    }

    public function variable()
    {
        return $this->belongsTo(Variables::class);
    }

    public function content()
    {
        return $this->belongsTo(Contents::class);
    }

}