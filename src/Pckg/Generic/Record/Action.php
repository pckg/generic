<?php

namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Generic\Entity\Actions;

/**
 * Class Action
 *
 * @package Pckg\Generic\Record
 */
class Action extends Record
{

    /**
     * @var
     */
    protected $entity = Actions::class;

    protected $toArray = ['pivot'];

}