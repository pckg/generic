<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Database\Relation\BelongsTo;
use Pckg\Database\Relation\HasAndBelongsTo;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Relation\MorphsMany;
use Pckg\Generic\Record\Route;

/**
 * Class Routes
 * @package Pckg\Generic\Entity
 */
class Routes extends Entity
{

    use Translatable;

    /**
     * @var
     */
    protected $record = Route::class;

    public function layout()
    {
        return $this->belongsTo(Layouts::class)
            ->foreignKey('layout_id')
            ->on('layout_id');
    }

    public function actions()
    {
        return $this->morphsMany(Actions::class)
            ->leftForeignKey('action_id')
            ->rightForeignKey('poly_id')
            ->over(ActionsMorphs::class)
            ->on('content_id')
            ->fill('actionsMorphs');
    }

    /**
     * @return $this
     */
    public function inExtendedContext()
    {
        return $this->withLayout(function (BelongsTo $relation) {
            $relation->joinTranslations();

        })->withActions(function (MorphsMany $relation) {
            $relation->getMiddleEntity()->withVariable();
            $relation->withContents(function(HasMany $relation){
                $relation->joinTranslations();

            });
        });
    }

}