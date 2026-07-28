<?php

declare(strict_types=1);

namespace FioApi\Upload\Entity;

use PHPUnit\Framework\Attributes\Depends;

class PaymentOrderListTest extends \PHPUnit\Framework\TestCase
{
    public function testAddPaymentOrderToPaymentOrderList(): PaymentOrderList
    {
        $paymentOrderList = new PaymentOrderList();
        $paymentOrderList->addPaymentOrder(self::createStub(PaymentOrderCzech::class));

        self::assertInstanceOf(PaymentOrderCzech::class, $paymentOrderList->getPaymentOrders()[0]);

        return $paymentOrderList;
    }

    public function testIsEmptyReturnsTrueIfNoPaymentOrders(): void
    {
        $paymentOrderList = new PaymentOrderList();

        self::assertTrue($paymentOrderList->isEmpty());
    }

    #[Depends('testAddPaymentOrderToPaymentOrderList')]
    public function testIsEmptyReturnsFalseIfPaymentOrderAlreadyAdded(PaymentOrderList $paymentOrderList): void
    {
        self::assertFalse($paymentOrderList->isEmpty());
    }

    #[Depends('testAddPaymentOrderToPaymentOrderList')]
    public function testClearDeleteAllPaymentOrders(PaymentOrderList $paymentOrderList): void
    {
        $paymentOrderList->clear();

        self::assertEmpty($paymentOrderList->getPaymentOrders());
    }
}
