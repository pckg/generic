<?php namespace Pckg\Dynamic\Record\Snippet;

use Pckg\Database\Entity;
use Pckg\Database\Record\Extension\Deletable;
use Pckg\Database\Repository;

trait RecordActions
{

    use Deletable;

    public static $dynamicTable;

    public function getViewUrl()
    {
        return url(
            'dynamic.record.view',
            [
                'table'  => static::$dynamicTable,
                'record' => $this,
            ]
        );
    }

    public function getEditUrl()
    {
        return url(
            'dynamic.record.edit',
            [
                'table'  => static::$dynamicTable,
                'record' => $this,
            ]
        );
    }

    public function getCloneUrl()
    {
        return url(
            'dynamic.record.clone',
            [
                'table'  => static::$dynamicTable,
                'record' => $this,
            ]
        );
    }

    public function getDeleteUrl()
    {
        return url(
            'dynamic.record.delete',
            [
                'table'  => static::$dynamicTable,
                'record' => $this,
            ]
        );
    }

    public function getDeleteTranslationUrlAttribute()
    {
        return url(
            'dynamic.record.deleteTranslation',
            [
                'table'    => static::$dynamicTable,
                'record'   => $this,
                'language' => session()->pckg_dynamic_lang_id,
            ]
        );
    }

    public function getForceDeleteUrl()
    {
        return url(
            'dynamic.record.forceDelete',
            [
                'table'  => static::$dynamicTable,
                'record' => $this,
            ]
        );
    }

    public function getListUrl()
    {
        return url(
            'dynamic.record.list',
            [
                'table' => $this,
            ]
        );
    }

    public function deleteTranslation($language, Entity $entity = null, Repository $repository = null)
    {
        if (!$entity) {
            $entity = $this->getEntity();
        }

        if (!$repository) {
            $repository = $entity->getRepository();
        }

        $deleted = $repository->deleteTranslation($this, $entity, $language);

        return $deleted;
    }

    public function isTranslatable()
    {
        $entity = $this->getEntity();

        return $entity->getRepository()->getCache()->hasTable($entity->getTable() . '_i18n');
    }

}