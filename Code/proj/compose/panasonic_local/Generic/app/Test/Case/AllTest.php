<?php
class AllTest extends PHPUnit_Framework_TestSuite {

    public static function suite() {

        $suite = new CakeTestSuite('All tests');

        $path = APP . DS . 'Plugin'. DS . 'App1'. DS. 'Test' . DS . 'Case' . DS;
        $suite->addTestDirectory($path . 'Controller' . DS);
        $path = APP . DS . 'Plugin'. DS . 'App2'. DS. 'Test' . DS . 'Case' . DS;
        $suite->addTestDirectory($path . 'Controller' . DS);

        return $suite;

    }

}

