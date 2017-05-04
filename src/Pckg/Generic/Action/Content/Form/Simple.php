<?php namespace Pckg\Generic\Action\Content\Form;

use Pckg\Htmlbuilder\Element\Form\Bootstrap;
use Pckg\Htmlbuilder\Element\Form\ResolvesOnRequest;

class Simple extends Bootstrap implements ResolvesOnRequest
{

    public function initFields()
    {
        $this->addCheckbox('picture')
             ->setLabel('Display picture');

        $this->addSelect('picturePosition')
             ->setLabel('Picture position')
             ->addOptions([
                              'left'       => 'Left',
                              'right'      => 'Right',
                              'top'        => 'Top',
                              'bottom'     => 'Bottom',
                              'background' => 'Background',
                          ]);

        $this->addSelect('pictureWidth')
             ->setLabel('Picture width')
             ->addOptions([]);

        $this->addSelect('pictureOffset')
             ->setLabel('Picture offset')
             ->addOptions([]);

        $this->addCheckbox('content')
             ->setLabel('Display content');

        $this->addSelect('contentWidth')
             ->setLabel('Content width')
             ->addOptions([]);

        $this->addSelect('contentOffset')
             ->setLabel('Content offset')
             ->addOptions([]);

        $this->addCheckbox('noPadding')
             ->setLabel('No padding');

        $this->addSelect('heading')
             ->setLabel('Heading')
             ->addOptions(['h1', 'h1', 'h3', 'h4', 'h5', 'h6']);

        $this->addSelect('variation')
             ->setLabel('Variation')
             ->addOptions(['default']);

        return $this;
    }

}