<?php

$curl = curl_init();

$data = '{"bic":"044525151","currency":"RUB","receiverId":"0079167932356","merchantId":"MA0000086551","firstName":"Максим","lastName":"Филин","middleName":"Сергеевич","phone":"0079031231231","amount":88100,"account":"40702810700000007050","receiverIdType":"MTEL","sourceId":"fPTb4VdT6KJOC2TX","msgSign":"M8Nh8GoAD9HXXW0t99Ak+FCuzcVKHkU57hMVnump9KB5vadWsqSjV413MC63fwY9k6l2Nil9e5rr7QBqSDXIKbC7Rd8qwDA0J38k9pqOsmspFy4\/oC6eFbcww5BqtvBupAElxhDlfAg0aIhyrso8TGkF8XZDTx2yFvJdohcY+qfDYtmyqRZKsYXLPC0EGOPPRdRFenzEw6Ctlj5eArzMuWArUJheEAR0\/6gPwOKCWvltomb89vvz92VnhU2mQ6aTDcyt3eqp31TwS3+pIJbdo1s\/WmYWIyFUnNby72Dnm4Sxfg25ojayaj2Ul8K7hz3eVjS+6bbqdwb701tuiWw6Bg=="}';

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://212.46.217.150:7601/eis-app/eis-rs/businessPaymentService/checkTransferB2c',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_SSLCERTTYPE => 'PEM',
    CURLOPT_SSLKEYTYPE => 'PEM',
    CURLOPT_SSLCERT => 'ssl/c_11942.pem',
    CURLOPT_SSLKEY => 'ssl/privateKey.pem',
//    CURLOPT_CAINFO => 'ssl/certca_11941.pem',
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'x-User-Login: 136999',
    ],
));

$response = curl_exec($curl);
$curlError = curl_error($curl);
$info = curl_getinfo($curl);

echo json_encode(['data' => $data, 'info' => $info, 'curlError' => $curlError, 'response' => $response], JSON_PRETTY_PRINT);
