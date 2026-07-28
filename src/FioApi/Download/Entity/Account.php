<?php

declare(strict_types=1);

namespace FioApi\Download\Entity;

class Account
{
    public function __construct(
        protected readonly string $accountNumber,
        protected readonly string $bankCode,
        protected readonly string $currency,
        protected readonly string $iban,
        protected readonly string $bic,
    ) {
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getBankCode(): string
    {
        return $this->bankCode;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getIban(): string
    {
        return $this->iban;
    }

    public function getBic(): string
    {
        return $this->bic;
    }
}
