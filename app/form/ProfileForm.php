<?php

namespace app\form;

use app\BaseForm;

class ProfileForm extends BaseForm
{
    protected function init()
    {
        parent::init();

        $this->validation
            ->addFilter('nama', 'required', true)
            ->addFilter('username', 'required', true)
            ->addFilter('old_password', 'required', true)
            ->addFilter('old_password', 'validatePassword', $this->get('old_password'))
            ->addFilter('new_password', 'minLength', [4, true])
            ;

        $this->assignLabels();

        return $this;
    }
}
