<?php

namespace Pckg\Dynamic\Resolver;

use Impero\Apache\Record\Site;
use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Framework\Provider\Helper\PostValidationResolver;
use Pckg\Framework\Provider\RouteResolver;

class Table implements RouteResolver
{

    use PostValidationResolver;

    /**
     * @var Dynamic
     */
    protected $dynamic;
    public function __construct(Dynamic $dynamic)
    {
        $this->dynamic = $dynamic;
    }

    public function resolve($value)
    {
        $dynamic = $this->dynamic;
        $table = runInLocale(function () use ($dynamic, $value) {

                $tables = new Tables();
                $dynamic->joinTranslationsIfTranslatable($tables);
                $dynamic->joinPermissionsIfPermissionable($tables);
                return $tables->where('id', $value)
                              ->withRelations(function (HasMany $relations) {

                                      $relations->joinTranslations();
                                      $relations->joinFallbackTranslation();
                              })
                              ->withFields()
                              ->withTabs(function (HasMany $tabs) {

                                      $tabs->joinTranslation();
                                      $tabs->joinFallbackTranslation();
                              })
                              ->withActions()
                              ->withListableFields()
                              ->oneOrFail(function () {
                                        response()->unauthorized('Table not found');
                              });
        }, 'en_GB');

        $this->validate($table);

        return $table;
    }

    public function parametrize($record)
    {
        return $record->id ?? $record;
    }
}
