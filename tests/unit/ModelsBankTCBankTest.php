<?php

use app\models\bank\TCBank;
use app\services\ident\IdentService;

class ModelsBankTCBankTest extends \Codeception\Test\Unit
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
    public function testSomeFeature()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $shopId = $tCBankReflectionClass->getProperty('shopId');
        $shopId->setAccessible(true);
        $keyFile = $tCBankReflectionClass->getProperty('keyFile');
        $keyFile->setAccessible(true);
        $params['LoginTkbOct'] = 'LoginTkbOct';
        $params['KeyTkbOct'] = 'KeyTkbOct';
        $tCBank->SetMfoGate($tCBank::$OCTGATE, $params);
        $this->tester->assertEquals('LoginTkbOct', $shopId->getValue($tCBank));
        $this->tester->assertEquals('KeyTkbOct', $keyFile->getValue($tCBank));
        $tCBank->SetMfoGate($tCBank::$SCHETGATE, $params);
        $this->tester->assertEquals('LoginTkbOct', $shopId->getValue($tCBank));
        $this->tester->assertEquals('KeyTkbOct', $keyFile->getValue($tCBank));
        $params['LoginTkbAft'] = 'LoginTkbAft';
        $params['KeyTkbAft'] = 'KeyTkbAft';
        $tCBank->SetMfoGate($tCBank::$AFTGATE, $params);
        $this->tester->assertEquals('LoginTkbAft', $shopId->getValue($tCBank));
        $this->tester->assertEquals('KeyTkbAft', $keyFile->getValue($tCBank));
        $params['LoginTkbEcom'] = 'LoginTkbEcom';
        $params['KeyTkbEcom'] = 'KeyTkbEcom';
        $tCBank->SetMfoGate($tCBank::$ECOMGATE, $params);
        $this->tester->assertEquals('LoginTkbEcom', $shopId->getValue($tCBank));
        $this->tester->assertEquals('KeyTkbEcom', $keyFile->getValue($tCBank));
        $params['LoginTkbVyvod'] = 'LoginTkbVyvod';
        $params['KeyTkbVyvod'] = 'KeyTkbVyvod';
        $tCBank->SetMfoGate($tCBank::$VYVODGATE, $params);
        $this->tester->assertEquals('LoginTkbVyvod', $shopId->getValue($tCBank));
        $this->tester->assertEquals('KeyTkbVyvod', $keyFile->getValue($tCBank));
        $params['LoginTkbJkh'] = 'LoginTkbJkh';
        $params['KeyTkbJkh'] = 'KeyTkbJkh';
        $tCBank->SetMfoGate($tCBank::$JKHGATE, $params);
        $this->tester->assertEquals('LoginTkbJkh', $shopId->getValue($tCBank));
        $this->tester->assertEquals('KeyTkbJkh', $keyFile->getValue($tCBank));
        $params['LoginTkbAuto1'] = 'LoginTkbAuto1';
        $params['KeyTkbAuto1'] = 'KeyTkbAuto1';
        $params['AutoPayIdGate'] = 1;
        $tCBank->SetMfoGate($tCBank::$AUTOPAYGATE, $params);
        $this->tester->assertEquals('LoginTkbAuto1', $shopId->getValue($tCBank));
        $this->tester->assertEquals('KeyTkbAuto1', $keyFile->getValue($tCBank));
        $params['LoginTkbPerevod'] = 'LoginTkbPerevod';
        $params['KeyTkbPerevod'] = 'KeyTkbPerevod';
        $tCBank->SetMfoGate($tCBank::$PEREVODGATE, $params);
        $this->tester->assertEquals('LoginTkbPerevod', $shopId->getValue($tCBank));
        $this->tester->assertEquals('KeyTkbPerevod', $keyFile->getValue($tCBank));
        $params['LoginTkbOctVyvod'] = 'LoginTkbOctVyvod';
        $params['KeyTkbOctVyvod'] = 'KeyTkbOctVyvod';
        $tCBank->SetMfoGate($tCBank::$VYVODOCTGATE, $params);
        $this->tester->assertEquals('LoginTkbOctVyvod', $shopId->getValue($tCBank));
        $this->tester->assertEquals('KeyTkbOctVyvod', $keyFile->getValue($tCBank));
        $params['LoginTkbOctPerevod'] = 'LoginTkbOctPerevod';
        $params['KeyTkbOctPerevod'] = 'KeyTkbOctPerevod';
        $tCBank->SetMfoGate($tCBank::$PEREVODOCTGATE, $params);
        $this->tester->assertEquals('LoginTkbOctPerevod', $shopId->getValue($tCBank));
        $this->tester->assertEquals('KeyTkbOctPerevod', $keyFile->getValue($tCBank));
        $params['LoginTkbParts'] = 'LoginTkbParts';
        $params['KeyTkbParts'] = 'KeyTkbParts';
        $tCBank->SetMfoGate($tCBank::$PARTSGATE, $params);
        $this->tester->assertEquals('LoginTkbParts', $shopId->getValue($tCBank));
        $this->tester->assertEquals('KeyTkbParts', $keyFile->getValue($tCBank));
    }

    public function testBeginPay()
    {
        $tCBank = new TCBank();
        $this->expectException(yii\base\ErrorException::class);
        $tCBank->beginPay([]);
    }

    public function testСonfirmPay()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['status' => 0, 'message' => '', 'rc' => '', 'IdPay' => 0, 'Params' => null, 'info' => null], $tCBank->confirmPay(0));
    }

    public function testReversOrder()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['state' => 0, 'Status' => '', 'message' => ''], $tCBank->reversOrder(0));
    }

    public function testСreateTisket()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['state' => 0, 'Status' => '', 'message' => ''], $tCBank->reversOrder(0));
    }

    public function testCheckStatusOrder()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $checkStatusOrder = $tCBankReflectionClass->getMethod('checkStatusOrder');
        $checkStatusOrder->setAccessible(true);
        $this->tester->assertEquals(['state' => 0], $checkStatusOrder->invoke($tCBank, null, null));
    }

    public function testConvertState()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $convertState = $tCBankReflectionClass->getMethod('convertState');
        $convertState->setAccessible(true);
        $result = null;
        $this->tester->assertEquals(0, $convertState->invoke($tCBank, $result));
        $result['orderinfo']['state'] = '3';
        $result['Status'] = '0';
        $this->tester->assertEquals(1, $convertState->invoke($tCBank, $result));
        $result['orderinfo']['state'] = '6';
        $this->tester->assertEquals(2, $convertState->invoke($tCBank, $result));
        $result['orderinfo']['state'] = '5';
        $this->tester->assertEquals(3, $convertState->invoke($tCBank, $result));

    }

    public function testConvertStatePay()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $convertStatePay = $tCBankReflectionClass->getMethod('convertStatePay');
        $convertStatePay->setAccessible(true);
        $result = null;
        $this->tester->assertEquals(0, $convertStatePay->invoke($tCBank, $result));
        $result['orderinfo']['state'] = '3';
        $result['Status'] = '0';
        $this->tester->assertEquals(1, $convertStatePay->invoke($tCBank, $result));
        $result['orderinfo']['state'] = '6';
        $this->tester->assertEquals(2, $convertStatePay->invoke($tCBank, $result));
        $result['orderinfo']['state'] = '5';
        $this->tester->assertEquals(3, $convertStatePay->invoke($tCBank, $result));
        $result['orderinfo']['state'] = '8';
        $this->tester->assertEquals(3, $convertStatePay->invoke($tCBank, $result));

    }

    public function testConvertStateCard()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $convertStateCard = $tCBankReflectionClass->getMethod('convertStateCard');
        $convertStateCard->setAccessible(true);
        $result = null;
        $this->tester->assertEquals(0, $convertStateCard->invoke($tCBank, $result));
        $result['orderinfo']['state'] = '5';
        $result['Status'] = '0';
        $this->tester->assertEquals(1, $convertStateCard->invoke($tCBank, $result));
        $result['orderinfo']['state'] = '6';
        $this->tester->assertEquals(2, $convertStateCard->invoke($tCBank, $result));
    }

    public function testConvertStateOut()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $convertStateOut = $tCBankReflectionClass->getMethod('convertStateOut');
        $convertStateOut->setAccessible(true);
        $result = null;
        $this->tester->assertEquals(0, $convertStateOut->invoke($tCBank, $result));
        $result['orderinfo']['state'] = '0';
        $result['Status'] = '0';
        $this->tester->assertEquals(1, $convertStateOut->invoke($tCBank, $result));
        $result['orderinfo']['state'] = '6';
        $this->tester->assertEquals(2, $convertStateOut->invoke($tCBank, $result));
    }

    public function testCurlXmlReq()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $curlXmlReq = $tCBankReflectionClass->getMethod('curlXmlReq');
        $curlXmlReq->setAccessible(true);
        $this->tester->assertEquals(['error' => '3: '], $curlXmlReq->invoke($tCBank, '', ''));
    }

    public function testLogArr()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $logArr = $tCBankReflectionClass->getMethod('logArr');
        $logArr->setAccessible(true);
        $this->tester->assertEquals("Array\n(\n)\n", $logArr->invoke($tCBank, []));
    }

    public function testParseAns()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $parseAns = $tCBankReflectionClass->getMethod('parseAns');
        $parseAns->setAccessible(true);
        $this->tester->assertEquals(['test' => 'TEST'], $parseAns->invoke($tCBank, ['TEST' => 'TEST']));
    }

    public function testArrayChangeKeyCaseRecursive()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $array_change_key_case_recursive = $tCBankReflectionClass->getMethod('array_change_key_case_recursive');
        $array_change_key_case_recursive->setAccessible(true);
        $this->tester->assertEquals(['test' => 'TEST'], $array_change_key_case_recursive->invoke($tCBank, ['TEST' => 'TEST'], CASE_LOWER));
    }

    public function testHmacSha1()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $HmacSha1 = $tCBankReflectionClass->getMethod('HmacSha1');
        $HmacSha1->setAccessible(true);
        $this->tester->assertEquals('DJRRXBXlCVuKh6ULoN87847QX+Y=', $HmacSha1->invoke($tCBank, 'test', 'test'));
    }

    public function testRegisterCard()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals('', $tCBank->registerCard(null, null));
    }

    public function testPayCard()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals('', $tCBank->payCard(null, null, null));
    }

    public function testTransferToAccount()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['status' => 0, 'message' => ''], $tCBank->transferToAccount(['IdPay' => null, 'summ' => 0, 'CardNum' => 0, 'account' => null, 'bic' => null, 'name' => null, 'descript' => '']));
    }

    public function testPersonIndent()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['status' => 0, 'message' => ''], $tCBank->personIndent(0, ['nam' => null, 'fam' => null, 'otc' => null, 'paspser' => null, 'paspnum' => null]));
    }

    public function testPersonGetIndentResult()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['status' => 2, 'result' => ''], $tCBank->personGetIndentResult(0));
    }

    public function testFormPayOnly()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['status' => 0, 'message' => ''], $tCBank->formPayOnly(['IdPay' => null, 'summ' => 0, 'TimeElapsed' => '']));
    }

    public function testCreateAutoPay()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['status' => 0, 'message' => ''], $tCBank->createAutoPay(['IdPay' => null, 'summ' => 0, 'CardFrom' => '']));
    }

    public function testCreateRecurrentPay()
    {
        $tCBank = new TCBank();
        $params['IdPay'] = null;
        $params['summ'] = 0;
        $params['card']['number'] = '';
        $params['card']['holder'] = '';
        $params['card']['year'] = '24';
        $params['card']['month'] = '12';
        $this->tester->assertEquals(['status' => 0, 'message' => ''], $tCBank->createRecurrentPay($params));
    }

    public function testGetBalance()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['status' => 0, 'message' => 'Ошибка запроса'], $tCBank->getBalance());
    }

    public function testGetBalanceAcc()
    {
        $tCBank = new TCBank();
        $params['account'] = null;
        $this->tester->assertEquals(['status' => 0, 'message' => 'Ошибка запроса'], $tCBank->getBalanceAcc($params));
    }

    public function testGetStatement()
    {
        $tCBank = new TCBank();
        $params['account'] = null;
        $params['datefrom'] = '';
        $params['dateto'] = '';
        $this->tester->assertEquals(['status' => 0, 'message' => 'Ошибка запроса'], $tCBank->getStatement($params));
    }

    public function testGetStatementNominal()
    {
        $tCBank = new TCBank();
        $tKBankReflectionClass = new ReflectionClass(TCBank::class);
        $bankUrlXml = $tKBankReflectionClass->getProperty('bankUrlXml');
        $bankUrlXml->setAccessible(true);
        $bankUrlXml->setValue($tCBank, 'https://localhost:8204');
        $params['account'] = null;
        $params['datefrom'] = '';
        $params['dateto'] = '';
        $this->tester->assertEquals(['status' => 0, 'message' => 'Ошибка запроса'], $tCBank->getStatementNominal($params));
    }

    public function testGetStatementAbs()
    {
        $tCBank = new TCBank();
        $params['account'] = null;
        $params['datefrom'] = '';
        $params['dateto'] = '';
        $this->tester->assertEquals(['status' => 0, 'message' => 'Ошибка запроса'], $tCBank->getStatementAbs($params));
    }

    public function testActivateCard()
    {
        $tCBank = new TCBank();
        $params["cardnum"]= '';
        $params["client"]["sex"]= '';
        $params["client"]["firstname"]= '';
        $params["client"]["middlename"]= '';
        $params["client"]["surname"]= '';
        $params["client"]["phone"]= '';
        $params["client"]["birthday"]= '';
        $params["client"]["birthplace"]= '';
        $params["client"]["birthcountrycode"]= '';
        $params["client"]["birthcountry"]= '';
        $params["client"]["countrycode"]= '';
        $params["client"]["countryname"]= '';
        $params["client"]["citycode"]= '';
        $params["client"]["cityname"]= '';
        $params["client"]["registrationaddress"]["country"]= '';
        $params["client"]["registrationaddress"]["region"]= '';
        $params["client"]["registrationaddress"]["district"]= '';
        $params["client"]["registrationaddress"]["city"]= '';
        $params["client"]["registrationaddress"]["settlement"]= '';
        $params["client"]["registrationaddress"]["street"]= '';
        $params["client"]["registrationaddress"]["house"]= '';
        $params["client"]["registrationaddress"]["flat"]= '';
        $params["client"]["registrationaddress"]["country"]= '';
        $params["client"]["registrationaddress"]["region"]= '';
        $params["client"]["registrationaddress"]["district"]= '';
        $params["client"]["registrationaddress"]["city"]= '';
        $params["client"]["registrationaddress"]["settlement"]= '';
        $params["client"]["registrationaddress"]["street"]= '';
        $params["client"]["registrationaddress"]["house"]= '';
        $params["client"]["registrationaddress"]["flat"]= '';
        $params["client"]["document"]["num"]= '';
        $params["client"]["document"]["series"]= '';
        $params["client"]["document"]["date"]= '';
        $params["client"]["document"]["regname"]= '';
        $params["client"]["document"]["regcode"]= '';
        $params["client"]["document"]["dateend"]= '';
        $params["controlword"]= '';
        $this->tester->assertEquals(['status' => 0, 'message' => ''], $tCBank->ActivateCard(0, $params));
    }

    public function testSimpleActivateCard()
    {
        $tCBank = new TCBank();
        $params["cardnum"]= '';
        $params["client"]["sex"]= '';
        $params["client"]["firstname"]= '';
        $params["client"]["middlename"]= '';
        $params["client"]["surname"]= '';
        $params["client"]["phone"]= '';
        $params["client"]["birthday"]= '';
        $params["client"]["birthplace"]= '';
        $params["client"]["birthcountrycode"]= '';
        $params["client"]["birthcountry"]= '';
        $params["client"]["countrycode"]= '';
        $params["client"]["countryname"]= '';
        $params["client"]["citycode"]= '';
        $params["client"]["cityname"]= '';
        $params["client"]["document"]["num"]= '';
        $params["client"]["document"]["series"]= '';
        $params["controlword"] = '';
        $this->tester->assertEquals(['status' => 0, 'message' => ''], $tCBank->SimpleActivateCard(0,$params));
    }

    public function testStateActivateCard()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['status' => 0, 'message' => ''], $tCBank->StateActivateCard(0));
    }

    public function testGetBinDBInfo()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['status' => 0, 'message' => ''], $tCBank->GetBinDBInfo(0));
    }

    public function testPayXml()
    {
        $tCBank = new TCBank();
        $params['ID'] = null;
        $params['SummFull'] = null;
        $params['ID'] = null;
        $params['card']['number'] = null;
        $params['card']['holder'] = null;
        $params['card']['year'] = null;
        $params['card']['month'] = null;
        $params['card']['cvc'] = null;
        $params['TimeElapsed'] = null;
        $this->tester->assertEquals(['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0], $tCBank->PayXml($params));
    }

    public function testPayApple()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0], $tCBank->PayApple([]));
    }

    public function testPayGoogle()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0], $tCBank->PayGoogle([]));
    }

    public function testPaySamsung()
    {
        $tCBank = new TCBank();
        $this->tester->assertEquals(['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0], $tCBank->PaySamsung([]));
    }

    public function testConfirmXml()
    {
        $tCBank = new TCBank();
        $params['ID'] = null;
        $params['MD'] = null;
        $params['PaRes'] = null;
        $this->tester->assertEquals(['status' => 0, 'message' => '', 'fatal' => 0], $tCBank->ConfirmXml($params));
    }

    public function testRegisterBenificiar()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $bankUrlXml = $tCBankReflectionClass->getProperty('bankUrlXml');
        $bankUrlXml->setAccessible(true);
        $bankUrlXml->setValue($tCBank, 'https://localhost:8204');
        $params['req'] = null;
        $this->tester->assertEquals(['status' => 0, 'message' => 'Ошибка запроса'], $tCBank->RegisterBenificiar($params));
    }

    public function testGetCardType()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $GetCardType = $tCBankReflectionClass->getMethod('GetCardType');
        $GetCardType->setAccessible(true);
        $this->tester->assertEquals(0, $GetCardType->invoke($tCBank, 'VISA'));
        $this->tester->assertEquals(1, $GetCardType->invoke($tCBank, 'MASTER'));
        $this->tester->assertEquals(2, $GetCardType->invoke($tCBank, 'MIR'));
        $this->tester->assertEquals(3, $GetCardType->invoke($tCBank, 'AMERICANEXPRESS'));
        $this->tester->assertEquals(4, $GetCardType->invoke($tCBank, 'JCB'));
        $this->tester->assertEquals(5, $GetCardType->invoke($tCBank, 'DINNERS'));
        $this->tester->assertEquals(0, $GetCardType->invoke($tCBank, 'TEST'));
    }

    public function testBuildSoapRequestRawBody()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $buildSoapRequestRawBody = $tCBankReflectionClass->getMethod('buildSoapRequestRawBody');
        $buildSoapRequestRawBody->setAccessible(true);
        $this->tester->assertEquals(null, $buildSoapRequestRawBody->invoke($tCBank, null, null));
    }

    public function testGetIdentService()
    {
        $tCBank = new TCBank();
        $tCBankReflectionClass = new ReflectionClass(TCBank::class);
        $getIdentService = $tCBankReflectionClass->getMethod('getIdentService');
        $getIdentService->setAccessible(true);
        $this->tester->assertInstanceOf(IdentService::class, $getIdentService->invoke($tCBank));
    }
}