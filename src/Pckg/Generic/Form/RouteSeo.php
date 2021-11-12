<?php

namespace Pckg\Generic\Form;

use CommsCenter\Pagebuilder\Entity\Routes;
use Pckg\Htmlbuilder\Element\Form\Bootstrap;
use Pckg\Htmlbuilder\Element\Form\ResolvesOnRequest;
use Pckg\Htmlbuilder\Validator\Method\Custom;

class RouteSeo extends Bootstrap implements ResolvesOnRequest
{

    public function initFields()
    {
        $title = $this->addText('seo[title]')->addCustomValidator(function ($value, Custom $validator) {
            $validator->setMsg('Title should be max 68 characters long');

            return strlen($value) <= 68;
        });

        $description = $this->addText('seo[description]')->addCustomValidator(function ($value, Custom $validator) {
            $validator->setMsg('Description should be max 130 characters long');

            return strlen($value) <= 130;
        });

        $keywords = $this->addText('seo[keywords]')->addCustomValidator(function ($value, Custom $validator) {
            $validator->setMsg('Keywords should be max 250 characters long');

            return strlen($value) <= 250;
        });

        $image = $this->addText('seo[image]');

        return $this;
    }
}
