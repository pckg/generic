<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Database\Repository;
use Pckg\Dynamic\Record\FieldGroup;

class FieldGroups extends DatabaseEntity
{

    use Translatable;

    protected $table = 'dynamic_field_groups';

    protected $record = FieldGroup::class;

    protected $repositoryName = Repository::class . '.dynamic';

    public function boot()
    {
        $this->joinTranslations();
        $this->joinFallbackTranslation();
    }

}