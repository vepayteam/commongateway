<?php

use app\models\partner\PartnerUsers;
use app\models\partner\UserLk;
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

        if ($partner) {
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
        return null;
    }


    public function tryToPartsBalanceTest(FunctionalTester $I)
    {
        $I->amOnRoute('partner/mfo/parts-balance');
        $I->see('Баланс по разбивке (Платформа)');
    }

//    public function tryToPartsBalanceProcessingTest(FunctionalTester $I)
//    {
//
//    }

    public function tryToPartsBalancePartnerTest(FunctionalTester $I)
    {
        $I->amOnRoute('partner/mfo/parts-balance-partner');
        $I->see('Баланс по разбивке (Партнер)');
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
