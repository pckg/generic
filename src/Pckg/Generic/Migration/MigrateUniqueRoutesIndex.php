<?php namespace Pckg\Generic\Migration;

use Pckg\Migration\Constraint\Index;
use Pckg\Migration\Constraint\Unique;
use Pckg\Migration\Migration;

class MigrateUniqueRoutesIndex extends Migration
{

    public function up()
    {
        try {
            $routes = $this->table('routes');
            $unique = new Unique($routes, 'slug');
            $unique->drop($this);
        } catch (\Throwable $e) {
            $this->output(exception($e));
        }
    }

}