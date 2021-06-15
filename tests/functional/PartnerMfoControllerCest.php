<?php

use app\models\partner\PartnerUsers;
use app\models\partner\UserLk;
use app\models\payonline\Uslugatovar;

//use app\services\balance\models\PartsBalancePartnerForm;

class PartnerMfoControllerCest
{
    public function _before(FunctionalTester $I)
    {
        $I->amLoggedInAs($this->findIdentity());
    }

    private function findIdentity()
    {
        /** @var PartnerUsers $partner */
        $partner = PartnerUsers::find()
            ->where(['partner_users.IsDeleted' => 0, 'partner_users.IsActive' => 1])
            ->leftJoin('partner', 'partner.ID = partner_users.IdPartner AND partner.IsDeleted = 0 AND partner.IsBlocked = 0')
            ->andWhere(['or', 'partner.ID > 0', 'partner_users.IsAdmin = 1'])
            ->one();

        return $this->getUser($partner);
    }

    private function findAdminIdentity()
    {
        /** @var PartnerUsers $partner */
        $partner = PartnerUsers::find()
            ->where(['partner_users.IsDeleted' => 0, 'partner_users.IsActive' => 1, 'partner_users.IsAdmin' => 1])
            ->leftJoin('partner', 'partner.ID = partner_users.IdPartner AND partner.IsDeleted = 0 AND partner.IsBlocked = 0')
            ->one();

        return $this->getUser($partner);
    }

    private function findPartnerWithPartsBalanceIdentity()
    {
        /** @var PartnerUsers $partner */
        $partner = PartnerUsers::find()
            ->where(['partner_users.IsDeleted' => 0, 'partner_users.IsActive' => 1, 'partner_users.IsAdmin' => 0])
            ->leftJoin('partner', 'partner.ID = partner_users.IdPartner AND partner.IsDeleted = 0 AND partner.IsBlocked = 0')
            ->leftJoin('uslugatovar', 'uslugatovar.IDPartner = partner_users.IdPartner')
            ->where(['in', 'uslugatovar.IsCustom', Uslugatovar::getPartsBalanceAccessCustoms()])
            ->where(['uslugatovar.IsDeleted' => false])
            ->one();

        return $this->getUser($partner);
    }

    private function findPartnerWithoutPartsBalanceIdentity()
    {
        /** @var PartnerUsers $partner */
        $partner = PartnerUsers::find()
            ->where(['partner_users.IsDeleted' => 0, 'partner_users.IsActive' => 1, 'partner_users.IsAdmin' => 0])
            ->leftJoin('partner', 'partner.ID = partner_users.IdPartner AND partner.IsDeleted = 0 AND partner.IsBlocked = 0')
            ->leftJoin('uslugatovar', 'uslugatovar.IDPartner = partner_users.IdPartner')
            ->where(['not in', 'uslugatovar.IsCustom', Uslugatovar::getPartsBalanceAccessCustoms()])
            ->orWhere(['uslugatovar.IsDeleted' => true])
            ->one();

        return $this->getUser($partner);
    }

    private function getUser(PartnerUsers $partner): ?UserLk
    {
        if (!$partner) {
            return null;
        }

        $user = new UserLk();
        $userLkReflectionClass = new ReflectionClass(UserLk::class);
        $id = $userLkReflectionClass->getProperty('id');
        $id->setAccessible(true);
        $id->setValue($user, $partner->Login);
        $fio = $userLkReflectionClass->getProperty('fio');
        $fio->setAccessible(true);
        $fio->setValue($user, $partner->FIO);
        $password = $userLkReflectionClass->getProperty('password');
        $password->setAccessible(true);
        $password->setValue($user, $partner->Password);
        $isAdmin = $userLkReflectionClass->getProperty('isAdmin');
        $isAdmin->setAccessible(true);
        $isAdmin->setValue($user, $partner->IsAdmin);
        $roleUser = $userLkReflectionClass->getProperty('roleUser');
        $roleUser->setAccessible(true);
        $roleUser->setValue($user, $partner->RoleUser);
        $idPartner = $userLkReflectionClass->getProperty('partner');
        $idPartner->setAccessible(true);
        $idPartner->setValue($user, $partner->IdPartner);
        $IdUser = $userLkReflectionClass->getProperty('IdUser');
        $IdUser->setAccessible(true);
        $IdUser->setValue($user, $partner->ID);
        $partnerModel = $userLkReflectionClass->getProperty('partnerModel');
        $partnerModel->setAccessible(true);
        $partnerModel->setValue($user, $partner);

        return $user;
    }

    public function tryToPartsBalanceAdminTest(FunctionalTester $I)
    {
        $I->amLoggedInAs($this->findAdminIdentity());

        $I->amOnRoute('partner/mfo/parts-balance');
        $I->see('Баланс по разбивке (Платформа)');
    }

    public function tryToPartsBalanceTruePartnerTest(FunctionalTester $I)
    {
        $I->amLoggedInAs($this->findPartnerWithPartsBalanceIdentity());

        $I->amOnRoute('partner/mfo/parts-balance');
        $I->see('Баланс по разбивке (Платформа)');
    }

    public function tryToPartsBalanceFalsePartnerTest(FunctionalTester $I)
    {
        $I->amLoggedInAs($this->findPartnerWithoutPartsBalanceIdentity());

        $I->amOnRoute('partner/mfo/parts-balance');
        $I->dontSee('Баланс по разбивке (Платформа)');
    }

//    public function tryToPartsBalanceProcessingTest(FunctionalTester $I)
//    {
//
//    }

    public function tryToPartsBalancePartnerAdminTest(FunctionalTester $I)
    {
        $I->amLoggedInAs($this->findAdminIdentity());

        $I->amOnRoute('partner/mfo/parts-balance-partner');
        $I->see('Баланс по разбивке (Партнер)');
    }

    public function tryToPartsBalancePartnerTrueTest(FunctionalTester $I)
    {
        $I->amLoggedInAs($this->findPartnerWithPartsBalanceIdentity());

        $I->amOnRoute('partner/mfo/parts-balance-partner');
        $I->see('Баланс по разбивке (Партнер)');
    }

    public function tryToPartsBalancePartnerFalseTest(FunctionalTester $I)
    {
        $I->amLoggedInAs($this->findPartnerWithoutPartsBalanceIdentity());

        $I->amOnRoute('partner/mfo/parts-balance-partner');
        $I->dontSee('Баланс по разбивке (Партнер)');
    }

//    public function tryToPartsBalancePartnerProcessingTest(FunctionalTester $I)
//    {
//
//    }
//
//    public function tryToBalanceorderTest(FunctionalTester $I)
//    {
//
//    }

    public function tryToExportvypTest(FunctionalTester $I)
    {
        $I->amOnRoute('partner/mfo/exportvyp', ['idpartner' => 117]);
        $I->see('{');
    }
}
