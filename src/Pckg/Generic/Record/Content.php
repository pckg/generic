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

        return view()->setTemplate($content)->addData($this->preparsedData)->autoparse();
    }

    public function addPreparsedData($data)
    {
        $this->preparsedData = $data;

        return $this;
    }

}