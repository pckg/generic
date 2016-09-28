<?php namespace Pckg\Dynamic\Entity;

use Pckg\Database\Entity;
use Pckg\Dynamic\Record\FieldType;

class FieldTypes extends Entity
{

    protected $table = 'dynamic_field_types';

    protected $record = FieldType::class;

}