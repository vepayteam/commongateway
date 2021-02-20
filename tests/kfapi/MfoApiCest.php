<?php 

class MfoApiCest
{
    public function _before(KfapiTester $I)
    {
    }

    // tests
    public function tryToCheck(KfapiTester $I)
    {
        $jsonData = [
        ];
        $I->haveHttpHeader('X-Mfo', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/mfo/check', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1');
    }

    // tests
    public function tryToPayLk(KfapiTester $I)
    {
        $jsonData = [
            "amount" => 10.00,
            "extid" => "mt99999",
            "type" => 0,
            "timeout" => 10
        ];
        $I->haveHttpHeader('X-Mfo', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/mfo/pay/lk', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');

    }

    // tests
    public function tryToPayAuto(KfapiTester $I)
    {
        $jsonData = [
            "card" => 209,
            "amount" => 10.00,
            "extid" => "mt99998"
        ];
        $I->haveHttpHeader('X-Mfo', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/mfo/pay/auto', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');

    }

// Тест не отрабатывает, требуется проверка логики.
//    public function tryToPayState(KfapiTester $I)
//    {
//        $jsonData = [
//            "id" => 9491,
//        ];
//        $I->haveHttpHeader('X-Mfo', '110');
//        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
//        $I->haveHttpHeader('Content-Type', 'application/json');
//        $I->sendPOST('/mfo/pay/state', $jsonData);
//        //echo $I->grabResponse();
//        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
//        $I->seeResponseIsJson();
//        $I->seeResponseContains('{"status":2,');
//
//    }

    // tests
    public function tryToCard(KfapiTester $I)
    {
        $jsonData = [
            "cardnum" => "4314090010071979",
            "amount" => 10.00,
            "extid" => "mt99991"
        ];
        $I->haveHttpHeader('X-Mfo', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/mfo/out/paycard', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');
    }

    // tests
    public function tryToPayUl(KfapiTester $I)
    {
        $jsonData = [
            "name" => "ООО &quot;РиК&quot;",
            "inn" => "4345029393",
            "kpp" => "434501001",
            "account" => "40201810022832828280",
            "bic" => "043409102",
            "descript" => "Оплата по счету",
            "amount" => 10.00,
            "extid" => "mt99994"
        ];
        $I->haveHttpHeader('X-Mfo', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData, 0))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/mfo/out/payul', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');
    }

    // tests
    public function tryToPayAcc(KfapiTester $I)
    {
        $jsonData = [
            "fio" => "Иванов Иван Иванович",
            "account" => "40201810022832828280",
            "bic" => "043409102",
            "descript" => "Оплата по счету",
            "amount" => 10.00,
            "extid" => "mt99995"
        ];
        $I->haveHttpHeader('X-Mfo', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData, 0))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/mfo/out/payacc', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');
    }

    // state
    public function tryOutState(KfapiTester $I)
    {
        $jsonData = [
            "id" => 9039
        ];
        $I->haveHttpHeader('X-Mfo', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/mfo/out/state', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":');
    }

    // balance
    public function tryToBalance(KfapiTester $I)
    {
        $jsonData = [
        ];
        $I->haveHttpHeader('X-Mfo', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/mfo/account/balance', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');

    }

}
