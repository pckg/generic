<?php

namespace Pckg\Dynamic\Form;

use Pckg\Htmlbuilder\Element\Form\Bootstrap;
use Pckg\Htmlbuilder\Element\Form\ResolvesOnRequest;

class Import extends Bootstrap implements ResolvesOnRequest
{

    public function initFields()
    {
        $this->addFile('file')->setLabel('File (.csv)');

        $this->addSubmit('submit')->setValue('Submit');

        return $this;
    }
}
