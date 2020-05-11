<?php

namespace app\models;

use app\models\antifraud\AntiFraud;
use app\models\bank\BankCheck;
use app\models\bank\MTSBank;
use app\models\crypt\CardToken;
use app\models\payonline\BalancePartner;
use app\models\payonline\Cards;
use app\models\geolocation\GeoInfo;
use app\models\payonline\OrderPay;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\queue\DraftPrintJob;
use app\models\queue\ExportpayJob;
use app\models\queue\ReverspayJob;
use Yii;
use yii\db\Exception;
use yii\helpers\VarDumper;
use yii\mutex\FileMutex;

/**
 * Оплата счета ЖКХ
 */
class Payschets
{

    /**
     * Данные счета для оплаты
     * @param int $idPay
     * @param string $ExtBillNumber
     * @param int $org
     * @return array|null
     * @throws \yii\db\Exception
     */
    public function getSchetData($idPay, $ExtBillNumber = '', $org = 0)
    {
        if ($idPay) {
            $sqlFilter = 'ps.`ID` = :ID';
            $sqlPar = [
                ":ID" => $idPay
            ];
        } else {
            $sqlFilter = 'ps.`ExtBillNumber` = :EXTBILID';
            $sqlPar = [
                ":EXTBILID" => $ExtBillNumber
            ];
        }

        if ($org) {
            //ограничение по организации
            $sqlFilter .= " AND ps.IdOrg = :IDORG";
            $sqlPar = $sqlPar + [
                    ":IDORG" => $org
                ];
        }

        $query = Yii::$app->db->createCommand('
            SELECT
                ps.`ID`,
                ps.`SummPay`,
                ps.`SummPay` + ps.`ComissSumm` AS SummFull,
                ps.`ComissSumm`,
                ps.`Status`,
                ps.`IdUser`,
                ps.`IdKard`,
                u.`Email`,
                u.`Phone`,
                u.`Fam`,
                u.`Name`,
                u.`Otch`,
                ps.`ExtBillNumber`,
                ps.`DateCreate`,
                qu.NameUsluga,
                qu.IsCustom,
                ps.TypeWidget,
                ps.IdUsluga,
                ps.UrlFormPay,
                ps.ErrorInfo,
                ps.IdGroupOplat,
                ps.SuccessUrl,
                ps.FailedUrl,
                ps.CancelUrl,
                p.IsMfo,
                ps.Bank,
                ps.Extid,
                ps.TimeElapsed,
                qu.UrlCheckReq,
                ps.UserClickPay,
                p.ID AS IDPartner,
                p.Name AS NamePartner,
                ps.AutoPayIdGate,
                p.URLSite,
                ps.ApprovalCode,
                ps.CardNum,
                ps.CardHolder,
                ps.CardExp,
                ps.RRN,
                ps.CardType,
                ps.BankName,
                ps.sms_accept,
                qu.IDPartner,
                ps.IdOrg,
                ps.IPAddressUser
            FROM
                `pay_schet` AS ps
                LEFT JOIN `user` AS u ON u.`ID`=ps.`IdUser`
                LEFT JOIN `uslugatovar` AS qu ON ps.IdUsluga = qu.ID
                LEFT JOIN `partner` AS p ON (qu.IDPartner = p.ID AND qu.ID <> 1) OR (qu.ID = 1 AND ps.IdOrg = p.ID)
            WHERE              
              ' . $sqlFilter . '
            LIMIT 1
        ', $sqlPar)
            ->queryOne();

        if ($query) {
            return $query;
        }
        return null;
    }

    /**
     * Cохранение транзакции банка
     * @param array $params [idpay, trx_id, url]
     * @return boolean
     * @throws \yii\db\Exception
     */
    public function SetBankTransact($params)
    {
        $rows = Yii::$app->db->createCommand('
            SELECT
                `ID`,
                `Status`
            FROM
                `pay_schet`
            WHERE
                ID = :ID
            LIMIT 1
        ', [
            ":ID" => $params['idpay']
        ])
            ->queryOne();

        if ($rows) {
            Yii::$app->db->createCommand()
                ->update('pay_schet', [
                    'ExtBillNumber' => $params['trx_id'],
                    'UrlFormPay' => isset($params['url']) ? $params['url'] : '',
                ], 'ID = ' . $params['idpay'])
                ->execute();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Сохранение платежа и экспорт
     * @param array $params [idpay, result_code, trx_id, ApprovalCode, RRN, message]
     * @return bool
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function confirmPay($params)
    {
        $res = false;
        $mutex = new FileMutex();
        if ($mutex->acquire('confirmPay'.$params['idpay'])) {
            try {
                $transaction = Yii::$app->db->beginTransaction();

                $query = $this->getPayInfoFoDraft($params['idpay']);

                if ($query['Status'] == 0) {
                    //только в обработке платеж завершать
                    if ($params['result_code'] == 1) {
                        //завершение оплаты и печать чека

                        //ок
                        $this->SetPayOk($params);

                        if ($query['IdOrder'] > 0) {
                            //счет оплачен
                            $this->SetOrderOk($query['IdOrder'], $params['idpay']);
                        }

                        /*if ($query['IsCustom'] == TU::$VYPLATVOZN) {
                            //выплата вознаграждения произведена
                            $this->SetVyplataVozn($params['idpay'], 1);
                        }*/

                        //чек пробить
                        //$this->CreateDraftPay($query, $params);

                        //оповещения на почту
                        $this->addNotification($params['idpay'], $query['TypeWidget'], 1);

                        //экспорт оплаты (для онлайн платежей)
                        //$this->exportPay($params['idpay']);

                        //возврат платежа при привязке карты
                        if ($query['IdUsluga'] == 1) {
                            $this->reversPay($params['idpay']);
                        }

                        //списать/зачислить на баланс
                        if ($query['IdUsluga'] != 1) {
                            $this->ChangeBalance($query, $params['idpay']);
                        }

                        $BankCheck = new BankCheck();
                        $BankCheck->UpdateLastWork($query['bank']);

                        if ($transaction->isActive) {
                            $transaction->commit();
                        }

                        $this->AntifrodUpdateStatus($params['idpay'], 1);

                        $res = true;

                    } elseif ($params['result_code'] != 0) {
                        //отмена платежа
                        $this->SetPayCancel($params);

                        /*if ($query['IsCustom'] == TU::$VYPLATVOZN) {
                            //выплата вознаграждения не произведена
                            $this->SetVyplataVozn($params['idpay'], 2);
                        }*/

                        //оповещения
                        $this->addNotification($params['idpay'], $query['TypeWidget'], 2);

                        if ($transaction->isActive) {
                            $transaction->commit();
                        }

                        $this->AntifrodUpdateStatus($params['idpay'], 0);

                        $res = false;

                    }
                } else {
                    if ($transaction) {
                        $transaction->rollBack();
                    }
                    $res = true;
                }

            } catch (\Throwable $e) {
                // в случае возникновения ошибки при выполнении одного из запросов выбрасывается исключение
                $transaction->rollback();
                Yii::error($e->getMessage(), 'rsbcron');
                throw new \Exception($e->getMessage(), $e->getCode(), $e);
            }
            $mutex->release('confirmPay'.$params['idpay']);
        }

        return $res;
    }

    /**
     * Успешных платеж
     * @param array $params [idpay, result_code, trx_id, ApprovalCode, CardNum, RRN, message]
     * @return bool
     * @throws \yii\db\Exception
     */
    private function SetPayOk(array $params)
    {
        Yii::$app->db->createCommand()
            ->update('pay_schet', [
                'PayType' => 0,
                'TimeElapsed' => 1800,
                'UserClickPay' => 1,
                'DateLastUpdate' => time(),
                'DateOplat' => time(),
                'ExtBillNumber' => $params['trx_id'],
                'ExtKeyAcces' => 0,
                'ApprovalCode' => isset($params['ApprovalCode']) ? $params['ApprovalCode'] : '',
                'RRN' => $params['RRN'],
                //'CardNum' => isset($params['CardNum']) ? $params['CardNum'] : '',
                //'CardType' => isset($params['CardBrand']) ? $params['CardBrand'] : '',
                //'BankName' => isset($params['CardIssuingBank']) ? $params['CardIssuingBank'] : '',
                'Status' => 1,
                'ErrorInfo' => isset($params['message']) ? $params['message'] : '',
                'CountSendOK' => 10
            ], '`ID` = :ID', [':ID' => $params['idpay']])
            ->execute();

        return true;
    }

    /**
     * Заказ оплачен
     * @param $IdOrder
     * @param $IdPay
     * @throws \yii\db\Exception
     */
    private function SetOrderOk($IdOrder, $IdPay)
    {
        Yii::$app->db->createCommand()
            ->update('order_pay', [
                'StateOrder' => 1,
                'DateOplata' => time(),
                'IdPaySchet' => $IdPay
            ], '`ID` = :ID', [':ID' => $IdOrder])
            ->execute();
    }

    /**
     * Возврат платежа
     * @param $IdPay
     * @throws \yii\db\Exception
     */
    public function SetReversPay($IdPay)
    {
        $query = $this->getPayInfoFoDraft($IdPay);

        if ($query['Status'] == 1) {

            Yii::$app->db->createCommand()
                ->update('pay_schet', [
                    'Status' => 3,
                    'ErrorInfo' => 'Возврат платежа',
                    'CountSendOK' => 0
                ], '`ID` = :ID', [':ID' => $IdPay])
                ->execute();

            //возврат на баланс
            if ($query['IdUsluga'] != 1) {
                $BalanceIn = new BalancePartner(BalancePartner::IN, $query['IdOrg']);
                $BalanceIn->Dec($query['SummPay'], 'Возврат платежа ' . $IdPay, 7, $IdPay, 0);
                $usl = Uslugatovar::findOne(['ID' => $query['IdUsluga']]);
                if ($usl) {
                    $comis = $usl->calcComissOrg($query['SummPay']);
                    if ($comis) {
                        $BalanceIn->Inc($comis, 'Возврат комиссии ' . $IdPay, 8, $IdPay, 0);
                    }
                }
            }
        }

    }

    /**
     * Изменить услугу платежа
     * @param $IdPay
     * @param $IdPartner
     * @param $UslugType
     * @return int
     * @throws Exception
     */
    public function ChangeUsluga($IdPay, $IdPartner, $UslugType)
    {
        $newUsluga = Yii::$app->db->createCommand("
            SELECT `ID` 
            FROM `uslugatovar`
            WHERE `IDPartner` = :IDPARTNER AND `IsCustom` = :TYPEUSL AND `IsDeleted` = 0
        ", [':IDPARTNER' => $IdPartner, ':TYPEUSL' => $UslugType]
        )->queryScalar();

        if ($newUsluga) {
            Yii::$app->db->createCommand()
                ->update('pay_schet', [
                    'IdUsluga' => $newUsluga,
                ], '`ID` = :ID', [':ID' => $IdPay])
                ->execute();

            return (int)$newUsluga;
        }
        return 0;
    }

    /**
     * Выплата произведена
     * @param $IdPay
     * @param $state
     * @throws Exception
     */
    private function SetVyplataVozn($IdPay, $state)
    {
        Yii::$app->db->createCommand()
            ->update('vyvod_system', [
                'SatateOp' => $state
            ], '`ID` = :ID', [':ID' => $IdPay])
            ->execute();
    }

    /**
     * Чек по платежу пробить
     * @param $query
     * @param array $params [idpay, result_code, trx_id, ApprovalCode, CardNum, RRN, message]
     * @return bool
     */
    private function CreateDraftPay($query, $params)
    {
        //0 - оплата услуги 1 - mobile
        if (in_array($query['TypeWidget'], [0, 1])) {
            //чек пробить
            /*if (!Yii::$app->request->isConsoleRequest) {
                Yii::$app->queue->push(new DraftPrintJob([
                    'idpay' => $params['idpay'],
                    'tovar' => $query['tovar'],
                    'tovarOFD' => $query['tovarOFD'],
                    'summDraft' => $query['SummPay'] + $query['ComissSumm'],
                    'email' => isset($query['Email']) ? $query['Email'] : ''
                ]));
            }*/

            /*$kassa = new OnlineKassa();
            $kassa->createDraft($params['idpay'], $query['tovar'], $query['tovarOFD'],
                $query['SummPay'] + $query['ComissSumm'],
                isset($query['Email']) ? $query['Email'] : '');*/
        }

        return true;
    }

    private function exportPay($idpay)
    {
        /*if (!Yii::$app->request->isConsoleRequest) {
            Yii::$app->queue->push(new ExportpayJob([
                'idpay' => $idpay
            ]));
        }*/
    }

    /**
     * @param $query
     * @param $IdPay
     * @throws Exception
     */
    private function ChangeBalance($query, $IdPay)
    {
        if (!empty($query['SchetTcbNominal'])) {
            //номинальный счет
            if (in_array($query['IsCustom'], [TU::$TOCARD, TU::$TOSCHET])) {
                //при выдаче списание суммы со счета погашения (номинального), комиссии с транзитного счета (выдачи)
                $BalanceIn = new BalancePartner(BalancePartner::IN, $query['IdOrg']);
                $BalanceIn->Dec($query['SummPay'], 'Платеж ' . $IdPay, 3, $IdPay, 0);
                $BalanceOut = new BalancePartner(BalancePartner::OUT, $query['IdOrg']);
                $usl = Uslugatovar::findOne(['ID' => $query['IdUsluga']]);
                if ($usl) {
                    $comis = $usl->calcComissOrg($query['SummPay']);
                    if ($comis) {
                        $BalanceOut->Dec($comis, 'Комиссия ' . $IdPay, 5, $IdPay, 0);
                    }
                }
            } elseif (in_array($query['IsCustom'], [TU::$POGASHECOM, TU::$POGASHATF])) {
                //погашение
                $BalanceIn = new BalancePartner(BalancePartner::IN, $query['IdOrg']);
                $BalanceIn->Inc($query['SummPay'], 'Платеж ' . $IdPay, 2, $IdPay, 0);
                $usl = Uslugatovar::findOne(['ID' => $query['IdUsluga']]);
                $comis = $usl->calcComissOrg($query['SummPay']);
                if ($comis) {
                    $BalanceOut = new BalancePartner(BalancePartner::OUT, $query['IdOrg']);
                    $BalanceOut->Dec($comis, 'Комиссия ' . $IdPay, 5, $IdPay, 0);
                }
            }
        } else {
            if (in_array($query['IsCustom'], [TU::$TOCARD, TU::$TOSCHET])) {
                //выплата
                $BalanceOut = new BalancePartner(BalancePartner::OUT, $query['IdOrg']);
                $BalanceOut->Dec($query['SummPay'], 'Платеж ' . $IdPay, 3, $IdPay, 0);
                $usl = Uslugatovar::findOne(['ID' => $query['IdUsluga']]);
                if ($usl) {
                    $comis = $usl->calcComissOrg($query['SummPay']);
                    if ($comis) {
                        $BalanceOut->Dec($comis, 'Комиссия ' . $IdPay, 5, $IdPay, 0);
                    }
                }
            } elseif (in_array($query['IsCustom'], [TU::$VYPLATVOZN, TU::$REVERSCOMIS])) {
                //перечисление вознаграждения - без списания баланса, возмещение комиссии банка тоже
                //noop
            } elseif (in_array($query['IsCustom'], [TU::$VYVODPAYS, TU::$PEREVPAYS])) {
                //перевод денег мфо
                $partner = Partner::findOne(['ID' => $query['ExtReestrIDUsluga']]);
                if ($partner && $partner->IsCommonSchetVydacha) {
                    //со счета выдачи
                    $BalanceOut = new BalancePartner(BalancePartner::OUT, $query['ExtReestrIDUsluga']);
                    $BalanceOut->Dec($query['SummPay'], 'Списание на перевод средств ' . $IdPay, 1, $IdPay, 0);
                } else {
                    //со счета погашения переводится
                    $BalanceIn = new BalancePartner(BalancePartner::IN, $query['ExtReestrIDUsluga']);
                    $BalanceIn->Dec($query['SummPay'], 'Списание на перевод средств ' . $IdPay, 1, $IdPay, 0);
                }
            } else {
                //погашение
                $BalanceIn = new BalancePartner(BalancePartner::IN, $query['IdOrg']);
                $BalanceIn->Inc($query['SummPay'], 'Платеж ' . $IdPay, 2, $IdPay, 0);
                $usl = Uslugatovar::findOne(['ID' => $query['IdUsluga']]);
                if ($usl) {
                    $comis = $usl->calcComissOrg($query['SummPay']);
                    if ($comis) {
                        $BalanceIn->Dec($comis, 'Комиссия ' . $IdPay, 5, $IdPay, 0);
                    }
                }

            }
        }
    }

    /**
     * Возврат платежа (через очередь)
     * @param $idpay
     */
    private function reversPay($idpay)
    {
        Yii::$app->queue->delay(60)->push(new ReverspayJob([
            'idpay' => $idpay
        ]));
    }


    /**
     * Отмена платежа
     * @param array $params [idpay, result_code, trx_id, ApprovalCode, CardNum, RRN, message]
     * @return bool
     * @throws \yii\db\Exception
     */
    private function SetPayCancel($params)
    {
        //отмена платежа
        Yii::$app->db->createCommand()
            ->update('pay_schet', [
                'Status' => 2,
                'ErrorInfo' => isset($params['message']) ? $params['message'] : '',
                'CountSendOK' => 0
            ], '`ID` = :ID', [':ID' => $params['idpay']])
            ->execute();

        return false;
    }

    private function AntifrodUpdateStatus($id, $status)
    {
        $antifraud = new AntiFraud($id);
        $antifraud->update_status_transaction($status == 1);
    }

    /**
     * Добавление необходимости уведомлении на почту
     * @param int $IdPay
     * @param int $TypeWidget [0 - оплата услуги 1 - mobile 2 - мерчент 3 - mfo]
     * @param int $status [1 - ok, 2 - err]
     * @throws \yii\db\Exception
     */
    protected function addNotification($IdPay, $TypeWidget, $status)
    {
        if (in_array($TypeWidget, [0, 1])) {
            //для плательщика по платежам
            $row = Yii::$app->db->createCommand('
                SELECT
                    u.Email,
                    p.UserUrlInform
                FROM
                    `pay_schet` AS p
                    LEFT JOIN `user` AS u ON (p.IdUser = u.ID AND u.IsDeleted = 0)
                WHERE
                    p.ID = :IDPAY
            ', [
                ':IDPAY' => $IdPay
            ])->queryOne();

            if ($row && !empty($row['Email']) && $status == 1) {
                //только успешные
                Yii::$app->db->createCommand()
                    ->insert('notification_pay', [
                        'IdPay' => $IdPay,
                        'Email' => $row['Email'],
                        'TypeNotif' => 0,
                        'DateCreate' => time(),
                        'DateSend' => 0
                    ])
                    ->execute();
            }

            if ($row && !empty($row['UserUrlInform'])) {
                //http
                Yii::$app->db->createCommand()
                    ->insert('notification_pay', [
                        'IdPay' => $IdPay,
                        'Email' => $row['UserUrlInform'],
                        'TypeNotif' => 3,
                        'DateCreate' => time(),
                        'DateSend' => 0
                    ])
                    ->execute();
            }
        }

        //для магазина
        $row = Yii::$app->db->createCommand("
            SELECT
                us.EmailReestr,
                us.UrlInform,
                p.Status
            FROM
                `pay_schet` AS p
                LEFT JOIN `uslugatovar` AS us ON (p.IdUsluga = us.ID AND us.IsDeleted = 0)
            WHERE
                p.ID = :IDPAY
                AND ((us.EmailReestr <> '' AND us.EmailReestr IS NOT NULL) OR (us.UrlInform <> '' AND us.UrlInform IS NOT NULL))
                AND us.IsCustom > 0
        ", [
            ':IDPAY' => $IdPay
        ])->queryOne();

        if ($row) {
            //по email успешные
            if (!empty($row['EmailReestr']) && $row['Status'] == 1) {
                Yii::$app->db->createCommand()
                    ->insert('notification_pay', [
                        'IdPay' => $IdPay,
                        'Email' => $row['EmailReestr'],
                        'TypeNotif' => 1,
                        'DateCreate' => time(),
                        'DateSend' => 0
                    ])
                    ->execute();
            }
            //по http успешные и нет
            if (!empty($row['UrlInform'])) {
                Yii::$app->db->createCommand()
                    ->insert('notification_pay', [
                        'IdPay' => $IdPay,
                        'Email' => $row['UrlInform'],
                        'TypeNotif' => 2,
                        'DateCreate' => time(),
                        'DateSend' => 0
                    ])
                    ->execute();
            }
        }
    }

