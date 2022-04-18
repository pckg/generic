<?php

namespace Pckg\Maestro\Helper;

use Pckg\Database\Entity;
use Pckg\Database\Record;
use Pckg\Htmlbuilder\Element\Form;
use Pckg\Maestro\Service\Formalize;
use Pckg\Maestro\Service\Tabelize;

trait Maestro
{
    /**
     * @param Entity $entity
     * @param array  $fields
     * @param        $title
     *
     * @return Tabelize
     */
    protected function tabelize(Entity $entity = null, $fields = [], $title = null)
    {
        return (new Tabelize($entity, $fields))->setTitle($title);
    }

    /**
     * @param Form   $form
     * @param Record $record
     * @param        $title
     *
     * @return Formalize
     */
    protected function formalize(Form $form, Record $record, $title)
    {
        return (new Formalize($form, $record))->setTitle($title);
    }
}
