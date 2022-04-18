<?php

namespace Pckg\Dynamic\Service\Export;

use Pckg\Database\Entity;

interface Strategy
{
    public function input(Entity $entity);
    public function output();
    public function prepare();
    public function getResponseType();
}
