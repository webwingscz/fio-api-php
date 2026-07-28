<?php

declare(strict_types=1);

namespace FioApi\Download;

use FioApi\Download\Entity\TransactionList;
use FioApi\Exceptions\ConnectionException;
use FioApi\Exceptions\InternalErrorException;
use FioApi\Exceptions\InvalidResponseException;
use FioApi\Exceptions\TooGreedyException;
use FioApi\Transferrer;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Client\NetworkExceptionInterface;

class Downloader extends Transferrer
{
    public function __construct(
        #[\SensitiveParameter] string $token,
        ?ClientInterface $client = null,
    ) {
        parent::__construct($token, $client);
    }

    public function downloadFromTo(\DateTimeInterface $from, \DateTimeInterface $to): TransactionList
    {
        $url = $this->urlBuilder->buildPeriodsUrl($from, $to);
        return $this->downloadTransactionsList($url);
    }

    public function downloadSince(\DateTimeInterface $since): TransactionList
    {
        return $this->downloadFromTo($since, new \DateTimeImmutable());
    }

    public function downloadLast(): TransactionList
    {
        $url = $this->urlBuilder->buildLastUrl();
        return $this->downloadTransactionsList($url);
    }

    public function setLastId(string $id): void
    {
        $url = $this->urlBuilder->buildSetLastIdUrl($id);

        try {
            $this->requestWithRetry('GET', $url);
        } catch (NetworkExceptionInterface $e) {
            throw new ConnectionException('Could not connect to the Fio API server.', $e->getCode(), $e);
        } catch (BadResponseException $e) {
            $this->handleBadResponseException($e);
        }
    }

    private function downloadTransactionsList(string $url): TransactionList
    {
        try {
            $response = $this->requestWithRetry('GET', $url);
            $jsonData = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
        } catch (NetworkExceptionInterface $e) {
            throw new ConnectionException('Could not connect to the Fio API server.', $e->getCode(), $e);
        } catch (\JsonException $e) {
            throw new InvalidResponseException('The Fio API response is not valid JSON.', $e->getCode(), $e);
        } catch (BadResponseException $e) {
            $this->handleBadResponseException($e);
        }

        if (isset($jsonData->accountStatement) === false) {
            throw new InvalidResponseException('The Fio API response does not contain accountStatement.');
        }

        return TransactionList::create($jsonData->accountStatement);
    }

    private function handleBadResponseException(BadResponseException $e): never
    {
        if ($e->getCode() === 409) {
            throw new TooGreedyException('You can use one token for API call every 30 seconds', $e->getCode(), $e);
        }
        if ($e->getCode() === 500) {
            throw new InternalErrorException(
                'Server returned 500 Internal Error (probably invalid token?)',
                $e->getCode(),
                $e
            );
        }
        throw $e;
    }
}
