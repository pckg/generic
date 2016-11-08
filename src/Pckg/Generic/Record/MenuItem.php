<?php namespace Pckg\Generic\Record;

use Pckg\Collection;
use Pckg\Database\Record;
use Pckg\Generic\Entity\MenuItems;

class MenuItem extends Record
{

    protected $entity = MenuItems::class;

    public function getRealUrl()
    {
        if (strpos($this->url, '://')) {
            return $this->url;
        }

        return (dev() ? '/dev.php' : '') . $this->url;
    }

    public function isActive()
    {
        if (router()->getCleanUri() == $this->url) {
            return true;
        }

        return (new Collection($this->getChildren))->has(
            function(MenuItem $menuItem) {
                return $menuItem->isActive();
            }
        );
    }

}