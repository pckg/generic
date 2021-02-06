<?php namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Framework\Request;
use Pckg\Framework\Service\Plugin;
use Pckg\Generic\Entity\Actions;

/**
 * Class Action
 *
 * @package Pckg\Generic\Record
 * @property string $class
 * @property string $method
 */
class Action extends Record
{

    /**
     * @var
     */
    protected $entity = Actions::class;

    protected $toArray = ['pivot'];

    public function build($args = [])
    {
        return measure('Making plugin ' . $this->class . ' @ ' . $this->method, function() use ($args) {
            $pluginService = new Plugin();

            return $pluginService->make($this->class, $this->method, $args, Request::GET, false);
        });
    }

}