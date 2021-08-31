yii2-curl extension
===================
                   
Easy working cURL extension for Yii2, including RESTful support:

 - POST
 - GET
 - HEAD
 - PUT
 - PATCH
 - DELETE
 - OPTIONS

Requirements
------------
- Yii2
- PHP >=7.0
- ext-curl, ext-json, and php-curl installed


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```bash
php composer.phar require --prefer-dist qfsx/yii2-curl "*"
```


Usage
-----

Once the extension is installed, simply use it in your code. The following example shows you how to handling a simple GET Request. 

```php
use qfsx\yii2\curl\Curl;
$curl = new Curl();

//get http://example.com/
$response = $curl->get('http://example.com/');

if ($curl->errorCode === null) {
   echo $response;
} else {
     // List of curl error codes here https://curl.haxx.se/libcurl/c/libcurl-errors.html
    switch ($curl->errorCode) {
    
        case 6:
            //host unknown example
            break;
    }
} 
```

```php
// GET request with GET params
// http://example.com/?key=value&scondKey=secondValue
$curl = new Curl();
$response = $curl->setGetParams([
        'key' => 'value',
        'secondKey' => 'secondValue'
     ])
     ->get('http://example.com/');
```


```php
// POST URL form-urlencoded 
$curl = new Curl();
$response = $curl->setPostParams([
        'key' => 'value',
        'secondKey' => 'secondValue'
     ])
     ->post('http://example.com/');
```

```php
// POST RAW JSON
$curl = new Curl();
$response = $curl->setRawPostData(
     json_encode[
        'key' => 'value',
        'secondKey' => 'secondValue'
     ])
     ->post('http://example.com/');
```

```php
// POST RAW JSON and auto decode JSON respawn by setting raw = true. 
// This is usefull if you expect an JSON response and want to autoparse it. 
$curl = new Curl();
$response = $curl->setRawPostData(
     json_encode[
        'key' => 'value',
        'secondKey' => 'secondValue'
     ])
     ->post('http://example.com/', true);
     
// JSON decoded response by parsing raw = true in to ->post().
var_dump($response);
```

```php
// POST RAW XML
$curl = new Curl();
$response = $curl->setRawPostData('<?xml version="1.0" encoding="UTF-8"?><someNode>Test</someNode>')
     ->post('http://example.com/');
```


```php
// POST with special headers
$curl = new Curl();
$response = $curl->setPostParams([
        'key' => 'value',
        'secondKey' => 'secondValue'
     ])
     ->setHeaders([
        'Custom-Header' => 'user-b'
     ])
     ->post('http://example.com/');
```


```php
// POST JSON with body string & special headers
$curl = new Curl();

$params = [
    'key' => 'value',
    'secondKey' => 'secondValue'
];

$response = $curl->setRequestBody(json_encode($params))
     ->setHeaders([
        'Content-Type' => 'application/json',
        'Content-Length' => strlen(json_encode($params))
     ])
     ->post('http://example.com/');
```

```php
// Avanced POST request with curl options & error handling
$curl = new Curl();

$params = [
    'key' => 'value',
    'secondKey' => 'secondValue'
];

$response = $curl->setRequestBody(json_encode($params))
     ->setOption(CURLOPT_ENCODING, 'gzip')
     ->post('http://example.com/');
     
// List of status codes here http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
switch ($curl->responseCode) {

    case 'timeout':
        //timeout error logic here
        break;
        
    case 200:
        //success logic here
        break;

    case 404:
        //404 Error logic here
        break;
}

//list response headers
var_dump($curl->responseHeaders);
```

Testing
------------

- Run codeception tests with `vendor/bin/codecept run` in repository root dir. 
  On windows run `vendor\bin\codecept.bat run`. 

##### Release 1.0.1 - Changelog
- Add OPTIONS request

##### Release 1.0 - Changelog
- Official stable release