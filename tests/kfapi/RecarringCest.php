<?php 

class RecarringCest
{
    public function _before(KfapiTester $I)
    {
    }

    // tests
    public function tryToRecarringReg(KfapiTester $I)
    {
        $jsonData = [];
        $I->haveHttpHeader('X-Login', '114');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/recarring/reg', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');
    }

// Тест не отрабатывает, требуется проверка логики.
//    public function tryToRecarringInfo(KfapiTester $I)
//    {
//        $jsonData = [
//            "card" => 233,
//        ];
//        $I->haveHttpHeader('X-Login', '114');
//        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
//        $I->haveHttpHeader('Content-Type', 'application/json');
//        $I->sendPOST('/recarring/info', $jsonData);
//        //echo $I->grabResponse();
//        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
//        $I->seeResponseIsJson();
//        $I->seeResponseContains('{"status":1,');
//    }

    // tests
    public function tryToRecarringGet(KfapiTester $I)
    {
        $jsonData = [
            "id" => 9096,
        ];
        $I->haveHttpHeader('X-Login', '114');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/recarring/get', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');
    }

    // tests
    public function tryToRecarringPay(KfapiTester $I)
    {
        $jsonData = [
            "card" => 32,
            "amount" => 10.00,
            "extid" => "rc99999"
        ];
        $I->haveHttpHeader('X-Login', '114');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/recarring/pay', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');	
    }

    // tests
    public function tryToRecarringState(KfapiTester $I)
    {
        $jsonData = [
            "id" => 9100,
        ];
        $I->haveHttpHeader('X-Login', '114');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/recarring/state', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":');
    }

    // tests
    public function tryToMerchantPay(KfapiTester $I)
    {
        $jsonData = [
            "amount" => 10.00,
            "extid" => "rmrc99999"
        ];
        $I->haveHttpHeader('X-Login', '114');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/merchant/pay', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');
    }
}
