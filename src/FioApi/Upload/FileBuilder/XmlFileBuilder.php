<?php

declare(strict_types=1);

namespace FioApi\Upload\FileBuilder;

use FioApi\Exceptions\UnexpectedPaymentOrderClassException;
use FioApi\Upload\Entity\PaymentOrder;
use FioApi\Upload\Entity\PaymentOrderCzech;
use FioApi\Upload\Entity\PaymentOrderEuro;
use FioApi\Upload\Entity\PaymentOrderInternational;
use FioApi\Upload\Entity\PaymentOrderList;
use XMLWriter;

class XmlFileBuilder implements FileBuilder
{
    protected ?XMLWriter $xml = null;

    // this array has to be sorted according to right order of payments types required by Fio API for XML files
    protected const PAYMENT_ORDER_TYPES_SORTED = [
        PaymentOrderCzech::class => 'DomesticTransaction',
        PaymentOrderEuro::class => 'T2Transaction',
        PaymentOrderInternational::class => 'ForeignTransaction',
    ];

    public function getFileType(): string
    {
        return 'xml';
    }

    public function createFromPaymentOrderList(PaymentOrderList $paymentOrderList, string $accountFrom): string
    {
        $segmentedArray = static::segmentPaymentOrdersByType($paymentOrderList);

        $this->createEmptyXml();

        foreach (self::PAYMENT_ORDER_TYPES_SORTED as $paymentOrderTypeForXml) {
            if (isset($segmentedArray[$paymentOrderTypeForXml])) {
                foreach ($segmentedArray[$paymentOrderTypeForXml] as $paymentOrder) {
                    static::createXmlFromPaymentOrder($paymentOrderTypeForXml, $paymentOrder, $accountFrom);
                }
            }
        }

        return $this->endDocument();
    }

    /**
     * @return array<string, list<PaymentOrder>>
     */
    protected static function segmentPaymentOrdersByType(PaymentOrderList $paymentOrderList): array
    {
        $segmentedArray = [];
        foreach ($paymentOrderList->getPaymentOrders() as $paymentOrder) {
            $paymentOrderClassName = get_class($paymentOrder);
            $segmentName = self::PAYMENT_ORDER_TYPES_SORTED[$paymentOrderClassName] ?? null;
            if ($segmentName === null) {
                throw new UnexpectedPaymentOrderClassException(
                    sprintf('Unknown payment order class "%s".', $paymentOrderClassName)
                );
            }
            $segmentedArray[$segmentName][] = $paymentOrder;
        }
        return $segmentedArray;
    }

    protected function createEmptyXml(): void
    {
        $this->xml = new XMLWriter();
        $xml = $this->getXml();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('Import');
        $xml->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->writeAttribute('xsi:noNamespaceSchemaLocation', 'http://www.fio.cz/schema/importIB.xsd');
        $xml->startElement('Orders');
    }

    protected function createXmlFromPaymentOrder(
        string $paymentOrderType,
        PaymentOrder $paymentOrder,
        string $accountFrom
    ): void {
        $xml = $this->getXml();
        $xml->startElement($paymentOrderType);

        $this->writeTextElement('accountFrom', $accountFrom);

        foreach ($paymentOrder->toArray() as $node => $value) {
            if ($value !== null) {
                $this->writeTextElement($node, (string) $value);
            }
        }

        $xml->endElement();
    }

    protected function writeTextElement(string $node, string $value): void
    {
        $xml = $this->getXml();
        $xml->startElement($node);
        $xml->writeRaw(htmlspecialchars($value, ENT_XML1 | ENT_NOQUOTES, 'UTF-8'));
        $xml->endElement();
    }

    protected function endDocument(): string
    {
        $xml = $this->getXml();
        $xml->endDocument();
        $output = $xml->outputMemory();
        $this->xml = null;
        return $output;
    }

    protected function getXml(): XMLWriter
    {
        if ($this->xml === null) {
            throw new \LogicException('XML writer has not been initialized.');
        }

        return $this->xml;
    }
}
