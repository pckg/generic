<?php namespace Pckg\Dynamic\Migration;

use Pckg\Dynamic\Entity\Tables;
use Pckg\Migration\Migration;

abstract class DynamicMigration extends Migration
{

    public function upDynamic()
    {
        return [
            'packet_deductions' => [
                'id',
                'addition_id' => [
                    'type' => 'select',
                ],
                'title'       => [
                    'type' => 'varchar',
                ],
                '_relations'  => [
                    [
                        'type'          => 'belongs_to',
                        'table'         => 'packets',
                        'primary_field' => 'packet_id',
                        'foreign_field' => 'id',
                    ],
                    [
                        'type'          => 'belongs_to',
                        'table'         => 'additions',
                        'primary_field' => 'addition_id',
                        'foreign_field' => 'id',
                    ],
                ],
            ],
            'packets'           => [
                '_relations' => [
                    [
                        'type'          => 'has_many',
                        'table'         => 'packets_deductions',
                        'primary_field' => 'id',
                        'foreign_field' => 'packet_id',
                    ],
                ],
            ],
            'additions'         => [
                '_relations' => [
                    [
                        'type'          => 'has_many',
                        'table'         => 'packets_deductions',
                        'primary_field' => 'id',
                        'foreign_field' => 'addition_id',
                    ],
                ],
            ],
        ];
    }

    public function upDynamicTables()
    {
        $data = $this->upDynamic();

        foreach ($data as $table => $properties) {
            $dynamicTable = $this->upDynamicTable($table);
            foreach ($properties as $key => $val) {
                if ($val == 'id') {
                    $this->upDynamicId($dynamicTable);

                } else if ($key == '_relations') {
                    foreach ($val as $relation) {
                        $this->upDynamicRelation($dynamicTable, $relation);
                    }
                } else {
                    $this->upDynamicKey($dynamicTable, $key, $val);
                }
            }
        }
    }

    public function upDynamicTable($table)
    {
        $table = Tables::getOrCreate(['table' => $table]);
    }

}