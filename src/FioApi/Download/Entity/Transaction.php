<?php

declare(strict_types=1);

namespace FioApi\Download\Entity;

class Transaction
{
    protected function __construct(
        protected readonly string $id,
        protected readonly \DateTimeImmutable $date,
        protected readonly float $amount,
        protected readonly string $currency,
        protected readonly ?string $senderAccountNumber,
        protected readonly ?string $senderBankCode,
        protected readonly ?string $senderBankName,
        protected readonly ?string $senderName,
        protected readonly ?string $constantSymbol,
        protected readonly ?string $variableSymbol,
        protected readonly ?string $specificSymbol,
        protected readonly ?string $userIdentity,
        protected readonly ?string $userMessage,
        protected readonly string $transactionType,
        protected readonly ?string $performedBy,
        protected readonly ?string $comment,
        protected readonly ?float $paymentOrderId,
        protected readonly ?string $specification,
    ) {
    }

    /**
     * @param \stdClass $data Transaction data from JSON API response
     */
    public static function create(\stdClass $data): self
    {
        return new self(
            (string) $data->column22->value, //ID pohybu
            new \DateTimeImmutable($data->column0->value), //Datum
            $data->column1->value, //Objem
            $data->column14->value, //Měna
            $data->column2?->value, //Protiúčet
            $data->column3?->value, //Kód banky
            $data->column12?->value, //Název banky
            $data->column10?->value, //Název protiúčtu
            $data->column4?->value, //KS
            $data->column5?->value, //VS
            $data->column6?->value, //SS
            $data->column7?->value, //Uživatelská identifikace
            $data->column16?->value, //Zpráva pro příjemce
            $data->column8->value ?? '', //Typ
            $data->column9?->value, //Provedl
            $data->column25?->value, //Komentář
            $data->column17?->value, //ID pokynu
            $data->column18?->value //Upřesnění
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getSenderAccountNumber(): ?string
    {
        return $this->senderAccountNumber;
    }

    public function getSenderBankCode(): ?string
    {
        return $this->senderBankCode;
    }

    public function getSenderBankName(): ?string
    {
        return $this->senderBankName;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function getConstantSymbol(): ?string
    {
        return $this->constantSymbol;
    }

    public function getVariableSymbol(): ?string
    {
        return $this->variableSymbol;
    }

    public function getSpecificSymbol(): ?string
    {
        return $this->specificSymbol;
    }

    public function getUserIdentity(): ?string
    {
        return $this->userIdentity;
    }

    public function getUserMessage(): ?string
    {
        return $this->userMessage;
    }

    public function getTransactionType(): string
    {
        return $this->transactionType;
    }

    public function getPerformedBy(): ?string
    {
        return $this->performedBy;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getPaymentOrderId(): ?float
    {
        return $this->paymentOrderId;
    }

    public function getSpecification(): ?string
    {
        return $this->specification;
    }
}
