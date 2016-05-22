<?php namespace Pckg\Generic\Form;

use Pckg\Generic\Entity\Actions;
use Pckg\Generic\Entity\Contents;
use Pckg\Generic\Entity\Layouts;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Entity\Variables;
use Pckg\Htmlbuilder\Element\Form\Bootstrap;

class ActionMorph extends Bootstrap
{

    public function initFields()
    {
        $this->addSelect('action_id')
            ->setAttribute('v-model', 'form.action_id')
            ->setLabel('Action')
            ->addOptions((new Actions())->all()->getListID());

        $this->addSelect('morph_id')
            ->setAttribute('v-model', 'form.morph_id')
            ->setLabel('Morph')
            ->addOptions([
                Layouts::class => Layouts::class,
                Routes::class  => Routes::class,
            ]);

        $this->addSelect('poly_id')
            ->setAttribute('v-model', 'form.poly_id')
            ->setLabel('Poly');

        $this->addSelect('content_id')
            ->setAttribute('v-model', 'form.content_id')
            ->setLabel('Content')
            ->addOptions((new Contents())->all()->getListID());

        $this->addSelect('variable_id')
            ->setAttribute('v-model', 'form.variable_id')
            ->setLabel('Variable')
            ->addOptions((new Variables())->all()->getListID());

        $this->addInteger('order')
            ->setAttribute('v-model', 'form.order')
            ->setLabel('Order');

        $submit = $this->addSubmit();
        $submit->setAttribute('v-on:click', 'onSubmit');

        return $this;
    }

}