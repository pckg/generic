<?php namespace Pckg\Generic\Form;

use Pckg\Generic\Entity\Actions;
use Pckg\Generic\Entity\Contents;
use Pckg\Generic\Record\Action;
use Pckg\Htmlbuilder\Element\Form\Bootstrap;

class ActionMorph extends Bootstrap
{

    public function initFields()
    {
        $this->addSelect('action_id')
             ->setAttribute('v-model', 'form.action_id')
             ->setLabel('Action')
             ->addOptions(
                 (new Actions())->all()->keyBy('id')->map(
                     function(Action $action) {
                         return $action->method . ' @ ' . $action->class;
                     }
                 )
             );

        $this->addSelect('content_id')
             ->setAttribute('v-model', 'form.content_id')
             ->setLabel('Content')
             ->addOptions((new Contents())->all()->getListID());

        $this->addText('template')
             ->setAttribute('v-model', 'form.template')
             ->setLabel('Custom template');

        $submit = $this->addSubmit();
        $submit->setAttribute('@click.prevent', 'onSubmit');
        $this->setAttribute('@submit.prevent', 'onSubmit');

        return $this;
    }

}