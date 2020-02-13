<?php namespace Pckg\Generic\Migration;

use Pckg\Migration\Constraint\Constraint;
use Pckg\Migration\Constraint\ForeignKey;
use Pckg\Migration\Constraint\Index;
use Pckg\Migration\Constraint\Unique;
use Pckg\Migration\Migration;

class MigrateUniqueRoutesIndex extends Migration
{

    public function up()
    {
        try {
            d('dropping slug index on routes');
            $unique = new Unique($this->table('routes'), 'slug');
            $unique->drop($this);
        } catch (\Throwable $e) {
            $this->output(exception($e));
        }

        try {
            d('dropping constraint on settings');
            $index = new ForeignKey($this->table('settings'), 'type');
            $index->drop($this);
        } catch (\Throwable $e) {
            $this->output(exception($e));
        }

        try {
            d('dropping index on settings');
            $index = new Index($this->table('settings'), 'type');
            $index->setName('type')->drop($this);
        } catch (\Throwable $e) {
            $this->output(exception($e));
        }
    }

}