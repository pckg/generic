<?php namespace Pckg\Generic\Resolver;

use Pckg\Framework\Provider\RouteResolver;
use Pckg\Generic\Entity\Routes;

class Route implements RouteResolver
{

    protected $field = 'slug';

    protected $routeName = 'name';

    public function resolve($value)
    {
        $routeName = router()->get($this->routeName);

        if ($this->routeName == 'name' && strpos($routeName, ':')) {
            $routeName = substr($routeName, 0, strpos($routeName, ':'));
        }

        return (new Routes())->where($this->field, $routeName)
                             ->joinTranslation()
                             ->withLayout()
                             ->withSettings()
                             ->oneOrFail(
                                 function() use ($value) {
                                     response()->notFound('Route ' . $value . ' not found in generic routes');
                                 }
                             );
    }

    public function by($field = 'slug', $routeName = 'name')
    {
        $this->field = $field;
        $this->routeName = $routeName;

        return $this;
    }

    public function parametrize($record)
    {
        return $record->id;
    }

}