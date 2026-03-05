<?php

declare(strict_types=1);

namespace FioApi;

use FioApi\Exceptions\MissingTokenException;

class UrlBuilder
{
    public const BASE_URL = 'https://fioapi.fio.cz/v1/rest/';

    protected string $token;
    protected string $baseUrl;

    public function __construct(string $token, ?string $baseUrl = null)
    {
        $this->setToken($token);
        $this->setBaseUrl($baseUrl ?? self::BASE_URL);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        if ($token === '') {
            throw new MissingTokenException(
                'Token is required for ebanking API calls. You can get one at https://www.fio.cz/.'
            );
        }
        $this->token = $token;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $normalizedBaseUrl = trim($baseUrl);
        if ($normalizedBaseUrl === '') {
            throw new \InvalidArgumentException('Base URL must not be empty.');
        }
        if (substr($normalizedBaseUrl, -1) !== '/') {
            $normalizedBaseUrl .= '/';
        }

        $this->baseUrl = $normalizedBaseUrl;
    }

    public function buildPeriodsUrl(\DateTimeInterface $from, \DateTimeInterface $to): string
    {
        return sprintf(
            $this->baseUrl . 'periods/%s/%s/%s/transactions.json',
            $this->getToken(),
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );
    }

    public function buildLastUrl(): string
    {
        return sprintf(
            $this->baseUrl . 'last/%s/transactions.json',
            $this->getToken()
        );
    }

    public function buildSetLastIdUrl(string $id): string
    {
        return sprintf(
            $this->baseUrl . 'set-last-id/%s/%s/',
            $this->getToken(),
            $id
        );
    }

    public function buildUploadUrl(): string
    {
        return $this->baseUrl . 'import/';
    }
}
