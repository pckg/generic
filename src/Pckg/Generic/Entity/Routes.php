<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Relation\MorphsMany;
use Pckg\Generic\Record\Route;

/**
 * Class Routes
 *
 * @package Pckg\Generic\Entity
 */
class Routes extends Entity
{

    /**
     * @var
     */
    protected $record = Route::class;

    public function layout()
    {
        return $this->belongsTo(Layouts::class)
                    ->foreignKey('layout_id')
                    ->fill('layout');
    }

    public function actions()
    {
        return $this->morphedBy(Actions::class)
                    ->over(ActionsMorphs::class)
                    ->rightForeignKey('action_id');
    }

    public function actionsMorphs()
    {
        return $this->hasMany(ActionsMorphs::class)
                    ->foreignKey('poly_id')
                    ->where('morph_id', Routes::class);
    }

    public function settings()
    {
        return $this->morphedBy(Settings::class)
                    ->over(SettingsMorphs::class)
                    ->rightForeignKey('setting_id');
    }

    /**
     * @return $this
     */
    public function inExtendedContext()
    {
        return $this->withLayout()
                    ->withActions(
                        function(MorphsMany $relation) {
                            $relation->withContents();
                        }
                    );
    }

}