<?php

namespace application\controllers;

use application\models\Foo;
use Monstra\View;

class Welcome extends \Monstra\Controller
{
    public function before()
    {
        // Do something...
    }

    public function actionIndex()
    {
        return View::factory('welcome');
    }

    public function actionTest()
    {
        $foo = new Foo();
        return $foo->foo();
    }

    public function after()
    {
        // Do something...
    }

}
