<?php

use app\models\bank\Banks;
use app\models\payonline\Uslugatovar;
use app\services\CompensationService;
use app\services\payment\models\Currency;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;

class CompensationServiceTest extends \Codeception\Test\Unit
{
    /* @var \UnitTester */
    protected $tester;

    /** @var CompensationService */
    private $compensationService;

    protected function _before()
    {
        $this->compensationService = new CompensationService();
    }

    protected function _after()
    {
    }

    // Start calculate for client use uslugatovar compensation
    public function testCalculateForClientNoGateZero()
    {
        $clientCommission = 0;
        $clientMinimalFee = 0;

        $uslugatovar = new Uslugatovar(['PcComission' => $clientCommission, 'MinsumComiss' => $clientMinimalFee]);
        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000, 'uslugatovar' => $uslugatovar]);
        $gate = $this->getGateCurrencyEqWithAttributes(['UseGateCompensation' => false]);

        $this->assertEquals(0.0, $this->compensationService->calculateForClient($paySchet, $gate));
    }

    public function testCalculateForClientNoGateCom2()
    {
        $clientCommission = 2;
        $clientMinimalFee = 0;

        $uslugatovar = new Uslugatovar(['PcComission' => $clientCommission, 'MinsumComiss' => $clientMinimalFee]);
        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000, 'uslugatovar' => $uslugatovar]);
        $gate = $this->getGateCurrencyEqWithAttributes(['UseGateCompensation' => false]);

        $this->assertEquals(200.0, $this->compensationService->calculateForClient($paySchet, $gate));
    }

    public function testCalculateForClientNoGateCom2Fee5()
    {
        $clientCommission = 2;
        $clientMinimalFee = 5;

        $uslugatovar = new Uslugatovar(['PcComission' => $clientCommission, 'MinsumComiss' => $clientMinimalFee]);
        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000, 'uslugatovar' => $uslugatovar]);
        $gate = $this->getGateCurrencyEqWithAttributes(['UseGateCompensation' => false]);

        $this->assertEquals(500.0, $this->compensationService->calculateForClient($paySchet, $gate));
    }
    // Ends calculate for client use uslugatovar compensation



    // Start calculate for partner use uslugatovar compensation
    public function testCalculateForPartnerNoGateZero()
    {
        $partnerCommission = 0;
        $partnerMinimalFee = 0;

        $uslugatovar = new Uslugatovar(['ProvVoznagPC' => $partnerCommission, 'ProvVoznagMin' => $partnerMinimalFee]);
        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000, 'uslugatovar' => $uslugatovar]);
        $gate = $this->getGateCurrencyEqWithAttributes(['UseGateCompensation' => false]);

        $this->assertEquals(0.0, $this->compensationService->calculateForPartner($paySchet, $gate));
    }

    public function testCalculateForPartnerNoGateCom2()
    {
        $partnerCommission = 2;
        $partnerMinimalFee = 0;

        $uslugatovar = new Uslugatovar(['ProvVoznagPC' => $partnerCommission, 'ProvVoznagMin' => $partnerMinimalFee]);
        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000, 'uslugatovar' => $uslugatovar]);
        $gate = $this->getGateCurrencyEqWithAttributes(['UseGateCompensation' => false]);

        $this->assertEquals(200.0, $this->compensationService->calculateForPartner($paySchet, $gate));
    }

    public function testCalculateForPartnerNoGateCom2Fee5()
    {
        $partnerCommission = 2;
        $partnerMinimalFee = 5;

        $uslugatovar = new Uslugatovar(['ProvVoznagPC' => $partnerCommission, 'ProvVoznagMin' => $partnerMinimalFee]);
        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000, 'uslugatovar' => $uslugatovar]);
        $gate = $this->getGateCurrencyEqWithAttributes(['UseGateCompensation' => false]);

        $this->assertEquals(500.0, $this->compensationService->calculateForPartner($paySchet, $gate));
    }
    // Ends calculate for partner use uslugatovar compensation



    // Start calculate for bank use uslugatovar compensation
    public function testCalculateForBankNoGateZero()
    {
        $clientCommission = 0;
        $clientMinimalFee = 0;
        $bankCommission = 0;
        $bankMinimalFee = 0;

        $uslugatovar = new Uslugatovar(['PcComission' => $clientCommission, 'MinsumComiss' => $clientMinimalFee, 'ProvComisPC' => $bankCommission, 'ProvComisMin' => $bankMinimalFee]);
        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000, 'uslugatovar' => $uslugatovar]);
        $gate = $this->getGateCurrencyEqWithAttributes(['UseGateCompensation' => false]);

        $this->assertEquals(0.0, $this->compensationService->calculateForBank($paySchet, $gate));
    }

    public function testCalculateForBankNoGateCom2Com10()
    {
        $clientCommission = 2;
        $clientMinimalFee = 0;
        $bankCommission = 10;
        $bankMinimalFee = 0;

        $uslugatovar = new Uslugatovar(['PcComission' => $clientCommission, 'MinsumComiss' => $clientMinimalFee, 'ProvComisPC' => $bankCommission, 'ProvComisMin' => $bankMinimalFee]);
        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000, 'uslugatovar' => $uslugatovar]);
        $gate = $this->getGateCurrencyEqWithAttributes(['UseGateCompensation' => false]);

        $this->assertEquals(1020.0, $this->compensationService->calculateForBank($paySchet, $gate));
    }

    public function testCalculateForBankNoGateCom2Fee5Com10Fee20()
    {
        $clientCommission = 2;
        $clientMinimalFee = 5;
        $bankCommission = 10;
        $bankMinimalFee = 20;

        $uslugatovar = new Uslugatovar(['PcComission' => $clientCommission, 'MinsumComiss' => $clientMinimalFee, 'ProvComisPC' => $bankCommission, 'ProvComisMin' => $bankMinimalFee]);
        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000, 'uslugatovar' => $uslugatovar]);
        $gate = $this->getGateCurrencyEqWithAttributes(['UseGateCompensation' => false]);

        $this->assertEquals(2000.0, $this->compensationService->calculateForBank($paySchet, $gate));
    }
    // Ends calculate for bank use uslugatovar compensation



    // Start calculate for client use gate compensation
    public function testCalculateForClientWithGateZero()
    {
        $clientCommission = 0;
        $clientMinimalFee = 0;

        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000]);
        $gate = $this->getGateCurrencyEqWithAttributes([
            'UseGateCompensation' => true,
            'bank' => new Banks(),
            'ClientCommission' => $clientCommission,
            'ClientMinimalFee' => $clientMinimalFee,
        ]);

        $this->assertEquals(0.0, $this->compensationService->calculateForClient($paySchet, $gate));
    }

    public function testCalculateForClientWithGateCom2()
    {
        $clientCommission = 2;
        $clientMinimalFee = 0;

        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000]);
        $gate = $this->getGateCurrencyEqWithAttributes([
            'UseGateCompensation' => true,
            'bank' => new Banks(),
            'ClientCommission' => $clientCommission,
            'ClientMinimalFee' => $clientMinimalFee,
        ]);

        $this->assertEquals(200.0, $this->compensationService->calculateForClient($paySchet, $gate));
    }

    public function testCalculateForClientWithGateCom2Fee5()
    {
        $clientCommission = 2;
        $clientMinimalFee = 5;

        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000]);
        $gate = $this->getGateCurrencyEqWithAttributes([
            'UseGateCompensation' => true,
            'bank' => new Banks(),
            'ClientCommission' => $clientCommission,
            'ClientMinimalFee' => $clientMinimalFee,
        ]);

        $this->assertEquals(500.0, $this->compensationService->calculateForClient($paySchet, $gate));
    }
    // Ends calculate for client use gate compensation



    // Start calculate for partner use gate compensation
    public function testCalculateForPartnerWithGateZero()
    {
        $partnerCommission = 0;
        $partnerMinimalFee = 0;

        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000]);
        $gate = $this->getGateCurrencyEqWithAttributes([
            'UseGateCompensation' => true,
            'bank' => new Banks(),
            'PartnerCommission' => $partnerCommission,
            'PartnerMinimalFee' => $partnerMinimalFee,
        ]);

        $this->assertEquals(0.0, $this->compensationService->calculateForPartner($paySchet, $gate));
    }

    public function testCalculateForPartnerWithGateCom2()
    {
        $partnerCommission = 2;
        $partnerMinimalFee = 0;

        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000]);
        $gate = $this->getGateCurrencyEqWithAttributes([
            'UseGateCompensation' => true,
            'bank' => new Banks(),
            'PartnerCommission' => $partnerCommission,
            'PartnerMinimalFee' => $partnerMinimalFee,
        ]);

        $this->assertEquals(200.0, $this->compensationService->calculateForPartner($paySchet, $gate));
    }

    public function testCalculateForPartnerWithGateCom2Fee5()
    {
        $partnerCommission = 2;
        $partnerMinimalFee = 5;

        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 10000]);
        $gate = $this->getGateCurrencyEqWithAttributes([
            'UseGateCompensation' => true,
            'bank' => new Banks(),
            'PartnerCommission' => $partnerCommission,
            'PartnerMinimalFee' => $partnerMinimalFee,
        ]);

        $this->assertEquals(500.0, $this->compensationService->calculateForPartner($paySchet, $gate));
    }
    // Ends calculate for partner use gate compensation



    public function testCalculateForBankWithGateProblem()
    {
        $clientCommission = 2.2;
        $clientMinimalFee = 0;
        $bankCommission = 2;
        $bankMinimalFee = 0;

        $paySchet = $this->getPaySchetWithAttributes(['SummPay' => 453400]);
        $gate = $this->getGateCurrencyEqWithAttributes([
            'UseGateCompensation' => true,
            'bank' => new Banks(),
            'ClientCommission' => $clientCommission,
            'ClientMinimalFee' => $clientMinimalFee,
            'BankCommission' => $bankCommission,
            'BankMinimalFee' => $bankMinimalFee,
        ]);

        $this->assertEquals(9268, round($this->compensationService->calculateForBank($paySchet, $gate)));
    }



    private function getGateCurrencyEqWithAttributes(array $attributes)
    {
        $gate = $this->getMockBuilder(PartnerBankGate::class)->onlyMethods(['attributes', 'safeAttributes'])->getMock();
        $keysAttribute = array_merge(
            ['minimalFeeCurrency', 'feeCurrency', 'currency'],
            array_keys($attributes)
        );
        $gate->method('attributes')->willReturn($keysAttribute);
        $gate->method('safeAttributes')->willReturn($keysAttribute);

        $gate->setAttribute('minimalFeeCurrency', new Currency(['Id' => 1]));
        $gate->setAttribute('feeCurrency', new Currency(['Id' => 1]));
        $gate->setAttribute('currency', new Currency(['Id' => 1]));
        $gate->setAttributes($attributes);

        return $gate;
    }

    private function getPaySchetWithAttributes(array $attributes) {
        $paySchet = $this->getMockBuilder(PaySchet::class)->onlyMethods(['attributes', 'safeAttributes'])->getMock();
        $keysAttribute = array_keys($attributes);
        $paySchet->method('attributes')->willReturn($keysAttribute);
        $paySchet->method('safeAttributes')->willReturn($keysAttribute);
        $paySchet->setAttributes($attributes);

        return $paySchet;
    }
}