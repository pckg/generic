<?php namespace Pckg\Dynamic\Resolver;

use Pckg\Dynamic\Record\Field as FieldRecord;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Framework\Provider\RouteResolver;

class Record implements RouteResolver
{

    /**
     * @var Dynamic
     */
    protected $dynamic;

    public function __construct(
        Dynamic $dynamic
    ) {
        $this->dynamic = $dynamic;
    }

    public function resolve($value)
    {
        $table = router()->resolved('table');

        $tablesEntity = $table->createEntity();

        //$tablesEntity = new Tables();
        //$tablesEntity->setTable($table->table);
        //$tablesEntity->setRecordClass(DatabaseRecord::class);

        //if ($table->repository) {
          //  $tablesEntity->setRepository($table->getRepository());
        //}

        //$this->dynamic->joinTranslationsIfTranslatable($tablesEntity);
        //$this->dynamic->joinPermissionsIfPermissionable($tablesEntity);

        $listableFields = $table->listableFields;
        $listableFields->each(
            function(FieldRecord $field) use ($tablesEntity) {
                $field->selectMultiField($tablesEntity);
            }
        );

        return $tablesEntity->where('id', $value)
                            ->oneOrFail(
                                function() {
                                    response()->unauthorized('Record not found');
                                }
                            );
    }

    public function parametrize($record)
    {
        return $record->id;
    }

}
