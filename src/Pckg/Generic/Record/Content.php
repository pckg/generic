<?php

namespace Pckg\Generic\Record;

use Pckg\Database\Record;
use Pckg\Generic\Entity\Contents;

/**
 * Class Content
 *
 * @package Pckg\Generic\Record
 * @property string $content
 * @property string $picture
 */
class Content extends Record
{

    /**
     * @var
     */
    protected $entity = Contents::class;

    protected $preparsedData = [];

    protected $toArray = ['+contents', 'image'];

    public function getImageAttribute()
    {
        if (!$this->picture) {
            return null;
        }

        return img($this->picture, 'contents');
    }

    public function getContentAttribute()
    {
        $content = $this->data('content');

        if (!$this->preparsedData) {
            return $content;
        }

        return view()->setTemplate(str_replace(['‘', '’'], '\'', $content))->addData($this->preparsedData)->autoparse();
    }

    public function getOriginalContentAttribute()
    {
        return $this->data('content');
    }

    public function addPreparsedData($data)
    {
        $this->preparsedData = $data;

        return $this;
    }

    public function jsonSerialize()
    {
        $data = $this->toArray();

        /**
         * Allow preparsed content.
         */
        $data['content'] = $this->content;

        return $data;
    }
}
