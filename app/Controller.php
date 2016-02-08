<?php

class Controller
{
    protected $template;
    protected $templateKey = 'content';

    public function beforeroute($app, $params)
    {
        $this->template = $this->template?:$app->get('app.template');
    }

    /**
     * Render template view
     * @param  string $view
     * @return null
     */
    public function render($view)
    {
        $app = Base::instance();
        if ($this->template) {
            $app->set($this->templateKey, $view);
            echo Template::instance()->render($this->template);
        } else {
            echo Template::instance()->render($view);
        }
    }
}