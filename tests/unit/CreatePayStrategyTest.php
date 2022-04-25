<?php

use app\services\payment\exceptions\DuplicateCreatePayException;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\CreatePayStrategy;
use yii\mutex\FileMutex;

class CreatePayStrategyTest extends \Codeception\Test\Unit
{
    /** @var \UnitTester */
    protected $tester;

    public function testSuccessCheckCreatePayLock()
    {
        $paySchet = $this->make(PaySchet::class, [
            'ID' => 1,
        ]);
        $this->releaseLock($paySchet);

        $createPayStrategy = $this->make(CreatePayStrategy::class, [
            'mutex' => new FileMutex(),
        ]);

        $reflection = new ReflectionClass(CreatePayStrategy::class);

        $getCacheKey = $reflection->getMethod('getCacheKey');
        $getCacheKey->setAccessible(true);
        $checkCreatePayLock = $reflection->getMethod('checkCreatePayLock');
        $checkCreatePayLock->setAccessible(true);

        $checkCreatePayLock->invoke($createPayStrategy, $paySchet);
        $cacheKey = $getCacheKey->invoke($createPayStrategy, $paySchet);

        $this->assertTrue(Yii::$app->cache->exists($cacheKey));
    }

    public function testFailCheckCreatePayLock()
    {
        $paySchet = $this->make(PaySchet::class, [
            'ID' => 1,
        ]);
        $this->releaseLock($paySchet);

        $createPayStrategy = $this->make(CreatePayStrategy::class, [
            'mutex' => new FileMutex(),
        ]);

        $reflection = new ReflectionClass(CreatePayStrategy::class);

        $checkCreatePayLock = $reflection->getMethod('checkCreatePayLock');
        $checkCreatePayLock->setAccessible(true);

        $checkCreatePayLock->invoke($createPayStrategy, $paySchet);

        $this->tester->expectThrowable(DuplicateCreatePayException::class, function () use ($checkCreatePayLock, $createPayStrategy, $paySchet) {
            $checkCreatePayLock->invoke($createPayStrategy, $paySchet);
        });
    }

    public function testReleaseLock()
    {
        $paySchetObj = PaySchet::find()->select('ID')->orderBy('ID ASC')->one();
        $paySchetId = $paySchetObj->ID;
        $paySchet = $this->make(PaySchet::class, [
            'ID' => $paySchetId,
        ]);
        $this->releaseLock($paySchet);

        $createPayStrategy = $this->construct(CreatePayStrategy::class, [new CreatePayForm(['IdPay' => $paySchet->ID])]);

        $reflection = new ReflectionClass(CreatePayStrategy::class);

        $getCacheKey = $reflection->getMethod('getCacheKey');
        $getCacheKey->setAccessible(true);
        $checkCreatePayLock = $reflection->getMethod('checkCreatePayLock');
        $checkCreatePayLock->setAccessible(true);

        $checkCreatePayLock->invoke($createPayStrategy, $paySchet);
        $cacheKey = $getCacheKey->invoke($createPayStrategy, $paySchet);

        $this->assertTrue(Yii::$app->cache->exists($cacheKey));

        $createPayStrategy->releaseLock();

        $this->assertFalse(Yii::$app->cache->exists($cacheKey));
    }

    private function releaseLock($paySchet)
    {
        Yii::$app->cache->delete(CreatePayStrategy::CACHE_PREFIX_LOCK_CREATE_PAY . $paySchet->ID);
    }
}
