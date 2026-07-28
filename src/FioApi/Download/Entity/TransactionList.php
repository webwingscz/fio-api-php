<?php

declare(strict_types=1);

namespace FioApi\Download\Entity;

class TransactionList
{
    /** @var list<Transaction> */
    protected array $transactions = [];

    protected function __construct(
        protected readonly float $openingBalance,
        protected readonly float $closingBalance,
        protected readonly \DateTimeImmutable $dateStart,
        protected readonly \DateTimeImmutable $dateEnd,
        protected readonly ?float $idFrom,
        protected readonly ?float $idTo,
        protected readonly ?int $idLastDownload,
        protected readonly Account $account,
    ) {
    }

    protected function addTransaction(Transaction $transaction): void
    {
        $this->transactions[] = $transaction;
    }

    /**
     * @param \stdClass $data Data from JSON API response
     */
    public static function create(\stdClass $data): self
    {
        $account = new Account(
            $data->info->accountId,
            $data->info->bankId,
            $data->info->currency,
            $data->info->iban,
            $data->info->bic
        );

        $transactionList = new self(
            $data->info->openingBalance,
            $data->info->closingBalance,
            new \DateTimeImmutable($data->info->dateStart),
            new \DateTimeImmutable($data->info->dateEnd),
            $data->info->idFrom,
            $data->info->idTo,
            $data->info->idLastDownload,
            $account
        );

        foreach ($data->transactionList->transaction as $transaction) {
            $transactionList->addTransaction(Transaction::create($transaction));
        }

        return $transactionList;
    }

    public function getOpeningBalance(): float
    {
        return $this->openingBalance;
    }

    public function getClosingBalance(): float
    {
        return $this->closingBalance;
    }

    public function getDateStart(): \DateTimeImmutable
    {
        return $this->dateStart;
    }

    public function getDateEnd(): \DateTimeImmutable
    {
        return $this->dateEnd;
    }

    public function getIdFrom(): ?float
    {
        return $this->idFrom;
    }

    public function getIdTo(): ?float
    {
        return $this->idTo;
    }

    public function getIdLastDownload(): ?int
    {
        return $this->idLastDownload;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @return list<Transaction>
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }
}
