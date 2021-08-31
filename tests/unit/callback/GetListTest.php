<?php

namespace callback;

use app\models\partner\callback\CallbackList;
use app\models\payonline\Partner;
use app\modules\partner\controllers\structures\PaginationPayLoad;
use app\services\notifications\models\NotificationPay;
use app\services\payment\models\PaySchet;
use Codeception\Test\Unit;
use yii\db\Expression;

/**
 * Тесты фильтров и различных кейсов по выборкам (метод GetList() класса CallbackList):
 * сначала проводится базовый тест структуры ответа, затем в каждом тесте проверяем,
 * что по каждому варианту фильтра действительно корректно возвращает.
 *
 * Class GetListTest
 *
 * @package callback
 */
class GetListTest extends Unit
{
    /**
     * @var CallbackList $callbackList
     */
    protected $callbackList;
    /**
     * @var string $defaultDateFrom
     */
    protected $defaultDateFrom;
    /**
     * @var string $defaultDateTo
     */
    protected $defaultDateTo;

    protected function _before()
    {
        $this->callbackList = new CallbackList();
        $this->defaultDateFrom = (new \DateTime())->modify('-1year')->format('d.m.Y H:i');
        $this->defaultDateTo = (new \DateTime())->format('d.m.Y H:i');
    }

    // Тестирование базовой структуры ответа GetList
    public function testBaseFilter(): void
    {
        $this->callbackList->load(['datefrom' => $this->defaultDateFrom, 'dateto' => $this->defaultDateTo], '');

        $data = $this->callbackList->GetList(true, 0, false);

        self::assertArrayHasKey('data', $data);
        self::assertIsArray($data['data']);
        self::assertArrayHasKey('payLoad', $data);
        self::assertInstanceOf(PaginationPayLoad::class, $data['payLoad']);
    }

    // Тестирование фильтрации коллбэков по дате
    public function testDateFilter(): void
    {
        $IsAdmin = true;

        $this->callbackList->load(['datefrom' => $this->defaultDateFrom, 'dateto' => $this->defaultDateTo], '');

        $data = $this->callbackList->GetList($IsAdmin, 0, false);

        self::assertEquals([], array_filter($data['data'], function($v) {
            return ($v['DateCreate'] >= strtotime($this->defaultDateFrom)
                    && $v['DateCreate'] <= strtotime($this->defaultDateTo)) === false;
        }));
    }

    // Тестирование фильтрации коллбэков по http-коду ответа
    public function testHTTPCodeFilter(): void
    {
        $httpStatusCode = (int) NotificationPay::find()
                                               ->select('HttpCode')
                                               ->where(['>', 'HttpCode', 0])
                                               ->orderBy(new Expression('RAND()'))
                                               ->scalar();

        $this->callbackList->load([
            'datefrom' => $this->defaultDateFrom,
            'dateto'   => $this->defaultDateTo,
            'httpCode' => $httpStatusCode,
            'testMode' => true,
        ], '');

        $data = $this->callbackList->GetList(true, 0, false);

        self::assertEquals([], array_filter($data['data'], static function($v) use ($httpStatusCode) {
            return (int) $v['HttpCode'] !== $httpStatusCode;
        }));
    }

    // Тестирование фильтрации коллбэков по ExtId
    public function testExtIdFilter(): void
    {
        $extId = PaySchet::find()->select('Extid')
                         ->where(['<>', 'Extid', ''])
                         ->andWhere(['not', ['Extid' => null]])
                         ->orderBy(new Expression('RAND()'))
                         ->scalar();

        $this->callbackList->load([
            'datefrom' => $this->defaultDateFrom,
            'dateto'   => $this->defaultDateTo,
            'Extid'    => $extId,
            'testMode' => true,
        ], '');

        $data = $this->callbackList->GetList(true, 0, false);

        self::assertEquals([], array_filter($data['data'], static function($v) use ($extId) {
            return $v['Extid'] !== $extId;
        }));
    }

    // Тестирование фильтрации коллбэков по Id PaySchet
    public function testIdPayFilter(): void
    {
        $idPay = (int) PaySchet::find()->select('ID')
                               ->orderBy(new Expression('RAND()'))
                               ->scalar();

        $this->callbackList->load([
            'datefrom' => $this->defaultDateFrom,
            'dateto'   => $this->defaultDateTo,
            'id'       => $idPay,
            'testMode' => true,
        ], '');

        $data = $this->callbackList->GetList(true, 0, false);

        self::assertEquals([], array_filter($data['data'], static function($v) use ($idPay) {
            return (int) $v['IdPay'] !== $idPay;
        }));
    }

    // Тестирование фильтрации коллбэков по ID партнёра
    public function testPartnerFilter(): void
    {
        $idPartner = (int) Partner::find()->select('ID')
                                  ->orderBy(new Expression('RAND()'))
                                  ->scalar();

        $this->callbackList->load([
            'datefrom' => $this->defaultDateFrom,
            'dateto'   => $this->defaultDateTo,
            'partner'  => $idPartner,
            'testMode' => true,
        ], '');

        $data = $this->callbackList->GetList(true, 0, false);

        self::assertEquals([], array_filter($data['data'], static function($v) use ($idPartner) {
            return (int) $v['IdOrg'] !== $idPartner;
        }));
    }

    // Тестирование фильтрации коллбэков в очереди
    public function testQueuedFilter(): void
    {
        $notifstate = 1;

        $this->callbackList->load([
            'datefrom'   => $this->defaultDateFrom,
            'dateto'     => $this->defaultDateTo,
            'notifstate' => $notifstate,
        ], '');

        $data = $this->callbackList->GetList(true, 0, false);

        self::assertEquals([], array_filter($data['data'], static function($v) {
            return $v['DateSend'] > 0;
        }));
    }

    // Тестирование фильтрации отправленных коллбэков
    public function testSendedFilter(): void
    {
        $notifstate = 2;

        $this->callbackList->load([
            'datefrom'   => $this->defaultDateFrom,
            'dateto'     => $this->defaultDateTo,
            'notifstate' => $notifstate,
        ], '');

        $data = $this->callbackList->GetList(true, 0, false);

        self::assertEquals([], array_filter($data['data'], static function($v) {
            return (int) $v['DateSend'] === 0;
        }));
    }
}
