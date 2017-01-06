<?php namespace Pckg\Dynamic\Resolver;

use Impero\Apache\Record\Site;
use Pckg\Concept\Reflect;
use Pckg\Database\Record;
use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Entity\Tables;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Framework\Inter\Entity\Languages;
use Pckg\Framework\Provider\RouteResolver;
use Pckg\Framework\Response;
use Pckg\Framework\Router;

class Table implements RouteResolver
{

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
        $table = runInLocale(
            function() use ($dynamic, $value) {
                $tables = new Tables();
                $dynamic->joinTranslationsIfTranslatable($tables);
                $dynamic->joinPermissionsIfPermissionable($tables);

                return $tables->where('id', $value)
                              ->withFields(
                                  function(HasMany $fields) {
                                      $fields->withFieldType();
                                      $fields->withSettings();
                                  }
                              )
                              ->withTabs(
                                  function(HasMany $tabs) {
                                      $tabs->joinTranslation();
                                      $tabs->joinFallbackTranslation();
                                  }
                              )
                              ->oneOrFail(
                                  function() {
                                      response()->unauthorized('Table not found');
                                  }
                              );
            },
            'en_GB'
        );

        return $table;
    }

    public function parametrize($record)
    {
        return $record->id ?? $record;
    }

}
