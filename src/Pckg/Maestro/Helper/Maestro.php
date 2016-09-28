<?php namespace Pckg\Maestro\Helper;

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
        $this->vueManager()->addVueComponent(
            [
                'vendor/pckg/maestro/src/Pckg/Maestro/public/vue/pckg-dynamic-paginator',
                'vendor/pckg/maestro/src/Pckg/Maestro/public/vue/pckg-maestro-table',
                'vendor/pckg/maestro/src/Pckg/Maestro/public/vue/pckg-bootstrap-alert',
                'vendor/pckg/maestro/src/Pckg/Maestro/public/vue/pckg-bootstrap-modal',
            ]
        );

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
        $this->vueManager()->addVueComponent(
            [
                'vendor/pckg/maestro/src/Pckg/Maestro/public/vue/pckg-dynamic-paginator',
                'vendor/pckg/maestro/src/Pckg/Maestro/public/vue/pckg-maestro-table',
                'vendor/pckg/maestro/src/Pckg/Maestro/public/vue/pckg-bootstrap-alert',
                'vendor/pckg/maestro/src/Pckg/Maestro/public/vue/pckg-bootstrap-modal',
            ]
        );

        return (new Formalize($form, $record))->setTitle($title);
    }

}