# Fio API PHP implemention

[![Latest Stable Version](https://poser.pugx.org/webwingscz/fio-api-php/version.png)](https://packagist.org/packages/webwingscz/fio-api-php) [![Total Downloads](https://poser.pugx.org/webwingscz/fio-api-php/downloads.png)](https://packagist.org/packages/webwingscz/fio-api-php) [![License](https://poser.pugx.org/webwingscz/fio-api-php/license.svg)](https://packagist.org/packages/webwingscz/fio-api-php) [![Coverage Status](https://coveralls.io/repos/webwingscz/fio-api-php/badge.svg?branch=master)](https://coveralls.io/r/webwingscz/fio-api-php?branch=master)

Fio bank REST API implementation in PHP. It allows you to download and iterate through account balance changes. You can also upload payment orders.

[There is a Symfony Bundle](https://github.com/mhujer/fio-api-bundle) for using this library in a Symfony app.

Usage
----
1. Install the latest version with `composer require webwingscz/fio-api-php`
2. Create a *token* in the ebanking (Nastavení / API)
3. Use it according to the example bellow and check the docblocks

### Download

```php
<?php
require_once 'vendor/autoload.php';

$downloader = new FioApi\Download\Downloader('TOKEN@todo');
$transactionList = $downloader->downloadSince(new \DateTimeImmutable('-1 week'));

foreach ($transactionList->getTransactions() as $transaction) {
    var_dump($transaction); //object with getters
}

```

#### Available endpoints:
- `downloadFromTo(DateTimeInterface $from, DateTimeInterface $to): TransactionList`
- `downloadSince(DateTimeInterface $since): TransactionList`
- `downloadLast(): TransactionList`
- `setLastId(string $id)` - sets the last downloaded ID through the API

#### Optional timeout and retry configuration
- `setBaseUrl(string $baseUrl)` - override API base URL (sandbox/proxy/testing)
- `setRequestTimeout(float $seconds)` - total request timeout (`timeout` in Guzzle)
- `setConnectTimeout(float $seconds)` - connection timeout (`connect_timeout` in Guzzle)
- `configureRetry(int $retryCount, int $initialDelayMs = 30000, float $backoffMultiplier = 2.0, ?int $maxDelayMs = null)`
  - retries only network/connect errors (`ConnectException`)
  - default initial delay is 30 seconds to respect Fio token rate limit

### Upload

```php
<?php
require_once 'vendor/autoload.php';

$uploader = new FioApi\Upload\Uploader('TOKEN@todo', 'accountFromWithoutBankCode@todo');
$uploader->addPaymentOrder(new FioApi\Upload\Entity\PaymentOrderCzech(...$params));
$response = $uploader->uploadPaymentOrders();
if ($response->hasUploadSucceeded()) {
    // ...
}

```


Requirements
------------
Fio API PHP works with PHP 7.4 or higher.

Stability and compatibility (version 6)
---------------------------------------
- Current major line: `6.x`
- Supported PHP versions: `^7.4 || ^8.0`
- Supported Guzzle versions: `~6.1 | ~7.0`
- Versioning policy: SemVer (`MAJOR.MINOR.PATCH`)
- Backward compatibility:
  - No BC breaks in `6.x` patch/minor releases.
  - BC breaks are allowed only in next major version (`7.0`).
- Release policy:
  - `master` branch contains upcoming changes.
  - Tagged releases (`6.x.y`) are considered stable and production-ready.
  - Security and critical bug fixes should be released as patch versions.

Submitting bugs and feature requests
------------------------------------
Bugs and feature request are tracked on [GitHub](https://github.com/webwingscz/fio-api-php/issues)

Author
------
Martin Hujer - <https://www.martinhujer.cz>

Changelog
----------

## 6.0.0 (2026-03-05)
- add configurable base URL (`setBaseUrl`) for sandbox/proxy/testing scenarios
- add configurable request timeout and connect timeout
- add a configurable retry strategy with exponential backoff for connection errors
- improve downloader error handling for connection and invalid JSON responses
- remove invalid bundled CA certificate fallback and use explicit certificate resolution
- modernize coding standards from PSR-2 to PSR-12 and update PHP_CodeSniffer

## 5.0.0 (2024-06-07)
- [#31](https://github.com/mhujer/fio-api-php/pull/31) add `composer/ca-bundle` as a required dependency instead of bundled root cert (thx @feldsam!)

## 4.2.0 (2024-05-30)
- [#28](https://github.com/mhujer/fio-api-php/pull/28) use new Fio API URL (thx @feldsam!)

## 4.1.2 (2019-12-28)
- [#19](https://github.com/mhujer/fio-api-php/pull/19) gracefully handle response with empty column8 (thx @fmasa!)

## 4.1.1 (2019-01-28)
- [#17](https://github.com/mhujer/fio-api-php/pull/17) added senderName (nazev protiuctu) (thx @jan-stanek!)

## 4.1.0 (2018-04-13)
- [#13](https://github.com/mhujer/fio-api-php/pull/13) Support /last and /set-last-id endpoints (thx @jiripudil!)

## 4.0.1 (2017-08-09)
- [#12](https://github.com/mhujer/fio-api-php/pull/12) handle empty transaction list  (thx @soukicz!)

## 4.0.0 (2017-08-04)
- [#9](https://github.com/mhujer/fio-api-php/pull/9) minimal supported version is PHP 7.1
- [#9](https://github.com/mhujer/fio-api-php/pull/9)`DateTime` replaced with `DateTimeImmutable` (or `DateTimeInterface`)
- [#9](https://github.com/mhujer/fio-api-php/pull/9) strict types and primitive typehints are used everywhere

## 3.0.0 (2016-11-24)
- dropped support for PHP <7

## 2.3.0 (2016-11-24)
- [#7](https://github.com/mhujer/fio-api-php/pull/7): added official composer CA bundle support (@soukicz)

## 2.2.0 (2016-03-13)
- [#2](https://github.com/mhujer/fio-api-php/pull/2): added [Kdyby/CurlCaBundle](https://github.com/Kdyby/CurlCaBundle)
 	as an optional dependency (@mhujer)

## 2.1.0 (2016-03-12)
- [#1](https://github.com/mhujer/fio-api-php/pull/1): updated default GeoTrust certificate (@soukiii)
- [#1](https://github.com/mhujer/fio-api-php/pull/1): added `specification` field in transaction (@soukiii)

## 2.0.0 (2015-06-14)
- upgraded to Guzzle 6
- support for PHP 5.4 dropped (as Guzzle 6 requires PHP 5.5+)

## 1.0.3 (2015-06-14)
- updated root certificate (Root 3) as the Fio changed it on 2014-05-26

## 1.0.0 (2015-04-05)
- initial release
