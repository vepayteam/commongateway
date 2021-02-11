<?php

use app\commands\WidgetController;

class CommandsWidgetControllerTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testRsbcron()
    {
//        $consoleController = new WidgetController('unit', Yii::$app);
//        $consoleController->runAction('rsbcron');
    }

    public function testQueue()
    {
//        $consoleController = new WidgetController('unit', Yii::$app);
//        $consoleController->runAction('queue');
    }
}