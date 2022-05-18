<?php

namespace app\services\payment\helpers;

use app\services\payment\exceptions\BankAdapterResponseException;
use yii\helpers\Json;

class BRSErrorHelper
{
    private static $errorCodes = [
        '000' => [
            'desc_short' => 'Approved',
            'desc_full' => 'Approved',
            'desc_rus' => 'Одобрено'
        ],
        '100' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline (general, no comments)',
            'desc_rus' => 'Отказ'
        ],
        '101' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, expired card',
            'desc_rus' => 'Отказ: карта просрочена'
        ],
        '102' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, suspected fraud',
            'desc_rus' => 'Отказ'
        ],
        '103' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, card acceptor contact acquirer',
            'desc_rus' => 'Отказ: свяжитесь с обслуживающим банком'
        ],
        '104' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, restricted card',
            'desc_rus' => 'Отказ: карта имеет ограничения'
        ],
        '105' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, card acceptor call acquirer\'s security department',
            'desc_rus' => 'Отказ: вызовите службу безопасности банка'
        ],
        '106' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, allowable PIN tries exceeded',
            'desc_rus' => 'Отказ: превышено число попыток ввода PIN -кода'
        ],
        '107' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, referto card issuer',
            'desc_rus' => 'Отказ: свяжитесь с Эмитентом.'
        ],
        '108' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, refer to card issuer\'s special conditions',
            'desc_rus' => 'Отказ: обратитесь к Эмитенту'
        ],
        '109' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, invalid merchant',
            'desc_rus' => 'Отказ: неверный код терминала'
        ],
        '110' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, invalid amount',
            'desc_rus' => 'Отказ: неверная сумма'
        ],
        '111' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, invalid card number',
            'desc_rus' => 'Отказ: неверный номер Карты'
        ],
        '112' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, PIN data required',
            'desc_rus' => 'Отказ: нужен ввод PIN-код'
        ],
        '113' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, unacceptable fee',
            'desc_rus' => 'Отказ: недопустимая комиссия'
        ],
        '114' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, no account of type requested',
            'desc_rus' => 'Отказ: неверный номер счета'
        ],
        '115' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, requested function not supported',
            'desc_rus' => 'Отказ: запрошенная операция недопустима для Карты'
        ],
        '116' => [
            'desc_short' => 'Decline, no funds',
            'desc_full' => 'Decline, not sufficient funds',
            'desc_rus' => 'Отказ: недостаточно средств'
        ],
        '117' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, incorrect PIN',
            'desc_rus' => 'Отказ: неверный PIN-код'
        ],
        '118' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, no card record',
            'desc_rus' => 'Отказ: Карта не найдена'
        ],
        '119' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, transaction not permitted to cardholder',
            'desc_rus' => 'Отказ: операция не разрешена Клиенту'
        ],
        '120' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, transaction not permitted to terminal',
            'desc_rus' => 'Отказ: операция, не разрешена по данному терминалу'
        ],
        '121' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, exceeds withdrawal amount limit',
            'desc_rus' => 'Отказ: превышен лимит'
        ],
        '122' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, security violation',
            'desc_rus' => 'Отказ: по соображениям безопасности'
        ],
        '123' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, exceeds withdrawal frequency limit',
            'desc_rus' => 'Отказ: превышено число операций'
        ],
        '124' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, violation of law',
            'desc_rus' => 'Отказ: запрещено законодательством'
        ],
        '125' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, card not effective',
            'desc_rus' => 'Отказ: Карта неактивна'
        ],
        '126' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, invalid PIN block',
            'desc_rus' => 'Отказ: неверный PIN-блок'
        ],
        '127' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, PIN length error',
            'desc_rus' => 'Отказ: неверная длина PIN-код'
        ],
        '128' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, PIN kay synch error',
            'desc_rus' => 'Отказ: ошибка обработки PIN-код'
        ],
        '129' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, suspected counterfeit card',
            'desc_rus' => 'Отказ: возможно, поддельная Карта (реквизиты карты введены не верно)'
        ],
        '180' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, by cardholders wish',
            'desc_rus' => 'Отказ: по желанию держателя карты'
        ],
        '181' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '182' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '183' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '184' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '185' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '186' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '187' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '188' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '189' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '190' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '191' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '192' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '193' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '194' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '195' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '196' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '197' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '198' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '199' => [
            'desc_short' => 'Decline',
            'desc_full' => 'Decline, Card is not active',
            'desc_rus' => 'Отказ: Карта не активна'
        ],
        '200' => [
            'desc_short' => 'Pick-up',
            'desc_full' => 'Pick-up (general, no comments)',
            'desc_rus' => 'Изъять Карту'
        ],
        '201' => [
            'desc_short' => 'Pick-up',
            'desc_full' => 'Pick-up, expired card',
            'desc_rus' => 'Изъять Карту: Карта просрочена'
        ],
        '202' => [
            'desc_short' => 'Pick-up',
            'desc_full' => 'Pick-up, suspected fraud',
            'desc_rus' => 'Изъять Карту: возможна мошенническая операция'
        ],
        '203' => [
            'desc_short' => 'Pick-up',
            'desc_full' => 'Pick-up, card acceptor contact card acquirer',
            'desc_rus' => 'Изъять Карту: свяжитесь с обслуживающим банком'
        ],
        '204' => [
            'desc_short' => 'Pick-up',
            'desc_full' => 'Pick-up, restricted card',
            'desc_rus' => 'Изъять Карту: Карта имеет ограничения'
        ],
        '205' => [
            'desc_short' => 'Pick-up',
            'desc_full' => 'Pick-up, card acceptor call acquirer\'s security department',
            'desc_rus' => 'Изъять Карту: вызовите службу безопасности обслуживающего банка'
        ],
        '206' => [
            'desc_short' => 'Pick-up',
            'desc_full' => 'Pick-up, allowable PIN tries exceeded',
            'desc_rus' => 'Изъять Карту: превышено число попыток ввода PIN-код'
        ],
        '207' => [
            'desc_short' => 'Pick-up',
            'desc_full' => 'Pick-up, special conditions',
            'desc_rus' => 'Изъять Карту: специальные условия'
        ],
        '208' => [
            'desc_short' => 'Pick-up',
            'desc_full' => 'Pick-up, lost card',
            'desc_rus' => 'Изъять Карту: Карта утеряна'
        ],
        '209' => [
            'desc_short' => 'Pick-up',
            'desc_full' => 'Pick-up, stolen card',
            'desc_rus' => 'Изъять Карту: Карта украдена'
        ],
        '210' => [
            'desc_short' => 'Pick-up',
            'desc_full' => 'Pick-up, suspected counterfeit card',
            'desc_rus' => 'Отказ: возможно, поддельная Карта (реквизиты карты введены не верно)'
        ],
        '400' => [
            'desc_short' => 'Accepted',
            'desc_full' => 'Accepted (for reversal)',
            'desc_rus' => 'Одобрено (для отмены Авторизации)'
        ],
        '499' => [
            'desc_short' => 'Approved',
            'desc_full' => 'Approved, no original message data',
            'desc_rus' => 'Одобрено, оригинальная операция не найдена'
        ],
        '500' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Status message: reconciled, in balance',
            'desc_rus' => 'Одобрено: итоги совпали'
        ],
        '900' => [
            'desc_short' => 'Accepted',
            'desc_full' => 'Advice acknowledged, no financial liability accepted',
            'desc_rus' => 'Запрос авторизован, никаких финансовых обязательств не одобрено'
        ],
        '901' => [
            'desc_short' => 'Accepted',
            'desc_full' => 'Advice acknowledged, finansial liability accepted',
            'desc_rus' => 'Запрос авторизован, финансовые обязательства одобрены'
        ],
        '902' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: invalid transaction',
            'desc_rus' => 'Обратитесь к дежурной службе: неверная операция'
        ],
        '903' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Status message: re-enter transaction',
            'desc_rus' => 'Обратитесь к дежурной службе: повторите операцию'
        ],
        '904' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: format error',
            'desc_rus' => 'Обратитесь к дежурной службе: ошибка формата данных'
        ],
        '905' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: acqiurer not supported by switch',
            'desc_rus' => 'Обратитесь к дежурной службе: ошибка связи с платежной системой'
        ],
        '906' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: cutover in process',
            'desc_rus' => 'Обратитесь к дежурной службе: ошибка связи с платежной системой'
        ],
        '907' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: card issuer or switch inoperative',
            'desc_rus' => 'Обратитесь к дежурной службе: ошибка связи с платежной системой'
        ],
        '908' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: transaction destination cannot be found for routing',
            'desc_rus' => 'Обратитесь к дежурной службе: ошибка связи с платежной системой'
        ],
        '909' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: system malfunction',
            'desc_rus' => 'Обратитесь к дежурной службе: системная ошибка'
        ],
        '910' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: card issuer signed off',
            'desc_rus' => 'Обратитесь к дежурной службе: ошибка связи с платежной системой'
        ],
        '911' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: card issuer timed out',
            'desc_rus' => 'Обратитесь к дежурной службе: Эмитент Карты недоступен'
        ],
        '912' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: card issuer unavailable',
            'desc_rus' => 'Обратитесь к дежурной службе: Эмитент Карты недоступен'
        ],
        '913' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: duplicate transmission',
            'desc_rus' => 'Обратитесь к дежурной службе: дублированная операция'
        ],
        '914' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: not able to trace back to original transaction',
            'desc_rus' => 'Обратитесь к дежурной службе: оригинальная операция не найдена'
        ],
        '915' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: reconciliation cutover or checkpoint error',
            'desc_rus' => 'Обратитесь к дежурной службе: ошибка связи с платежной системой'
        ],
        '916' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: MAC incorrect',
            'desc_rus' => 'Обратитесь к дежурной службе: ошибка MAC'
        ],
        '917' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: MAC key sync error',
            'desc_rus' => 'Обратитесь к дежурной службе: ошибка MAC'
        ],
        '918' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: no communication keys available for use',
            'desc_rus' => 'Обратитесь к дежурной службе: ошибка PIN-PAD'
        ],
        '919' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: encryption key sync error',
            'desc_rus' => 'Обратитесь к дежурной службе: ошибка шифрования'
        ],
        '920' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: security software/hardware error -try again',
            'desc_rus' => 'Обратитесь к дежурной службе: ошибка шифрования'
        ],
        '921' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: security software/hardware error -no action',
            'desc_rus' => 'Обратитесь к дежурной службе: ошибка шифрования'
        ],
        '922' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Decline reason message: message number out of sequence',
            'desc_rus' => 'Обратитесь к дежурной службе: неверный номер сообщения'
        ],
        '923' => [
            'desc_short' => 'Call acquirer',
            'desc_full' => 'Status message: request in progress',
            'desc_rus' => 'Обратитесь к дежурной службе: запрос обрабатывается'
        ],
        '950' => [
            'desc_short' => 'Not accepted',
            'desc_full' => 'Decline reason message: violation of business arrangement',
            'desc_rus' => 'Отказ: недопустимая операция'
        ],
        '1001' => [
            'desc_short' => 'Error',
            'desc_full' => 'System error. Contact with bank acquirer.',
            'desc_rus' => 'Системная ошибка. Свяжитесь с банком-эквайером.'
        ],
        'XXX' => [
            'desc_short' => 'Undefined',
            'desc_full' => 'Code to be replaced by card status code or stoplist insertion reason code',
            'desc_rus' => 'Обратитесь к дежурной службе'
        ],
    ];

    public static function getMessage($brsResponse)
    {
        $resultCode = $brsResponse['RESULT_CODE'] ?? null;
        if ($resultCode && isset(self::$errorCodes[$resultCode])) {
            return $resultCode . ' - ' . self::$errorCodes[$resultCode]['desc_rus'];
        }

        if (isset($brsResponse['RESULT'])) {
            return $brsResponse['RESULT'];
        }

        \Yii::info('BRSErrorHelper RESULT_CODE or RESULT not defined, response=' . Json::encode($brsResponse));

        return BankAdapterResponseException::REQUEST_ERROR_MSG;
    }
}
