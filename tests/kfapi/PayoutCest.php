<?php 

class PayoutCest
{
    public function _before(KfapiTester $I)
    {
    }

    // tests
    public function tryToCard(KfapiTester $I)
    {
        $jsonData = [
            "cardnum" => "4314090010071979",
            "amount" => 10.00,
            "extid" => "t99991"
        ];
        $I->haveHttpHeader('X-Login', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/kfapi/out/paycard', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');
    }

    // tests
    public function tryToUl(KfapiTester $I)
    {
        $jsonData = [
            "name" => "ООО &quot;РиК&quot;",
            "inn" => "4345029393",
            "kpp" => "434501001",
            "account" => "40201810022832828280",
            "bic" => "043409102",
            "descript" => "Оплата по счету",
            "amount" => 10.00,
            "extid" => "t99992"
        ];
        $I->haveHttpHeader('X-Login', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/kfapi/out/ul', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');
    }

    // tests
    public function tryToFl(KfapiTester $I)
    {
        $jsonData = [
            "fio" => "Иванов Иван Иванович",
            "account" => "40201810022832828280",
            "bic" => "043409102",
            "descript" => "Оплата по счету",
            "amount" => 10.00,
            "extid" => "t99993"
        ];
        $I->haveHttpHeader('X-Login', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/kfapi/out/fl', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');
    }

    // tests
    public function tryToInt(KfapiTester $I)
    {
        $jsonData = [
            "name" => "ООО &quot;РиК&quot;",
            "inn" => "4345029393",
            "kpp" => "434501001",
            "account" => "40201810022832828280",
            "descript" => "Оплата по счету",
            "amount" => 10.00,
            "extid" => "t99994"
        ];
        $I->haveHttpHeader('X-Login', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/kfapi/out/int', $jsonData);
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
        $I->haveHttpHeader('X-Login', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/kfapi/out/state', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":');
    }
}
