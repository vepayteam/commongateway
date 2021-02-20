<?php

use app\models\mfo\MfoReq;
use yii\web\ForbiddenHttpException;
use app\models\payonline\Partner;

class ModelsMfoMfoReqTest extends \Codeception\Test\Unit
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

    public function testGetKek()
    {
        $mfoReq = new MfoReq();
        Yii::$app->request->headers->set('X-Mfo', 117);
        Yii::$app->request->headers->set('X-Token', '4db602f436fda086d9b946267dcf0959197779cb');
        $this->assertEquals(null, $mfoReq->LoadData('[]'));
    }

    public function testCheckMfoToken()
    {
        $mfoReq = new MfoReq();
        $mfoReqReflectionClass = new ReflectionClass(MfoReq::class);
        $checkMfoToken = $mfoReqReflectionClass->getMethod('checkMfoToken');
        $checkMfoToken->setAccessible(true);
        if (Yii::$app->params['DEVMODE'] == 'Y') {
            $this->assertEquals(true, $checkMfoToken->invoke($mfoReq, '[]', 117, '4db602f436fda086d9b946267dcf0959197779cb'));
        } elseif (Yii::$app->params['TESTMODE'] == 'Y') {
            $this->assertEquals(true, $checkMfoToken->invoke($mfoReq, '[]', 117, '4db602f436fda086d9b946267dcf0959197779cb'));
        }
    }

    public function testCheckIpAccess()
    {
        $mfoReq = new MfoReq();
        $mfoReqReflectionClass = new ReflectionClass(MfoReq::class);
        $сheckIpAccess = $mfoReqReflectionClass->getMethod('CheckIpAccess');
        $сheckIpAccess->setAccessible(true);
        $this->expectException(ForbiddenHttpException::class);
        $сheckIpAccess->invoke($mfoReq, '');
    }

    public function testReq()
    {
        $mfoReq = new MfoReq();
        $this->assertEquals([], $mfoReq->Req());
    }

    public function testGetReq()
    {
        $mfoReq = new MfoReq();
        $mfoReqReflectionClass = new ReflectionClass(MfoReq::class);
        $req = $mfoReqReflectionClass->getProperty('req');
        $req->setAccessible(true);
        $this->assertEquals('defval_test', $mfoReq->GetReq('test', 'defval_test'));
        $req->setValue($mfoReq, ['test' => 'test_value']);
        $this->assertEquals('test_value', $mfoReq->GetReq('test', 'defval_test'));
    }

    public function testGetReqs()
    {
        $mfoReq = new MfoReq();
        $this->assertEquals(['test' => null, 'test1' => null], $mfoReq->GetReqs(['test', 'test1']));
    }

    public function testGetLinkOutCard()
    {
        $mfoReq = new MfoReq();
        if (Yii::$app->params['DEVMODE'] == 'Y') {
            $this->tester->assertEquals('http://127.0.0.1:806/mfo/default/outcard/999999', $mfoReq->getLinkOutCard('999999'));
        } elseif (Yii::$app->params['TESTMODE'] == 'Y') {
            $this->tester->assertEquals('https://'.$_SERVER['SERVER_NAME'].'/mfo/default/outcard/999999', $mfoReq->getLinkOutCard('999999'));
        }
    }

    public function testGetPartner()
    {
        $mfoReq = new MfoReq();
        $mfoReqReflectionClass = new ReflectionClass(MfoReq::class);
        $mfo = $mfoReqReflectionClass->getProperty('mfo');
        $mfo->setAccessible(true);
        $mfo->setValue($mfoReq, 117);
        $this->tester->assertInstanceOf(Partner::class, $mfoReq->getPartner());
    }
}