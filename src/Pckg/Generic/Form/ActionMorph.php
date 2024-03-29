<?php

namespace Pckg\Generic\Form;

use CommsCenter\Pagebuilder\Entity\Actions;
use CommsCenter\Pagebuilder\Entity\Contents;
use CommsCenter\Pagebuilder\Record\Action;
use CommsCenter\Pagebuilder\Record\Content;
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
                    function (Action $action) {
                        return $action->method . ' @ ' . $action->class;
                    }
                )
            );

        $this->addSelect('content_id')
            ->setAttribute('v-model', 'form.content_id')
            ->setLabel('Content')
            ->addOptions((new Contents())->all()->keyBy('id')->map(function (Content $content) {
                return '#' . $content->id . ' - ' . $content->title;
            }));

        $this->addText('template')
            ->setAttribute('v-model', 'form.template')
            ->setLabel('Custom template');

        $submit = $this->addSubmit();
        $submit->setAttribute('@click.prevent', 'onSubmit');
        $this->setAttribute('@submit.prevent', 'onSubmit');

        return $this;
    }
}
