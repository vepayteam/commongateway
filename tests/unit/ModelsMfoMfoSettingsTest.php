<?php

use app\models\mfo\MfoSettings;

class ModelsMfoMfoSettingsTest extends \Codeception\Test\Unit
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

    public function testSave()
    {
        $mfoSettings = new MfoSettings();
        $this->tester->assertEquals(1, $mfoSettings->Save());
    }
}
