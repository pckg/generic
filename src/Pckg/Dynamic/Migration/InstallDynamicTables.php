<?php

namespace Pckg\Dynamic\Migration;

use Pckg\Database\Collection;
use Pckg\Dynamic\Entity\Fields;
use Pckg\Dynamic\Entity\FieldTypes;
use Pckg\Dynamic\Entity\RelationTypes;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\FieldType;
use Pckg\Dynamic\Record\Relation;
use Pckg\Dynamic\Record\RelationType;
use Pckg\Dynamic\Record\Tab;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Record\TableAction;
use Pckg\Migration\Migration;

class InstallDynamicTables extends Migration
{

    public function up()
    {
        $this->installTypes();
        $this->installTables();
        $this->installRelations();
        $this->installGnp();

        return $this;
    }

    protected function installTypes()
    {
        $dynamicFieldTypes = [
            'id',
            'integer',
            'email',
            'text',
            'editor',
            'slug',
            'password',
            'select',
            'order',
            'datetime',
            'date',
            'time',
            'hash',
            'decimal',
            'textarea',
            'boolean',
            'file',
            'picture',
        ];

        $dynamicRelationTypes = [
            'belongs_to',
            'has_many',
            'has_one',
            'has_and_belongs_to',
            'morphed_by',
            'morphs_many',
        ];

        $fieldTypes = new Collection();
        foreach ($dynamicFieldTypes as $fieldType) {
            $record = (new FieldType())->setData(['slug' => $fieldType]);
            $record->save();
            $fieldTypes->push($record, $fieldType);
        }

        $relationTypes = new Collection();
        foreach ($dynamicRelationTypes as $relationType) {
            $record = (new RelationType())->setData(['slug' => $relationType]);
            $record->save();
            $relationTypes->push($record, $relationType);
        }
    }

