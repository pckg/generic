<?php namespace Pckg\Dynamic\Resolver;

use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Record\Field as FieldRecord;
use Pckg\Dynamic\Record\Record as DatabaseRecord;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Framework\Provider\RouteResolver;

class ForeignRecord implements RouteResolver
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
        $resolvedTable = router()->resolved('table');
        $resolvedRelation = router()->resolved('relation');
        $showTable = $resolvedRelation->showTable;
        $onTable = $resolvedRelation->onTable;

        $tablesEntity = new Tables();
        $tablesEntity->setTable($onTable->table);
        $tablesEntity->setRecordClass(DatabaseRecord::class);

        if ($onTable->repository) {
            $tablesEntity->setRepository($onTable->getRepository());
        }

        $this->dynamic->joinTranslationsIfTranslatable($tablesEntity);
        $this->dynamic->joinPermissionsIfPermissionable($tablesEntity);

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
