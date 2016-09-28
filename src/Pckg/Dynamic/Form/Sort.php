<?php namespace Pckg\Dynamic\Form;

use Pckg\Dynamic\Record\Table;
use Pckg\Htmlbuilder\Element\Form\Bootstrap;

class Sort extends Bootstrap
{

    /**
     * var Table
     */
    protected $table;

    public function setTable(Table $table)
    {
        $this->table = $table;

        return $this;
    }

    public function initFields()
    {
        $fields = $this->table->fields;

        foreach ($fields as $field) {
            $fieldset = $this->addFieldset('field');
            $fieldset->addSelect('order')
                     ->setLabel($field->field)
                     ->addOptions(
                         [
                             'asc'  => 'Ascending',
                             'desc' => 'Descending',
                         ]
                     );

            $actions = $this->addFieldset('actions');
            $actions->addButton('up')->setValue('Up');
            $actions->addButton('down')->setValue('Down');
            $actions->addButton('add')->setValue('Add');
            $actions->addButton('remove')->setValue('Remove');
        }

        $this->addSubmit();

        return $this;
    }

}