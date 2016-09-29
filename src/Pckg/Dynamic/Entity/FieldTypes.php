<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Dynamic\Record\FieldType;

class FieldTypes extends DatabaseEntity
{

    protected $table = 'dynamic_field_types';

    protected $record = FieldType::class;

}