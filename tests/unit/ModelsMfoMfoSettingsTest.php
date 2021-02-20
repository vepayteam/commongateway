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

    public function testReadUrl()
    {
        $mfoSettings = new MfoSettings();
        $mfoSettings->IdPartner = 117;
        $mfoSettings->ReadUrl();
        $this->tester->assertEquals('http://processing.vepay.ru/c1.php', $mfoSettings->url);
    }

    public function testSave()
    {
        $mfoSettings = new MfoSettings();
        $this->tester->assertEquals(1, $mfoSettings->Save());
    }
}