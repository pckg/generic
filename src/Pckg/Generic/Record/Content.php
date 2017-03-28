<?php

namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Generic\Entity\Contents;

/**
 * Class Content
 *
 * @package Pckg\Generic\Record
 */
class Content extends Record
{

    /**
     * @var
     */
    protected $entity = Contents::class;

    public function getImageAttribute()
    {
        if (!$this->picture) {
            return null;
        }

        return img($this->picture, 'contents');
    }

}