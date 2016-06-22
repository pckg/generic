<?php namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;

use Pckg\Generic\Record\ActionsMorph;

class ActionsMorphs extends Entity
{

    protected $record = ActionsMorph::class;

    public function action() {
        return $this->belongsTo(Actions::class)
                    ->foreignKey('action_id')
                    ->fill('action');
    }

    public function variable() {
        return $this->belongsTo(Variables::class)
                    ->foreignKey('variable_id')
                    ->fill('variable');
    }

    public function content() {
        return $this->belongsTo(Contents::class)
                    ->foreignKey('content_id')
                    ->fill('content');
    }

    public function settings() {
        return $this->morphsMany(Settings::class)
                    ->leftForeignKey('setting_id')
                    ->rightForeignKey('poly_id')
                    ->over(SettingsMorphs::class)
                    ->fill('settingsMorphs');
    }

}