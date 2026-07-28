<?php

declare(strict_types=1);

namespace FioApi\Upload\Entity;

class PaymentOrderList
{
    /** @var list<PaymentOrder> */
    protected array $paymentOrders = [];

    public function addPaymentOrder(PaymentOrder $paymentOrder): static
    {
        $this->paymentOrders[] = $paymentOrder;
        return $this;
    }

    /**
     * @return list<PaymentOrder>
     */
    public function getPaymentOrders(): array
    {
        return $this->paymentOrders;
    }

    public function isEmpty(): bool
    {
        return $this->paymentOrders === [];
    }

    public function clear(): void
    {
        $this->paymentOrders = [];
    }
}
