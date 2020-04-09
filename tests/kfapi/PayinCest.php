<?php 

class PayinCest
{
    public function _before(KfapiTester $I)
    {
    }

    // tests
    public function tryPayIn(KfapiTester $I)
    {
        $jsonData = [
            "amount" => 10.00,
            "extid" => "t99999"
        ];
        $I->haveHttpHeader('X-Login', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/kfapi/pay/in', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');
    }

    // state
    public function tryPayState(KfapiTester $I)
    {
        $jsonData = [
            "id" => 9035
        ];
        $I->haveHttpHeader('X-Login', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/kfapi/pay/state', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":');
    }
}
