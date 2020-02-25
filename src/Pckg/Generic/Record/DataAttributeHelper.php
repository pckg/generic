<?php namespace Pckg\Generic\Record;

trait DataAttributeHelper
{

    public function getDataAttribute($slug, $default = null)
    {
        $dataAttribute = $this->dataAttributes->first(function (DataAttribute $dataAttribute) use ($slug) {
            return $dataAttribute->slug == $slug;
        });

        if (!$dataAttribute) {
            return $default;
        }

        return $dataAttribute->value;
    }

}