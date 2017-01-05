<?php

namespace Pckg\Generic\Entity;

use Pckg\Database\Entity;
use Pckg\Database\Entity\Extension\Translatable;
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

    use Translatable;

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

    /**
     * @return $this
     */
    public function inExtendedContext()
    {
        return $this->withLayout()
                    ->withActions(
                        function(MorphsMany $relation) {
                            $relation->getMiddleEntity()->withVariable();
                            $relation->withContents(
                                function(HasMany $relation) {
                                    $relation->joinTranslations();

                                }
                            );
                        }
                    );
    }

}