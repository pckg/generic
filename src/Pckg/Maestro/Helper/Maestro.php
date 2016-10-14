<?php namespace Pckg\Maestro\Helper;

use Pckg\Database\Entity;
use Pckg\Database\Record;
use Pckg\Htmlbuilder\Element\Form;
use Pckg\Maestro\Service\Formalize;
use Pckg\Maestro\Service\Tabelize;

trait Maestro
{

    protected function initMaestroVue()
    {
        $this->vueManager()->addComponent(
            [
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-dynamic-paginator',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-maestro-table',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-bootstrap-alert',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-bootstrap-modal',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-tabelize-field-datetime',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-tabelize-field-boolean',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-tabelize-field-editor',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-htmlbuilder-dropzone',
                'vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-htmlbuilder-select',
            ]
        );

        $this->assetManager()->addAssets(
            [
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-dynamic-paginator.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-maestro-table.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-bootstrap-alert.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-bootstrap-modal.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-tabelize-field-datetime.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-tabelize-field-boolean.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-tabelize-field-editor.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-htmlbuilder-dropzone.js',
                'vendor/pckg/generic/src/Pckg/Maestro/public/js/pckg-htmlbuilder-select.js',
            ],
            'vue'
        );
    }

    /**
     * @param Entity $entity
     * @param array  $fields
     * @param        $title
     *
     * @return Tabelize
     */
    protected function tabelize(Entity $entity = null, $fields = [], $title = null)
    {
        $this->initMaestroVue();

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
        $this->initMaestroVue();

        return (new Formalize($form, $record))->setTitle($title);
    }

}