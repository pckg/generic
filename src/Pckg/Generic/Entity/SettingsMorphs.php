<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Repository;
use Pckg\Generic\Record\SettingsMorph;

class SettingsMorphs extends Entity
{
    protected $record = SettingsMorph::class;

    public function setting()
    {
        return $this->belongsTo(Settings::class)
                    ->foreignKey('setting_id');
    }
}
