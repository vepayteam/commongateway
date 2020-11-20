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
            $initForm->load($data['base_snils'], '');
            $this->assertFalse($initForm->validate());
            $this->assertTrue(count($initForm->getErrors($field)) > 0);
        }
    }

    public function testInit()
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

            /** @var \app\services\ident\IdentService $identService */
            $identService = Yii::$container->get('IdentService');

            /** @var \app\services\ident\responses\RunaIdentInitResponse $runaIdentInitResponse */
            $runaIdentInitResponse = $identService->runaInit($initForm);
            $identRunaMock = $this->make(IdentRuna::class, [
                'save' => \Codeception\Stub\Expected::once(),
            ]);

            $this->assertTrue(get_class($runaIdentInitResponse) == 'RunaIdentInitResponse');
            $this->assertTrue($runaIdentInitResponse->validate());
        }
    }

    public function testState()
    {
        $idents = IdentRuna::find()
            ->where(['Status' => 0])
            ->orderBy('Id DESC')
            ->limit(10)
            ->all();

        /** @var \app\services\ident\IdentService $identService */
        $identService = Yii::$container->get('IdentService');

        $identRuna = IdentRuna::find()->where(['Status' => 0])->orderBy('Id DESC')->one();
        $runaIdentStateResponse = $identService->getRunaState($identRuna);

        $this->assertTrue(get_class($runaIdentStateResponse) == 'RunaIdentStateResponse');
        $this->assertTrue(in_array($runaIdentStateResponse->details['code'], ['0', '1', '2']));
    }
}
