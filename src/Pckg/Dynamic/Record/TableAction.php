<?php

namespace Pckg\Dynamic\Record;

use Pckg\Database\Helper\Convention;
use Pckg\Database\Record as DatabaseRecord;
use Pckg\Dynamic\Entity\TableActions;

/**
 * Class TableAction
 * @package Pckg\Dynamic\Record
 * @property string $template
 * @property string $slug
 */
class TableAction extends DatabaseRecord
{

    protected $entity = TableActions::class;

    public function getEntityTemplateAttribute()
    {
        if (!$this->template) {
            return $this->slug;
        }

        if (!strpos($this->template, '@')) {
            return $this->template;
        }

        if (strpos($this->template, ':')) {
            return substr($this->template, strpos($this->template, ':') + 1);
        }

        return substr($this->template, strpos($this->template, '@') + 1);
    }

    public function getVueComponentAttribute()
    {
        if (!$this->template) {
            return;
        }

        $template = $this->template;
        if ($pos = strpos($template, ':')) {
            $template = substr($template, $pos + 1);
        }

        $expl = explode('/', $template);
        $expl[0] = str_replace('_', '-', Convention::fromCamel($expl[0]));
        $expl[1] = str_replace('_', '-', Convention::fromCamel($expl[1]));

        return 'derive-' . $expl[0] . '-tabelize-' . $expl[1];
    }
}
