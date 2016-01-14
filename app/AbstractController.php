<?php

/**
 * Abstract Controller
 *
 * @author Eko Kurniawan <fkurniawan@outlook.com>
 */
abstract class AbstractController
{
    /**
     * Main template
     * @var string
     */
    protected $template = 'template.main.htm';
    /**
     * Current user access
     * @var string
     */
    protected $access;
    /**
     * Current user data
     * @var array
     */
    protected $user;

    /**
     * Constructor
     * @param Base $app
     * @param array $params
     */
    public function __construct($app,$params)
    {
        $this->user = [
            'id'=>$app->get('SESSION.user.login'),
            'type'=>$app->get('SESSION.user.type'),
            'info'=>$app->get('SESSION.user.info'),
        ];
    }

    /**
     * Save user data to session
     * @param  string $type access type
     * @param  string $id
     * @param  array  $data
     * @return bool
     */
    protected function getIn($type, $id, array $data)
    {
        $data = [
            'login'=>$id,
            'type'=>$type,
            'info'=>$data,
        ];
        Base::instance()->set('SESSION.user', $data);
        $this->user += $data + ['id'=>$id];

        return true;
    }

    /**
     * Check current user is guest
     * @return boolean
     */
    protected function isGuest()
    {
        return empty($this->user['id']);
    }

    /**
     * Check current user is authenticated
     * @return boolean
     */
    protected function isAuthenticated()
    {
        return !$this->isGuest();
    }

    /**
     * Check current user has right access
     * @return boolean
     */
    protected function hasAccess()
    {
        return $this->access && $this->isAuthenticated() && $this->access === $this->user['type'];
    }

    /**
     * Check user access and redirect to a route or domain
     * if user has no access
     * @param  string $redirect
     * @return null
     */
    protected function accessOnly($redirect = '@login')
    {
        $this->hasAccess() || Base::instance()->reroute($redirect);
    }

    /**
     * Check current user is authenticated and redirect to route or domain
     * if user has no access
     * @param  string $redirect
     * @return null
     */
    protected function authenticatedOnly($redirect = '@login')
    {
        $this->isAuthenticated() || Base::instance()->reroute($redirect);
    }

    /**
     * Check current user is guest and redirect to route or domain
     * if user has no access
     * @param  string $redirect
     * @return null
     */
    protected function guestOnly($redirect = '@logout')
    {
        $this->isGuest() || Base::instance()->reroute($redirect);
    }

    /**
     * Is a post request ?
     * @return boolean
     */
    protected function isPost()
    {
        return 'POST'===Base::instance()->get('VERB');
    }

    /**
     * Set real view that must be set as content
     * @param string $view filename
     */
    protected function setContent($view)
    {
        Base::instance()->set('CONTENT', Template::instance()->render($view));
    }

    /**
     * Handling before route
     * @param  Base $app
     * @param  array $params
     * @return null
     */
    public function beforeroute($app,$params)
    {
    }

    /**
     * Handling after route
     * @param  Base $app
     * @param  array $params
     * @return null
     */
    public function afterRoute($app,$params)
    {
        echo Template::instance()->render($this->template);
    }
}