    public function installTables()
    {

        $dynamicTables = [
            [
                'table'    => 'dynamic_tables',
                '_tabs'    => [],
                '_fields'  => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'table',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'framework_entity',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'repository',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                ],
                '_actions' => [],
            ],
            [
                'table'    => 'dynamic_table_tabs',
                '_tabs'    => [],
                '_fields'  => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'dynamic_table_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'order',
                        'type'  => 'order',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                ],
                '_actions' => [],
            ],
            [
                'table'    => 'dynamic_table_views',
                '_tabs'    => [],
                '_fields'  => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'dynamic_table_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'settings',
                        'type'  => 'text',
                    ],
                ],
                '_actions' => [],
            ],
            [
                'table'    => 'dynamic_table_actions',
                '_tabs'    => [],
                '_fields'  => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'dynamic_table_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'slug',
                        'type'  => 'slug',
                    ],
                    [
                        'field' => 'type',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                ],
                '_actions' => [],
            ],
            [
                'table'    => 'dynamic_fields',
                '_tabs'    => [],
                '_fields'  => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'field',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'dynamic_field_type_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'order',
                        'type'  => 'order',
                    ],
                    [
                        'field' => 'dynamic_table_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'help',
                        'type'  => 'text',
                    ],
                ],
                '_actions' => [],
            ],
            [
                'table'    => 'dynamic_functions',
                '_tabs'    => [],
                '_fields'  => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'dynamic_table_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'dynamic_table_tab_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'class',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'method',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                ],
                '_actions' => [],
            ],
            [
                'table'    => 'dynamic_relations',
                '_tabs'    => [],
                '_fields'  => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'on_table_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'dynamic_relation_type_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'show_table_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'on_field_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'dynamic_table_tab_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'value',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                ],
                '_actions' => [],
            ],
            [
                'table'    => 'dynamic_field_types',
                '_tabs'    => [],
                '_fields'  => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'slug',
                        'type'  => 'slug',
                    ],
                ],
                '_actions' => [],
            ],
            [
                'table'    => 'dynamic_relation_types',
                '_tabs'    => [],
                '_fields'  => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'slug',
                        'type'  => 'slug',
                    ],
                ],
                '_actions' => [],
            ],
        ];

        $this->install($dynamicTables, []);
    }

    public function installRelations()
    {
        $relations = [
            [
                'on_table'      => 'dynamic_fields',
                'on_field'      => 'dynamic_field_type_id',
                'show_table'    => 'dynamic_field_types',
                'relation_type' => 'belongs_to',
                'value'         => '$record->slug',
            ],
            [
                'on_table'      => 'dynamic_fields',
                'on_field'      => 'dynamic_table_id',
                'show_table'    => 'dynamic_tables',
                'relation_type' => 'belongs_to',
                'value'         => '$record->table',
            ],
            [
                'on_table'      => 'dynamic_functions',
                'on_field'      => 'dynamic_table_id',
                'show_table'    => 'dynamic_tables',
                'relation_type' => 'belongs_to',
                'value'         => '$record->table',
            ],
            [
                'on_table'      => 'dynamic_functions',
                'on_field'      => 'dynamic_table_tab_id',
                'show_table'    => 'dynamic_table_tabs',
                'relation_type' => 'belongs_to',
                'value'         => '$record->id',
            ],
            [
                'on_table'      => 'dynamic_relations',
                'on_field'      => 'on_table_id',
                'show_table'    => 'dynamic_tables',
                'relation_type' => 'belongs_to',
                'value'         => '$record->table',
            ],
            [
                'on_table'      => 'dynamic_relations',
                'on_field'      => 'on_field_id',
                'show_table'    => 'dynamic_fields',
                'relation_type' => 'belongs_to',
                'value'         => '$record->field',
            ],
            [
                'on_table'      => 'dynamic_relations',
                'on_field'      => 'show_table_id',
                'show_table'    => 'dynamic_tables',
                'relation_type' => 'belongs_to',
                'value'         => '$record->table',
            ],
            [
                'on_table'      => 'dynamic_relations',
                'on_field'      => 'dynamic_relation_type_id',
                'show_table'    => 'dynamic_relation_types',
                'relation_type' => 'belongs_to',
                'value'         => '$record->slug',
            ],
            [
                'on_table'      => 'dynamic_relations',
                'on_field'      => 'dynamic_table_tab_id',
                'show_table'    => 'dynamic_table_tabs',
                'relation_type' => 'belongs_to',
                'value'         => '$record->id',
            ],
            [
                'on_table'      => 'dynamic_table_actions',
                'on_field'      => 'dynamic_table_id',
                'show_table'    => 'dynamic_tables',
                'relation_type' => 'belongs_to',
                'value'         => '$record->table',
            ],
            [
                'on_table'      => 'dynamic_table_tabs',
                'on_field'      => 'dynamic_table_id',
                'show_table'    => 'dynamic_tables',
                'relation_type' => 'belongs_to',
                'value'         => '$record->table',
            ],
            [
                'on_table'      => 'dynamic_table_views',
                'on_field'      => 'dynamic_table_id',
                'show_table'    => 'dynamic_tables',
                'relation_type' => 'belongs_to',
                'value'         => '$record->table',
            ],
        ];

        $this->install([], $relations);
    }

    public function installGnp()
    {
        $tables = [
            [
                'table'      => 'users',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'status_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'city_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'password',
                        'type'  => 'password',
                    ],
                    [
                        'field' => 'email',
                        'type'  => 'email',
                    ],
                    [
                        'field' => 'name',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'surname',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'dt_birth',
                        'type'  => 'date',
                    ],
                    [
                        'field' => 'address',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'phone',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'enabled',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'post',
                        'type'  => 'text',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'translations',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'slug',
                        'type'  => 'slug',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'content',
                        'type'  => 'editor',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'tags',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'statuses',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'settings',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'skey',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'svalue',
                        'type'  => 'text',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'promo_codes',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'code',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'value',
                        'type'  => 'decimal',
                    ],
                    [
                        'field' => 'limit',
                        'type'  => 'integer',
                    ],
                    [
                        'field' => 'active',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'promo_code_type_id',
                        'type'  => 'select',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'paypal',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'order_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'user_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'order_hash',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'paypal_hash',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'paypal_id',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'status',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'dt_started',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_confirmed',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'price',
                        'type'  => 'decimal',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'pages',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'content',
                        'type'  => 'editor',
                    ],
                    [
                        'field' => 'url',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'system',
                        'type'  => 'boolean',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'packets_tabs',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'packet_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'content',
                        'type'  => 'editor',
                    ],
                    [
                        'field' => 'published',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'position',
                        'type'  => 'order',
                    ],
                    [
                        'field' => 'picture',
                        'type'  => 'picture',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'packets_includes',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'packet_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'addition_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'value',
                        'type'  => 'text',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'packets_cities',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'packet_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'city_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'dt_departure',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'fee',
                        'type'  => 'decimal',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'packets_additions',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'addition_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'packet_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'value',
                        'type'  => 'decimal',
                    ],
                    [
                        'field' => 'visible',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'quantity',
                        'type'  => 'decimal',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'packets',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'price',
                        'type'  => 'decimal',
                    ],
                    [
                        'field' => 'offer_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'dt_published',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_opened',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_closed',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'position',
                        'type'  => 'order',
                    ],
                    [
                        'field' => 'ticket',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'order_limit',
                        'type'  => 'integer',
                    ],
                    [
                        'field' => 'order_limit_count',
                        'type'  => 'integer',
                    ],
                    [
                        'field' => 'combinations',
                        'type'  => 'text',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'orders_users_additions',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'orders_user_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'addition_id',
                        'type'  => 'select',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'orders_users',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'packet_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'user_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'dt_added',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_confirmed',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_rejected',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_canceled',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'order_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'city_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'notes',
                        'type'  => 'textarea',
                    ],
                    [
                        'field' => 'admin_notes',
                        'type'  => 'textarea',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'orders_tags',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'order_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'type',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'value',
                        'type'  => 'text',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'orders_bills',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'order_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'dt_added',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_valid',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_confirmed',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'price',
                        'type'  => 'decimal',
                    ],
                    [
                        'field' => 'notes',
                        'type'  => 'textarea',
                    ],
                    [
                        'field' => 'dt_override',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'bill_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'url',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'reservation',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'type',
                        'type'  => 'integer',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'orders',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'num',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'offer_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'dt_added',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_confirmed',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_rejected',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_canceled',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_payed',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_finished',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'user_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'hash',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'bill_url',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'estimate_url',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'voucher_url',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'original',
                        'type'  => 'decimal',
                    ],
                    [
                        'field' => 'dt_locked',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'wishes',
                        'type'  => 'textarea',
                    ],
                    [
                        'field' => 'referer',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'dt_notify_reservation',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'promo_code_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'voucher_sent_at',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'furs_eor',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'furs_zoi',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'furs_confirmed_at',
                        'type'  => 'datetime',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'offers_tags',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'tag_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'offer_id',
                        'type'  => 'select',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'offers_promo_codes',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'offer_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'promo_code_id',
                        'type'  => 'select',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'offers_pictures',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'offer_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'url',
                        'type'  => 'picture',
                    ],
                    [
                        'field' => 'main',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'thumb',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'position',
                        'type'  => 'order',
                    ],
                    [
                        'field' => 'url_main',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'url_thumb',
                        'type'  => 'text',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'offers_payment_methods',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'offer_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'method',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'bill',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'enabled',
                        'type'  => 'boolean',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'offers',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'subtitle',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'city_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'dt_start',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_end',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'short_content',
                        'type'  => 'editor',
                    ],
                    [
                        'field' => 'content',
                        'type'  => 'editor',
                    ],
                    [
                        'field' => 'dt_published',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'published',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'pickup_line',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'firstpage',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'top',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'dt_opened',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_closed',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'soundcloud',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'picture',
                        'type'  => 'picture',
                    ],
                    [
                        'field' => 'order_limit',
                        'type'  => 'integer',
                    ],
                    [
                        'field' => 'price_text',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'category_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'lineup',
                        'type'  => 'editor',
                    ],
                    [
                        'field' => 'fb_event_url',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'order_limit_count',
                        'type'  => 'integer',
                    ],
                    [
                        'field' => 'private',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'max_portions',
                        'type'  => 'integer',
                    ],
                    [
                        'field' => 'rebuy',
                        'type'  => 'text',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'news_tags',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'news_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'tag_id',
                        'type'  => 'select',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'news',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'content',
                        'type'  => 'editor',
                    ],
                    [
                        'field' => 'picture',
                        'type'  => 'picture',
                    ],
                    [
                        'field' => 'dt_published',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'content_short',
                        'type'  => 'editor',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'moneta',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'confirmationid',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'confirmationsignature',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'tarifficationerror',
                        'type'  => 'integer',
                    ],
                    [
                        'field' => 'startdate',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'confirmdate',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'refreshcounter',
                        'type'  => 'integer',
                    ],
                    [
                        'field' => 'purchasestatus',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'providerdata',
                        'type'  => 'textarea',
                    ],
                    [
                        'field' => 'order_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'user_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'data',
                        'type'  => 'textarea',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'mails_sents',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'mail_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'subject',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'content',
                        'type'  => 'editor',
                    ],
                    [
                        'field' => 'from',
                        'type'  => 'email',
                    ],
                    [
                        'field' => 'to',
                        'type'  => 'email',
                    ],
                    [
                        'field' => 'datetime',
                        'type'  => 'datetime',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'mails',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'subject',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'content',
                        'type'  => 'editor',
                    ],
                    [
                        'field' => 'sender',
                        'type'  => 'email',
                    ],
                    [
                        'field' => 'identifier',
                        'type'  => 'slug',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'logins',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'user_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'datetime',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'active',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'hash',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'dt_logged_out',
                        'type'  => 'datetime',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'galleries_tags',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'gallery_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'tag_id',
                        'type'  => 'select',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'galleries_pictures',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'gallery_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'url',
                        'type'  => 'picture',
                    ],
                    [
                        'field' => 'main',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'thumb',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'url_main',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'url_thumb',
                        'type'  => 'text',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'galleries',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'dt_published',
                        'type'  => 'datetime',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'countries',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'cities',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'country_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'departure',
                        'type'  => 'boolean',
                    ],
                    [
                        'field' => 'code',
                        'type'  => 'text',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'categories',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'position',
                        'type'  => 'order',
                    ],
                    [
                        'field' => 'published',
                        'type'  => 'boolean',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'braintree',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'order_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'user_id',
                        'type'  => 'select',
                    ],
                    [
                        'field' => 'order_hash',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'braintree_hash',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'braintree_client_token',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'braintree_payment_nonce',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'braintree_transaction_id',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'dt_started',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'dt_confirmed',
                        'type'  => 'datetime',
                    ],
                    [
                        'field' => 'price',
                        'type'  => 'decimal',
                    ],
                    [
                        'field' => 'state',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'error',
                        'type'  => 'textarea',
                    ],
                    [
                        'field' => 'data',
                        'type'  => 'textarea',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
            [
                'table'      => 'additions',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'value',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'short',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'position',
                        'type'  => 'order',
                    ],
                    [
                        'field' => 'description',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'max_quantity',
                        'type'  => 'integer',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],

            ],
            [
                'table'      => 'abbreviations',
                'repository' => 'gnp',
                '_fields'    => [
                    [
                        'field' => 'id',
                        'type'  => 'id',
                    ],
                    [
                        'field' => 'url',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'short',
                        'type'  => 'text',
                    ],
                    [
                        'field' => 'clicks',
                        'type'  => 'integer',
                    ],
                ],
                '_tabs'      => [],
                '_actions'   => [],
            ],
        ];

        $relations = [
            [
                'on_table'      => 'braintree',
                'on_field'      => 'order_id',
                'show_table'    => 'orders',
                'relation_type' => 'belongs_to',
                'value'         => '$record->id . \' \' . $record->num',
            ],
            [
                'on_table'      => 'braintree',
                'on_field'      => 'user_id',
                'show_table'    => 'users',
                'relation_type' => 'belongs_to',
                'value'         => '$record->email',
            ],
            [
                'on_table'      => 'cities',
                'on_field'      => 'country_id',
                'show_table'    => 'countries',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'galleries_pictures',
                'on_field'      => 'gallery_id',
                'show_table'    => 'galleries',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'galleries_tags',
                'on_field'      => 'tag_id',
                'show_table'    => 'tags',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'galleries_tags',
                'on_field'      => 'gallery_id',
                'show_table'    => 'galleries',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'logins',
                'on_field'      => 'user_id',
                'show_table'    => 'users',
                'relation_type' => 'belongs_to',
                'value'         => '$record->email',
            ],
            [
                'on_table'      => 'mails_sents',
                'on_field'      => 'mail_id',
                'show_table'    => 'mails',
                'relation_type' => 'belongs_to',
                'value'         => '$record->subject',
            ],
            [
                'on_table'      => 'moneta',
                'on_field'      => 'order_id',
                'show_table'    => 'orders',
                'relation_type' => 'belongs_to',
                'value'         => '$record->id . \' \' . $record->num',
            ],
            [
                'on_table'      => 'moneta',
                'on_field'      => 'user_id',
                'show_table'    => 'users',
                'relation_type' => 'belongs_to',
                'value'         => '$record->email',
            ],
            [
                'on_table'      => 'news_tags',
                'on_field'      => 'news_id',
                'show_table'    => 'news',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'news_tags',
                'on_field'      => 'tag_id',
                'show_table'    => 'tags',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'offers',
                'on_field'      => 'city_id',
                'show_table'    => 'cities',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'offers',
                'on_field'      => 'category_id',
                'show_table'    => 'categories',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'offers_payment_methods',
                'on_field'      => 'offer_id',
                'show_table'    => 'offers',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'offers_pictures',
                'on_field'      => 'offer_id',
                'show_table'    => 'offers',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'offers_promo_codes',
                'on_field'      => 'offer_id',
                'show_table'    => 'offers',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'offers_promo_codes',
                'on_field'      => 'promo_code_id',
                'show_table'    => 'promo_codes',
                'relation_type' => 'belongs_to',
                'value'         => '$record->code',
            ],
            [
                'on_table'      => 'offers_tags',
                'on_field'      => 'offer_id',
                'show_table'    => 'offers',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'offers_tags',
                'on_field'      => 'tag_id',
                'show_table'    => 'tags',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'orders',
                'on_field'      => 'offer_id',
                'show_table'    => 'offers',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'orders',
                'on_field'      => 'user_id',
                'show_table'    => 'users',
                'relation_type' => 'belongs_to',
                'value'         => '$record->email',
            ],
            [
                'on_table'      => 'orders',
                'on_field'      => 'promo_code_id',
                'show_table'    => 'promo_codes',
                'relation_type' => 'belongs_to',
                'value'         => '$record->code',
            ],
            [
                'on_table'      => 'orders_bills',
                'on_field'      => 'order_id',
                'show_table'    => 'orders',
                'relation_type' => 'belongs_to',
                'value'         => '$record->id . \' \' . $record->num',
            ],
            [
                'on_table'      => 'orders_tags',
                'on_field'      => 'order_id',
                'show_table'    => 'orders',
                'relation_type' => 'belongs_to',
                'value'         => '$record->id . \' \' . $record->num',
            ],
            [
                'on_table'      => 'orders_users',
                'on_field'      => 'order_id',
                'show_table'    => 'orders',
                'relation_type' => 'belongs_to',
                'value'         => '$record->id . \' \' . $record->num',
            ],
            [
                'on_table'      => 'orders_users',
                'on_field'      => 'city_id',
                'show_table'    => 'cities',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'orders_users',
                'on_field'      => 'user_id',
                'show_table'    => 'users',
                'relation_type' => 'belongs_to',
                'value'         => '$record->email',
            ],
            [
                'on_table'      => 'orders_users',
                'on_field'      => 'packet_id',
                'show_table'    => 'packets',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'orders_users_additions',
                'on_field'      => 'orders_user_id',
                'show_table'    => 'orders_users',
                'relation_type' => 'belongs_to',
                'value'         => '$record->id',
            ],
            [
                'on_table'      => 'orders_users_additions',
                'on_field'      => 'addition_id',
                'show_table'    => 'packets_additions',
                'relation_type' => 'belongs_to',
                'value'         => '$record->id',
            ],
            [
                'on_table'      => 'packets',
                'on_field'      => 'offer_id',
                'show_table'    => 'offers',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'packets_additions',
                'on_field'      => 'addition_id',
                'show_table'    => 'additions',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'packets_additions',
                'on_field'      => 'packet_id',
                'show_table'    => 'packets',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'packets_cities',
                'on_field'      => 'packet_id',
                'show_table'    => 'packets',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'packets_cities',
                'on_field'      => 'city_id',
                'show_table'    => 'cities',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'packets_includes',
                'on_field'      => 'packet_id',
                'show_table'    => 'packets',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'packets_includes',
                'on_field'      => 'addition_id',
                'show_table'    => 'packets_additions',
                'relation_type' => 'belongs_to',
                'value'         => '$record->id',
            ],
            [
                'on_table'      => 'packets_tabs',
                'on_field'      => 'packet_id',
                'show_table'    => 'packets',
                'relation_type' => 'belongs_to',
                'value'         => '$record->title',
            ],
            [
                'on_table'      => 'paypal',
                'on_field'      => 'order_id',
                'show_table'    => 'orders',
                'relation_type' => 'belongs_to',
                'value'         => '$record->id . \' \' . $record->num',
            ],
            [
                'on_table'      => 'paypal',
                'on_field'      => 'user_id',
                'show_table'    => 'users',
                'relation_type' => 'belongs_to',
                'value'         => '$record->email',
            ],
            // has many
            [
                'on_table'      => 'orders',
                'on_field'      => 'order_id',
                'show_table'    => 'braintree',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'users',
                'on_field'      => 'user_id',
                'show_table'    => 'braintree',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'countries',
                'on_field'      => 'country_id',
                'show_table'    => 'cities',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'galleries',
                'on_field'      => 'gallery_id',
                'show_table'    => 'galleries_pictures',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'tags',
                'on_field'      => 'tag_id',
                'show_table'    => 'galleries_tags',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'galleries',
                'on_field'      => 'gallery_id',
                'show_table'    => 'galleries_tags',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'users',
                'on_field'      => 'user_id',
                'show_table'    => 'logins',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'mails',
                'on_field'      => 'mail_id',
                'show_table'    => 'mails_sents',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'orders',
                'on_field'      => 'order_id',
                'show_table'    => 'moneta',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'users',
                'on_field'      => 'user_id',
                'show_table'    => 'moneta',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'news',
                'on_field'      => 'news_id',
                'show_table'    => 'news_tags',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'tags',
                'on_field'      => 'tag_id',
                'show_table'    => 'news_tags',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'cities',
                'on_field'      => 'city_id',
                'show_table'    => 'offers',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'categories',
                'on_field'      => 'category_id',
                'show_table'    => 'offers',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'offers',
                'on_field'      => 'offer_id',
                'show_table'    => 'offers_payment_methods',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'offers',
                'on_field'      => 'offer_id',
                'show_table'    => 'offers_pictures',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'offers',
                'on_field'      => 'offer_id',
                'show_table'    => 'offers_promo_codes',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'promo_codes',
                'on_field'      => 'promo_code_id',
                'show_table'    => 'offers_promo_codes',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'offers',
                'on_field'      => 'offer_id',
                'show_table'    => 'offers_tags',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'tags',
                'on_field'      => 'tag_id',
                'show_table'    => 'offers_tags',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'offers',
                'on_field'      => 'offer_id',
                'show_table'    => 'orders',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'users',
                'on_field'      => 'user_id',
                'show_table'    => 'orders',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'promo_codes',
                'on_field'      => 'promo_code_id',
                'show_table'    => 'orders',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'orders',
                'on_field'      => 'order_id',
                'show_table'    => 'orders_bills',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'orders',
                'on_field'      => 'order_id',
                'show_table'    => 'orders_tags',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'orders',
                'on_field'      => 'order_id',
                'show_table'    => 'orders_users',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'cities',
                'on_field'      => 'city_id',
                'show_table'    => 'orders_users',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'users',
                'on_field'      => 'user_id',
                'show_table'    => 'orders_users',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'packets',
                'on_field'      => 'packet_id',
                'show_table'    => 'orders_users',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'orders_users',
                'on_field'      => 'orders_user_id',
                'show_table'    => 'orders_users_additions',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'packets_additions',
                'on_field'      => 'addition_id',
                'show_table'    => 'orders_users_additions',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'offers',
                'on_field'      => 'offer_id',
                'show_table'    => 'packets',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'additions',
                'on_field'      => 'addition_id',
                'show_table'    => 'packets_additions',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'packets',
                'on_field'      => 'packet_id',
                'show_table'    => 'packets_additions',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'packets',
                'on_field'      => 'packet_id',
                'show_table'    => 'packets_cities',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'cities',
                'on_field'      => 'city_id',
                'show_table'    => 'packets_cities',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'packets',
                'on_field'      => 'packet_id',
                'show_table'    => 'packets_includes',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'packets_additions',
                'on_field'      => 'addition_id',
                'show_table'    => 'packets_includes',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'packets',
                'on_field'      => 'packet_id',
                'show_table'    => 'packets_tabs',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'orders',
                'on_field'      => 'order_id',
                'show_table'    => 'paypal',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
            [
                'on_table'      => 'users',
                'on_field'      => 'user_id',
                'show_table'    => 'paypal',
                'relation_type' => 'has_many',
                'value'         => '',
            ],
        ];

        $this->install($tables, $relations);
    }

    protected function install($dynamicTables = [], $relations = [])
    {

        $fieldTypes = (new FieldTypes())->all()->keyBy('slug');

        $tables = new Collection();
        foreach ($dynamicTables as $dynamicTable) {
            $record = (new Table())->setData(
                [
                    'table'      => $dynamicTable['table'],
                    'repository' => $dynamicTable['repository'] ?? null,
                ]
            );
            $record->save();
            $tables->push($record, $dynamicTable['table']);
        }

        $fields = new Collection();
        $tabs = new Collection();
        $actions = new Collection();
        foreach ($dynamicTables as $dynamicTable) {
            $table = $tables->getKey($dynamicTable['table']);
            foreach ($dynamicTable['_fields'] as $i => $field) {
                $field = (new Field())->setData(
                    [
                        'field'                 => $field['field'],
                        'dynamic_table_id'      => $table->id,
                        'dynamic_field_type_id' => $fieldTypes->getKey($field['type'])->id,
                        'order'                 => $i * 10,
                    ]
                );
                $field->save();
                $fields->push($field, $table->table . '.' . $field->field);
            }
            foreach ($dynamicTable['_tabs'] as $i => $tab) {
                $record = (new Tab())->setData(
                    [
                        'dynamic_table_id' => $table->id,
                        'order'            => $i * 10,
                    ]
                );
                $record->save();
                $tabs->push($record, $table->table . '.' . $i);
            }
            foreach ($dynamicTable['_actions'] as $i => $action) {
                $record = (new TableAction())->setData(
                    [
                        'dynamic_table_id' => $table->id,
                        'slug'             => $action['slug'],
                        'type'             => $action['type'],
                    ]
                );
                $record->save();
                $actions->push($record, $table->table . '.' . $action->slug);
            }
        }

        $tables = (new Tables())->all()->keyBy('table');
        $fields = (new Fields())->withTable()->all()->keyBy(
            function (Field $field) {
                return $field->table->table . '.' . $field->field;
            }
        );
        $relationTypes = (new RelationTypes())->all()->keyBy('slug');

        foreach ($relations as $relation) {
            $record = (new Relation())->setData(
                [
                    'on_table_id'              => $tables->getKey($relation['on_table'])->id,
                    'on_field_id'              => $fields->getKey(
                        ($relation['relation_type'] == 'belongs_to' ? $relation['on_table'] : $relation['show_table']) . '.' . $relation['on_field']
                    )->id,
                    'show_table_id'            => $tables->getKey($relation['show_table'])->id,
                    'dynamic_relation_type_id' => $relationTypes->getKey($relation['relation_type'])->id,
                    'value'                    => $relation['value'],
                ]
            )->save();
        }
    }
}
