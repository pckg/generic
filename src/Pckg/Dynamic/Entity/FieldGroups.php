<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Entity\Extension\Translatable;
use Pckg\Dynamic\Record\FieldGroup;

class FieldGroups extends DatabaseEntity
{

    use Translatable;

    protected $table = 'dynamic_field_groups';

    protected $record = FieldGroup::class;

    public function boot()
    {
        $this->joinTranslations();
        $this->joinFallbackTranslation();
    }

}