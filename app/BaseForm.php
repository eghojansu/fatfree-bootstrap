<?php

namespace app;

use Nutrition\HTML\Form;

class BaseForm extends Form
{
    protected function onOpen()
    {
        return $this->hidden('submitted', ['value'=>'1'], true);
    }

    public function error($field)
    {
        $value = $this->validation->getError($field);

        return $value ? $this->element('p', null, ['class'=>'help-block form-error', 'value'=>$value]) : '';
    }

    protected function assignLabels()
    {
        $labels = $this->mapper->getLabels();
        $this->setLabels($labels);
        $this->validation->setLabels($labels);

        return $this;
    }
}
