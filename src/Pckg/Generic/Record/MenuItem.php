<?php namespace Pckg\Generic\Record;

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
        $currentUrl = router()->getURL();

        if ($currentUrl == $this->url) {
            return true;
        }

        $childActive = false;
        $this->menuItems->each(
            function(MenuItem $menuItem) use (&$childActive) {
                $childActive = $childActive || $menuItem->isActive();
            }
        );

        return $childActive;
    }

}