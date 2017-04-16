<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Repository;
use Pckg\Dynamic\Record\FieldGroup;

class FieldGroups extends DatabaseEntity
{

    protected $table = 'dynamic_field_groups';

    protected $record = FieldGroup::class;

    protected $repositoryName = Repository::class . '.dynamic';

    public function boot()
    {
        $this->joinTranslations();
        $this->joinFallbackTranslation();
    }

}