<?php

use app\services\ident\forms\RunaIdentInitForm;
use app\services\ident\models\IdentRuna;

class IdentServiceRunaTest extends \Codeception\Test\Unit
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

    // tests
    public function testInitForm()
    {
        $data = json_decode(file_get_contents(__DIR__  . '/../_data/unit/IdentServiceRunaTest.json'), true);

        $dataPrefixes = [
            'base_inn',
            'base_snils',
            'base_inn_and_snils',
        ];

        foreach ($dataPrefixes as $dataPrefix) {
            $initForm = new RunaIdentInitForm();
            $initForm->load($data[$dataPrefix], '');
            $this->assertTrue($initForm->validate());
        }

        $dataPrefixes = [
            'error_passport_series_length' => 'passport_series',
            'error_passport_number_length' => 'passport_number',
            'error_inn_length' => 'inn',
            'error_inn_regex' => 'inn',
            'error_snils_length' => 'snils',
            'error_empty_name' => 'name',
            'error_empty_surname' => 'surname',
            'error_empty_patronymic' => 'patronymic',
            'error_empty_inn_and_snils' => 'inn',
        ];

        foreach ($dataPrefixes as $dataPrefix => $field) {
            $initForm = new RunaIdentInitForm();
            $initForm->load($data[$dataPrefix], '');
            $this->assertFalse($initForm->validate());
            $this->assertTrue(count($initForm->getErrors($field)) > 0);
        }
    }
}
