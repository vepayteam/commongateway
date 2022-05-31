<?php

use app\models\mfo\statements\StatementsAccount;
use app\models\payonline\Partner;
use app\services\statements\StatementsService;
use Codeception\Test\Unit;
use yii\db\Expression;

/**
 * Class StatementsServiceTest
 *
 * @property \UnitTester $tester
 */
class StatementsServiceTest extends Unit
{
    protected $tester;

    private const TYPE_ACC = 0; // 0 - счет на выдачу
    private const GET_BANK_STATEMENTS_DATEFROM_OFFSET = 86400 * 365 * 2; // 2 года

    /**
     * @throws Exception
     */
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testGetBankStatements(): void
    {
        $randomStatementAccount = $this->getRandomStatementAccount();
        if($randomStatementAccount) {
            $partner = Partner::findOne($randomStatementAccount->IdPartner);
        } else {
            die('empty statements_account table!');
        }

        $testDateFrom = time() - self::GET_BANK_STATEMENTS_DATEFROM_OFFSET;
        $testDateTo = time();
        $result = StatementsService::GetBankStatements(
            $partner,
            self::TYPE_ACC,
            $testDateFrom,
            $testDateTo,
            SORT_ASC,
            static function() {}
        );
        /** @var StatementsAccount $firstResult */
        $firstResult = reset($result);
        $this->assertTrue(empty($result) || $firstResult instanceof StatementsAccount);
        if ($firstResult) {
            $this->assertTrue($firstResult->DatePP >= $testDateFrom && $firstResult->DatePP <= $testDateTo);
            $this->assertSame($firstResult->TypeAccount, self::TYPE_ACC);
        }
    }

    /**
     * Вернуть рандомную запись выписки из БД
     *
     * @return StatementsAccount|null
     * @throws Exception
     */
    private function getRandomStatementAccount(): ?StatementsAccount
    {
        return StatementsAccount
            ::find()
            ->where(['between', 'DatePP', time() - self::GET_BANK_STATEMENTS_DATEFROM_OFFSET, time()])
            ->orderBy(new Expression('rand()'))
            ->limit(1)
            ->one();
    }
}
