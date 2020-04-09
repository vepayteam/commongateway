<?php 

class BalanceCest
{
    public function _before(KfapiTester $I)
    {
    }

    // tests
    public function tryToBalance(KfapiTester $I)
    {
        $jsonData = [
            "account" => "30232810400000089122",
        ];
        $I->haveHttpHeader('X-Login', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/kfapi/account/balance', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":1,');

    }

    // tests
    public function tryToStatements(KfapiTester $I)
    {
        $jsonData = [
            "account" => "40201810020392828398",
            "datefrom" => "2019-11-05T12:00:43.421Z",
            "dateto" => "2019-11-05T12:00:43.421Z"
        ];
        $I->haveHttpHeader('X-Login', '110');
        $I->haveHttpHeader('X-Token', sha1(sha1('YNgGPQ736').sha1(\yii\helpers\Json::encode($jsonData))));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/kfapi/account/statements', $jsonData);
        //echo $I->grabResponse();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"status":');

    }

}
