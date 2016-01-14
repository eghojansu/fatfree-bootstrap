<?php

namespace tests;

use App;
use Test as Tester;

class Test extends \AbstractController
{
    protected $template = 'tests/ui/layout.htm';

    public function get($app,$params)
    {
        $app->set('CONTENT', 'Welcome to test dashboard, pick your test in sidebar list');
    }

    public function beforeroute($app,$params)
    {
        $app->set('tests.active', ltrim($app->rel($app->get('URI')), '/'));
        $menu = $app->get('tests.menu');
        foreach (App::dirContent(__DIR__.'/unit') as $file) {
            $file = basename($file, '.php');
            $menu['test/'.strtolower($file)] = App::titleIze($file);
        }
        $app->set('tests.menu', $menu);
    }

    protected function newTest()
    {
        return new Tester;
    }

    protected function testResult(Tester $test)
    {
        $app = \Base::instance();
        $app->set('tests.results', $test->results());
        $app->set('tests.passed', $test->passed());
    }
}