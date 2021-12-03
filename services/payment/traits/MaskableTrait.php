<?php

namespace app\services\payment\traits;

trait MaskableTrait
{
    private function maskRequestCardInfo(array $data): array
    {
        // CreatePayRequest model
        if (isset($data['cardNumber'])) {
            $data['cardNumber'] = $this->maskCardNumber($data['cardNumber']);
        }

        // CreatePayRequest model
        if (isset($data['cvv'])) {
            $data['cvv'] = '***';
        }

        // OutCardPayRequest model
        if (isset($data['cards']) && is_array($data['cards'])) {
            foreach ($data['cards'] as &$card) {
                $card['card'] = $this->maskCardNumber($card['card']);
            }
        }

        return $data;
    }

    private function maskResponseCardInfo(array $response): array
    {
        if (isset($response['data']['cards'])) {
            foreach ($response['data']['cards'] as &$card) {
                $card['card'] = $this->maskCardNumber($card['card']);
            }
        }

        return $response;
    }

    private function maskCardNumber(string $cardNumber): string
    {
        return preg_replace('/(\d{6})(.+)(\d{4})/', '$1****$3', $cardNumber);
    }
}
