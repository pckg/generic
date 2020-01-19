<?php namespace Pckg\Generic\Form;

use Pckg\Generic\Entity\Routes;
use Pckg\Htmlbuilder\Element\Form\Bootstrap;
use Pckg\Htmlbuilder\Element\Form\ResolvesOnRequest;
use Pckg\Htmlbuilder\Validator\Method\Custom;

class NewRoute extends Bootstrap implements ResolvesOnRequest
{

    public function initFields()
    {
        $text = $this->addText('slug')->required()->addValidator((new Custom(function($value, Custom $validator) {
            $validator->setMsg('Enter unique identifier, slug already exists');

            return !((new Routes())->where('slug', $value)->one());
        })))->addValidator((new Custom(function($value, Custom $validator) {
            $validator->setMsg('Slug should contain only lower case alphanumeric characters, minus and dot');

            return $value == sluggify($value, '-', '\.');
        })));

        $this->addText('route')->required()->addValidator((new Custom(function($value, Custom $validator) {
            $validator->setMsg('Enter unique route, URL already exists');

            return !((new Routes())->joinTranslations()->where('route', $value)->one());
        })))/*->addValidator((new Custom(function($value, Custom $validator) {
            $validator->setMsg('Enter valid url');

            return $value == sluggify($value, '-', '\/\[\]\.'); // no, what about dynamic urls?
        })))*/->addValidator((new Custom(function($value, Custom $validator) {
            $validator->setMsg('Url should start with /');

            return substr($value, 0, 1) == '/';
        })))->addValidator((new Custom(function($value, Custom $validator) {
            $validator->setMsg('Url should not end with /');

            return $value == '/' || substr(strrev($value), 0, 1) != '/';
        })))->addValidator((new Custom(function($value, Custom $validator) {
            $validator->setMsg('Check dynamic parameters');

            return count(explode('[', $value)) == count(explode(']', $value)) &&
                count(explode('/[', $value)) == count(explode(']', $value));
        })));

        $this->addText('title')->required();

        return $this;
    }

}