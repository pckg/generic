<?php namespace Pckg\Generic\Form;

use Pckg\Htmlbuilder\Element\Form\Bootstrap;
use Pckg\Htmlbuilder\Element\Form\ResolvesOnRequest;
use Pckg\Htmlbuilder\Validator\Method\Custom;

class NewRoute extends Bootstrap implements ResolvesOnRequest
{

    public function initFields()
    {
        $this->addText('slug')->required()->addValidator((new Custom(function($value) {

        })));
        $this->addText('url')->required()->addValidator((new Custom(function($value) {

        })));
        $this->addText('title')->required();

        return $this;
    }

}