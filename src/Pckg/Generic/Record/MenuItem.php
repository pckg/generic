<?php

namespace Pckg\Generic\Record;

use Pckg\Collection;
use Pckg\Database\Record;
use Pckg\Generic\Entity\MenuItems;

/**
 * Class MenuItem
 * @package Pckg\Generic\Record
 * @property string $url
 * @property Collection $getChildren
 * @property string $title
 * @property string $icon
 * @property string $parent_id
 */
class MenuItem extends Record
{
    protected $entity = MenuItems::class;
    public function getAdditionalClass()
    {
        if (strpos($this->url, '#') !== 0 && strpos($this->url, '/#') !== 0) {
            return;
        }

        return 'clicknscroll';
    }

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

        if (
            in_array(router()->get('name'), ['dynamic.record.edit', 'dynamic.record.view'])
            && $this->url == '/dynamic/tables/list/' . explode('/', router()->getCleanUri())[4]
        ) {
            return true;
        }

        return $this->isSubActive();
    }

    public function isSubActive()
    {
        return (new Collection($this->getChildren))->has(function (MenuItem $menuItem) {

                return $menuItem->isActive();
        });
    }
}
