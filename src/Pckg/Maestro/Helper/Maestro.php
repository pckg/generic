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
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-dynamic-paginator',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-maestro-table',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-bootstrap-alert',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-bootstrap-modal',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-tabelize-field-datetime',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-tabelize-field-boolean',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-tabelize-field-editor',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-htmlbuilder-dropzone',
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
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-dynamic-paginator',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-maestro-table',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-bootstrap-alert',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-bootstrap-modal',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-tabelize-field-datetime',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-tabelize-field-boolean',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-tabelize-field-editor',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-htmlbuilder-dropzone',
            ]
        );

        return (new Formalize($form, $record))->setTitle($title);
    }

}