    /**
     * проверяем есть ли карта
     * @param int $IdUser
     * @param int $idCard
     * @param int $activate
     * @return array|null ['ExtCardIDP', 'SrokKard']
     * @throws \yii\db\Exception
     */
    public function getSavedCard($IdUser, $idCard = 0, $activate = 0)
    {
        $card = Yii::$app->db->createCommand("
            SELECT
                c.ExtCardIDP,
                c.SrokKard
            FROM
                cards AS c
            WHERE
                c.`IsDeleted` = 0
                AND c.`TypeCard` = 0
                AND c.Status = " . ($activate ? "0" : "1") . "
                AND c.IdUser = :IdUser
                AND (
                    (MOD(c.`SrokKard`,100) > :YEAR)
                    OR
                    (ROUND(c.SrokKard/100) >= :MONTH AND MOD(c.`SrokKard`,100) = :YEAR)
                )
                " . ($idCard > 0 ? " AND `ID` = " . intval($idCard) : "") . "
            ORDER BY c.`Default` DESC
        ", [
            ":IdUser" => $IdUser,
            ":YEAR" => date('y'),
            ":MONTH" => date('n'),
        ])
            ->queryOne();

        if ($card) {
            return $card;
        }
        return null;
    }

    /**
     * Сохранение карты
     * @param int $IdUser
     * @param array $card [number,idcard,expiry,type,holder]
     * @param int $order
     * @param int $bank
     * @throws Exception
     */
    private function saveCard($IdUser, $card, $order, $bank)
    {
        $IdCart = 0;
        $rowCard = Yii::$app->db->createCommand("
            SELECT
                c.ID,
                c.ExtCardIDP
            FROM
                `user` AS u 
                LEFT JOIN `cards` AS c ON(c.IdUser = u.ID AND c.TypeCard = 0)
            WHERE
                u.ID = :IDUSER AND u.IsDeleted = 0
        ", [':IDUSER' => $IdUser]
        )->queryOne();

        $typeCard = 0;
        if ($rowCard) {
            //пользователь есть
            if ($rowCard['ExtCardIDP'] != $card['idcard']) {
                //Карта есть - удалить старую
                Yii::$app->db->createCommand()
                    ->update('cards', [
                        'IsDeleted' => 1
                    ], 'IdUser = :IDUSER', [':IDUSER' => $IdUser])
                    ->execute();

                //новая карта
                Yii::$app->db->createCommand()->insert('cards', [
                    'IdUser' => $IdUser,
                    'NameCard' => $card['number'],
                    'ExtCardIDP' => $card['idcard'],
                    'CardNumber' => $card['number'],
                    'CardType' => $typeCard,
                    'SrokKard' => $card['expiry'],
                    'CardHolder' => mb_substr($card['holder'], 0, 99),
                    'Status' => 1,
                    'DateAdd' => time(),
                    'Default' => 0,
                    'IdBank' => $bank
                ])->execute();
                $IdCart = Yii::$app->db->getLastInsertID();
            } else {
                //Карта есть - вернуть её
                $IdCart = $rowCard['ID'];
            }
        }

        if ($IdCart) {
            Yii::$app->db->createCommand()
                ->update('pay_schet', [
                    'IdKard' => $IdCart,
                ], 'ID = :IDPAY', [':IDPAY' => intval($order)])
                ->execute();
        }
    }


    /**
     * Информация для чека
     * @param int $IdPayschet
     * @return array|false
     * @throws \yii\db\Exception
     */
    public function getPayInfoFoDraft($IdPayschet)
    {
        $query = Yii::$app->db->createCommand('
              SELECT
                u.`Email`, 
                p.`SummPay`,
                p.`ComissSumm`,
                ut.IDPartner,
                p.Schetcheks,
                ut.SchetchikFormat,
                ut.SchetchikNames,
                ut.SchetchikIzm,
                ut.NameUsluga,
                p.IdUsluga,
                p.IdQrProv,
                p.QrParams,
                ut.Labels,
                ut.IsCustom,
                p.IdUser,
                p.IdShablon,
                p.`TypeWidget`,
                p.IdOrder,
                1 AS IsQrpay,
                p.Status,
                pr.ID AS IdOrg,
                pr.SchetTcbNominal,
                ut.ExtReestrIDUsluga
              FROM
                `pay_schet` AS p
                LEFT JOIN `user` AS u ON p.`IdUser` = u.`ID` AND u.IsDeleted = 0
                LEFT JOIN `uslugatovar` AS ut ON ut.ID = p.IdUsluga
                LEFT JOIN `partner` AS pr ON p.IdOrg = pr.ID
              WHERE
                p.`ID` = :OrderID
              LIMIT 1
            ', [
            ":OrderID" => $IdPayschet
        ])
            ->queryOne();

        if (!$query) {
            return false;
        }
        $query['tovar'] = 'Назначение платежа: ' . $query['NameUsluga'] . "\r\n";
        if ($query['IsCustom'] > 0) {
            $query['tovar'] = "Оплата заказа\r\n";
        }
        $query['tovar'] .= str_ireplace('|', ', ', $query['QrParams']);

        $query['summ'] = $query['SummPay'] + $query['ComissSumm'];
        $query['tovar'] .=
            "\r\nПринято: " .
            sprintf("%02.2f", $query['summ'] / 100.0) .
            "\r\nК зачислению: " . sprintf("%02.2f", $query['SummPay'] / 100.0) .
            "\r\nКомиссия: " . sprintf("%02.2f", $query['ComissSumm'] / 100.0);

        $query['tovarOFD'] = $query['NameUsluga'];

        return $query;
    }

    /**
     * флаг начала платежа
     * @param $IdPay
     * @param $Transac
     * @throws \yii\db\Exception
     */
    public function SetStartPay($IdPay, $Transac)
    {
        //флаг начала платежа
        Yii::$app->db->createCommand()
            ->update('pay_schet', [
                'UserClickPay' => 1,
                'UrlFormPay' => '/pay/form/' . $IdPay,
                'ExtBillNumber' => $Transac,
                'DateLastUpdate' => time(),
                'CountSendOK' => 0
            ], '`ID` = :ID', [':ID' => $IdPay])
            ->execute();
    }

    /**
     * занести данные карты в платеж
     * @param $IdPay
     * @param array $card [number, holder, month, year]
     * @throws Exception
     */
    public function SetCardPay($IdPay, array $card)
    {
        $cartToken = new CardToken();
        if (($token = $cartToken->CheckExistToken($card['number'],$card['month'].$card['year'])) == 0) {
            $token = $cartToken->CreateToken($card['number'],$card['month'].$card['year']);
        }
        Yii::$app->db->createCommand()
            ->update('pay_schet', [
                'CardNum' => Cards::MaskCard($card['number']),
                'CardType' => Cards::GetCardBrand(Cards::GetTypeCard($card['number'])),
                'CardHolder' => mb_substr($card['holder'], 0, 99),
                'CardExp' => $card['month'].$card['year'],
                'IdShablon' => $token
            ],'`ID` = :ID', [':ID' => $IdPay])
            ->execute();
    }

    /**
     * Сохранение карты PCI DSS
     * @param int $IdUser
     * @param array $card [number,idcard,expiry,type,holder]
     * @param $IdPAN
     * @throws \yii\db\Exception
     */
    public function SaveCardPan($IdUser, $card, $IdPAN)
    {
        $rowCard = Yii::$app->db->createCommand("
            SELECT
                c.ID,
                c.ExtCardIDP
            FROM
                `user` AS u 
                LEFT JOIN `cards` AS c ON(c.IdUser = u.ID AND c.TypeCard = 0)
            WHERE
                u.ID = :IDUSER AND u.IsDeleted = 0
        ", [':IDUSER' => $IdUser]
        )->queryOne();

        if ($rowCard) {
            //удалить старую
            Yii::$app->db->createCommand()
                ->update('cards', [
                    'IsDeleted' => 1
                ], 'IdUser = :IDUSER', [':IDUSER' => $IdUser])
                ->execute();
        }

        //новая карта
        Yii::$app->db->createCommand()->insert('cards', [
            'IdUser' => $IdUser,
            'NameCard' => Cards::MaskCard($card['number']),
            'ExtCardIDP' => 0,
            'CardNumber' => Cards::MaskCard($card['number']),
            'CardType' => 0,
            'SrokKard' => $card['month'] . $card['year'],
            'CardHolder' => mb_substr($card['holder'], 0, 99),
            'Status' => 1,
            'DateAdd' => time(),
            'Default' => 0,
            'IdPan' => $IdPAN
        ])->execute();
    }

    /**
     * Сохранение карты PCI DSS после подтверждения банка
     * @param int $IdUser
     * @param array $card [number,idcard,expiry,type,holder]
     * @param int $order
     * @param $bank
     * @throws \yii\db\Exception
     */
    public function UpdateCardExtId($IdUser, $card, $order, $bank)
    {
        $rowCard = Yii::$app->db->createCommand("
            SELECT
                c.ID,
                c.ExtCardIDP
            FROM
                `user` AS u 
                LEFT JOIN `cards` AS c ON(c.IdUser = u.ID AND c.TypeCard = 0)
            WHERE
                u.ID = :IDUSER AND u.IsDeleted = 0
        ", [':IDUSER' => $IdUser]
        )->queryOne();

        if ($rowCard && $rowCard['ID'] > 0) {
            //Карта есть
            if ($rowCard['ExtCardIDP'] != $card['idcard']) {

                //обновить данные карты
                Yii::$app->db->createCommand()->update('cards', [
                    'ExtCardIDP' => $card['idcard'],
                    'CardNumber' => $card['number'],
                    'CardType' => $card['type'],
                    'SrokKard' => $card['expiry'],
                    'CardHolder' => mb_substr($card['holder'], 0, 99),
                    'IdBank' => $bank
                ], '`ID` = :ID', ['ID' => $rowCard['ID']])->execute();
            }

            Yii::$app->db->createCommand()
                ->update('pay_schet', [
                    'IdKard' => $rowCard['ID'],
                ], 'ID = :IDPAY', [':IDPAY' => (int)$order])
                ->execute();
        } else {
            //новая карта
            $this->saveCard($IdUser, $card, $order, $bank);
        }
    }

    /**
     * Определяем IP пользователя
     * @param $IdPay
     */
    public function SetIpAddress($IdPay)
    {
        try {
            $geoIp = new GeoInfo();
            Yii::$app->db->createCommand()->update('pay_schet', [
                'IPAddressUser' => Yii::$app->request->getUserIP(),
                'CountryUser' => $geoIp->GetCountry(),
                'CityUser' => $geoIp->GetCity()
            ], ['ID' => $IdPay])->execute();
        } catch (Exception $e) {
        }
    }

    public static function RedirectUrl($url, $Extid)
    {
        if (!empty($url)) {
            return $url.(mb_stripos($url,"?")>0?"&":"?")."extid=".urlencode($Extid);
        }
        return '';
    }
}
