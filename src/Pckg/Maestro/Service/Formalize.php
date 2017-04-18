<?php namespace Pckg\Maestro\Service;

use Pckg\Database\Record;
use Pckg\Htmlbuilder\Element\Form;

class Formalize
{

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var Record
     */
    protected $record;

    /**
     * @var string
     */
    protected $title;

    protected $table;

    /**
     * Formalize constructor.
     *
     * @param Form   $form
     * @param Record $record
     */
    public function __construct(Form $form, Record $record)
    {
        $this->form = $form;
        $this->record = $record;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function __toString()
    {
        return (string)view(
            'Pckg/Maestro:formalize',
            [
                'record'    => $this->record,
                'form'      => $this->form,
                'formalize' => $this,
            ]
        );
    }

    public function getEditUrl()
    {
        return url('dynamic.record.edit', [
            'table'  => $this->table,
            'record' => $this->record,
        ]);
    }

}